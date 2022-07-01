<?php

namespace Artificers\Container;

use Artificers\Supports\Reflector;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Artificers\Utilities\Ary;
use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

class MethodHandler {
    /**
     * Call and resolve all dependencies of method and closure.
     *
     * @throws NotFoundException
     * @throws BindingException
     * @throws ReflectionException
     */
    public static function call(Container $container, Closure|array|string $callback, array $params = [], $defaultMethod = null) {
        //1. we have to resolve default method if callback is string. because it may be only class name or class name with @ sign with method.
        if(is_string($callback) && !$defaultMethod && method_exists($callback, '__invoke')) {
            $defaultMethod = '__invoke';
        }

        if(static::callableWithAtSign($callback) || $defaultMethod) {
            return static::classCall($container, $callback, $params, $defaultMethod);
        }

        return static::boundCall($container, $callback, $params);
    }

    /**
     * @throws ReflectionException
     * @throws BindingException
     */
    protected static function boundCall(Container $container, Closure|array|string $callback, $params) {
        return $callback(...array_values(static::resolveMethodDependencies($container, $callback, $params)));
    }

    /**
     * Resolve all dependencies.
     *
     * @throws ReflectionException
     * @throws BindingException
     */
    protected static function resolveMethodDependencies($container, $callback, $params): array {
        $dependencies = [];

        foreach(static::getReflector($callback)->getParameters() as $param) {
            static::addDependencies($container, $param, $params, $dependencies);
        }

        return $dependencies;
    }

    /**
     * Add dependencies into dependencies array.
     *
     * @throws ReflectionException
     * @throws BindingException
     */
    protected static function addDependencies(Container $container, ReflectionParameter $parameter, &$parameters, &$dependencies): void {
        //1. get the param name and check if it is there in parameters array or not.
        if(Ary::keyExists($name = $parameter->getName(), $parameters)) {
            $dependencies[] = $parameters[$name];
            unset($parameters[$name]);
        }elseif(!is_null($className = Reflector::getParamClassName($parameter))) {
           static::resolveClassDependencies($container, $className, $parameter, $parameters, $dependencies);
        }elseif($parameter->getDefaultValue()) {
            $dependencies[] = $parameter->getDefaultValue();
        }else {
            throw new BindingException("Unresolved dependency {$parameter} in className[{$parameter->getDeclaringClass()->getName()}]");
        }
    }

    /**
     * Check @ sign.
     *
     * @param $callback
     * @return bool
     */
    public static function callableWithAtSign($callback): bool {
        return is_string($callback) && str_contains($callback, '@');
    }

    /**
     * Resolve method dependencies.
     *
     * @param Container $container
     * @param $className
     * @param ReflectionParameter $parameter
     * @param $parameters
     * @param $dependencies
     * @return void
     */
    private static function resolveClassDependencies(Container $container, $className, ReflectionParameter $parameter, &$parameters, &$dependencies): void {
        if(array_key_exists($className, $parameters)) {
            $dependencies[] = $parameters[$className];
            unset($parameters[$className]);
        }elseif($parameter->isVariadic()) {
            $variadicDependencies = $container[$className];

            $dependencies = array_merge($dependencies, Ary::wrap($variadicDependencies));
        }else {
            $dependencies[] = $container[$className];
        }
    }

    /**
     * Generate method and function reflector.
     *
     * @throws ReflectionException
     */
    protected static function getReflector($callback): ReflectionMethod|ReflectionFunction {
        if(is_object($callback) && !$callback instanceof \Closure) {
            $callback = Ary::make($callback, '__invoke');
        }

        return is_array($callback) ? new ReflectionMethod(...$callback): new ReflectionFunction($callback);
    }

    /**
     * Handler of method.
     *
     * @throws NotFoundException
     * @throws BindingException
     * @throws ReflectionException
     */
    public static function classCall(Container $container, Closure|array|string $callback, array $params = [], $defaultMethod = null) {
       [$action, $method] = static::getClassAndMethodName($callback, $defaultMethod);

        return static::call($container, Ary::make($container->make($action), $method), $params);
    }

    /**
     * Generate class and its method.
     *
     * @param $callback
     * @param $defaultMethod
     * @return array
     */
    public static function getClassAndMethodName($callback, $defaultMethod): array {
        $method = count($arr = explode('@', $callback)) === 2 ? $arr[1] : $defaultMethod;

        if(is_null($method)) {
            throw new InvalidArgumentException('Method did not provide');
        }

        return Ary::make($arr[0], $method);
    }
}