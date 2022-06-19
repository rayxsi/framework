<?php
declare(strict_types=1);
namespace Artificers\Container;

use ArrayAccess;
use Artificers\Supports\Reflector;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\ContainerTreaties;
use Artificers\Treaties\Container\NotFoundException;
use Closure;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\TentativeType;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use TypeError;

class Container implements ArrayAccess, ContainerTreaties {
    /**
     *All explicitly bindings goes to bindings array.
     *
     * @var array $bindings
     */
    protected  array $bindings = [];

    /**
     *Automatically resolve class and dependencies goes to resolved array.
     *
     * @var array $resolved
     */
    protected array $resolved = [];

    /**
     *All mechanix framework singleton class instances goes to instances array.
     *
     * @var array $instances
     */
    protected array $instances = [];

    /**
     *All mechanix framework class aliases goes to aliases array.
     *
     * @var array $aliases
     */
    protected array $aliases = [];

    /**
     *It contains all variadic parameter bindings.
     *
     * @var array $variadicBindings
     */
    protected array $variadicBindings = [];

    /**
     *{@inheritdoc}
     * @param string $id
     * @throws BindingException
     * @throws NotFoundException
     */
    public function get(string $id) {
        try {
            return $this->resolve($id);
        } catch (BindingException|NotFoundException $e) {
            if($this->has($id)) throw $e;

            throw new NotFoundException("Not found {$id}", $e->getCode());
        }
    }

    /**
     *{@inheritdoc}
     * @param string $id
     */
    public function has(string $id): bool {
        return isset($this->bindings[$id]) || isset($this->resolved[$id]);
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $identifier
     * @return bool
     */
    public function bound(string $identifier): bool {
        return isset($this->bindings[$identifier]) ||
            isset($this->instances[$identifier]) ||
            $this->isAlias($identifier);
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param  string  $name
     * @return bool
     */
    public function isAlias(string $name): bool {
        return isset($this->aliases[$name]);
    }

    /**
     *Check if identifier is resolved or not
     * @param string $identifier
     *
     * @return bool
     */
    public function isResolved(string $identifier): bool {
        return isset($this->resolved[$identifier]);
    }

    /**
     * Register a binding with the container.
     *
     * @param  string  $identifier
     * @param Closure|string|null $concrete
     * @param  bool  $shared
     * @return void
     * @throws TypeError
     */
    public function bind(string $identifier, Closure|string $concrete = null, bool $shared = false): void {
        //remove the stale instance from instance array and aliases
        $this->removeStaleInstances($identifier);

        //1. check $concrete is null. If null  then set $concrete = $identifier
        if(is_null($concrete))
            $concrete = $identifier;

        //2 check $concrete is not instance of Closure and string. Then generate Closure for it
        if(! $concrete instanceof  Closure) {
            if(!is_string($concrete)) {
                throw new TypeError(self::class.'::bind(): Argument #2 ($concrete) must be of type Closure|string|null');
            }

            $concrete = $this->generateClosure($identifier, $concrete);
        }

        //Bind to the bindings array
        $this->bindings[$identifier] = [
            'concrete' => $concrete,
            'shared' => $shared
        ];

        //Here we have to implement if  already resolved
    }

    /**
     *Closure generator. It generates closure if concrete is string.
     *
     * @param string $identifier
     * @param string $concrete
     * @return Closure
     */
    private function generateClosure(string $identifier, string $concrete): Closure {
        return function($container, $parameters = []) use($identifier, $concrete) {
            if($identifier === $concrete)
                return $container->build($concrete);

            return $container->resolve($concrete, $parameters);
        };
    }

    /**
    *Remove instance and alias.
     *
     * @param string $identifier
     * @return void
     */
    private function removeStaleInstances(string $identifier):void {
        unset($this->instances[$identifier], $this->aliases[$identifier]);
    }

    /**
     *Resolve the identifier from the container.
     *
     * @param string $identifier
     * @param array $params
     * @return mixed
     * @throws BindingException
     * @throws NotFoundException
     */
    public function make(string $identifier, array $params = []): mixed {
        return $this->resolve($identifier, $params);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string|callable $identifier
     * @param array $params
     * @return mixed
     * @throws BindingException
     * @throws NotFoundException
     */
    public function resolve(string|callable $identifier, array $params = []): mixed {
        $object = null;

        //1. find alias if exists
        $identifier = $this->getAlias($identifier);


        //2. check if it already there(instances[]).
        if(isset($this->instances[$identifier])) {
            return $this->instances[$identifier];
        }

        //3. Get concrete if it is in bindings array
        $concrete = $this->getConcrete($identifier);

        //3.Now we have to build manually...........
        //3a.First check is buildable or not
        if($this->isBuildable($identifier, $concrete)) {
            $object = $this->build($concrete);
        }

        //Check if it is singleton??
        if($this->isShared($identifier)) $this->instances[$identifier] = $object;

        $this->resolved[$identifier] = true;

        return $object;
    }

    /**
    *Return bool if singleton.
     *
     * @param string $identifier
     * @return bool
     */
    public function isShared(string $identifier): bool {
        return isset($this->bindings[$identifier]['shared']) && $this->bindings[$identifier]['shared'] || isset($this->instances[$identifier]);
    }

    /**
    *Return alias for given identifier.
     *
     * @param string|callable $identifier
     * @return string
     */
    public function getAlias(string|callable $identifier): string {
        return isset($this->aliases[$identifier]) ? $this->getAlias($this->aliases[$identifier]) : $identifier;
    }

    public function setAlias(string $which, string $key) {
        $this->aliases[$key] = $which;
    }

    /**
     *Return concrete for given identifier if exists in bindings array.
     *
     * @param string $identifier
     * @return Closure|string
     */
    public function getConcrete(string $identifier): Closure|string {
        return isset($this->bindings[$identifier]) ? $this->bindings[$identifier]['concrete'] : $identifier;
    }

    /**
     *Check if buildable or not. Return true or false.
     *
     * @param string $identifier
     * @param Closure|string $concrete
     * @return bool
     */
    public function isBuildable(string $identifier, Closure|string $concrete): bool {
        return $concrete instanceof Closure || $concrete === $identifier;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param string|callable $concrete
     * @return object
     * @throws BindingException
     * @throws NotFoundException
     */
    public function build(string|callable $concrete): object {
        //1.Check $concrete is a valid closure or not. If it is then simply call this concrete and return this result.
        if($concrete instanceof Closure) {
            return $concrete($this);
        }

        //Create Reflection object
        try{
            $reflector = new ReflectionClass($concrete);
        }catch(ReflectionException $e) {
            throw new BindingException("Target class [$concrete] doesn't exist");
        }

        //Check if instantiable or not. If not then throw a BindingException.
        if(!$reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        //Check have constructor?? If not then there is no dependency. It's easy peasy. Just return the new instance.
        $constructor = $reflector->getConstructor();
        if(!$constructor) return new $concrete;

        $dependencies = $constructor->getParameters();

        //Check constructor params. If not then it's also easy peasy. Just return the new instance.
        if(empty($dependencies)) return new $concrete;

        //Now we have all dependencies. Now we have to resolve those dependencies
        $instances = $this->resolveDependencies($dependencies);

        try {
            return $reflector->newInstanceArgs($instances);
        } catch (ReflectionException $e) {
            throw new BindingException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Resolve all dependencies. Return instances array.
     *
     * @param array $dependencies
     * @return array
     * @throws BindingException
     * @throws NotFoundException
     */
    protected function resolveDependencies(array $dependencies): array {
        $instancesArr = [];

        //1.Check all dependencies if there is any primitive type hint
        foreach($dependencies as $dependency) {
            //In here first we assume that dependency is a ReflectionNamedType, and it has proper type hinted then get the class name of this dependency.
            //Now if the getParamClassName return null then it is primitive type we have to handle resolveUnionType, Otherwise we get proper class name and have to handle resolveClass.
            $arr = is_null(Reflector::getParamClassName($dependency)) ? $this->resolveUnionType($dependency) : $this->resolveClass($dependency);

            $instancesArr[] = $arr;
        }

        return $instancesArr;
    }

    /**
     * Resolve primitive type hint.
     *
     * @param ReflectionParameter $param
     * @return mixed
     * @throws BindingException
     */
    private function resolveUnionType(ReflectionParameter $param): mixed {
        //For, now we sure that dependency is primitive type. So, we can't do anything.
        //Check param holds default value or not. If it is then simply return this value, Otherwise throw a BindingException.
        if($param->isDefaultValueAvailable()) return $param->getDefaultValue();

        throw new BindingException("Dependency is unresolvable :(. When resolving [$param] of primitive type in class [{$param->getDeclaringClass()->getName()}]");
    }

    /**
     * Resolve class and its dependencies.
     *
     * @param ReflectionParameter $param
     * @return array|mixed
     * @throws BindingException
     * @throws NotFoundException
     */
    private function resolveClass(ReflectionParameter $param): mixed {
        //1.Check  if it is variadic(class) type hint
        try {
           return $param->isVariadic() ? $this->resolveVariadicClass($param) : $this->make(Reflector::getParamClassName($param));
        }catch (BindingException $e) {
            if($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * Resolve variadic class.
     *
     * @param ReflectionParameter $param
     * @return array|mixed
     * @throws BindingException
     * @throws NotFoundException
     */
    private function resolveVariadicClass(ReflectionParameter $param): mixed {
        //Resolve parameter type and alias
        $className = Reflector::getParamClassName($param);
        $interfaceIdentifier = $this->getAlias($className);

        //Resolve declaring class name and alias
        $belongToClass = $param->getDeclaringClass();
        $belongToIdentifier = $this->getAlias($belongToClass->getName());

        $concrete = $this->variadicBindings[$belongToIdentifier][$interfaceIdentifier];

        if(is_null($concrete)) {
            //throw a VariadicBindingException
            throw new NotFoundException("Unresolved variadic parameter [{$param}] in class {$belongToClass}. You need to explicitly do variadic binding for this.");
        }

        //Check if Closure then just call it and return its instances.
        if($concrete instanceof Closure) {
            return $concrete($this);
        }

        //Check if array then Container must have to resolve all of those dependencies
        $instances = [];
        return array_map(function($identifier){
           return $this->resolve($identifier);
        }, $concrete);
    }

    /**
     * Throws an exception if not instantiable.
     *
     * @param $concrete
     * @throws BindingException
     */
    protected function notInstantiable($concrete) {
        throw new BindingException("Target [$concrete] is not instantiable :(");
    }

    /**
     *Bind variadic parameters.
     *
     * Where declared.
     * @param string $when
     *
     * Interface name.
     * @param string $identifier
     *
     * @param Closure|array $concrete
     * Callback must have to return an array.
     *
     * @return void
     */
    public function variadicPropBinding(string $when, string $identifier, Closure|array $concrete): void {
        $this->variadicBindings[$when][$identifier] = $concrete;
    }

    /**
     * Register a singleton binding with the container.
     *
     * @param  string  $identifier
     * @param Closure|string|null $concrete
     * @param  bool  $shared
     * @return void
     * @throws TypeError
     */
    public function singleton(string $identifier, Closure|string $concrete = null, bool $shared = false): void {
        $this->bind($identifier, $concrete, true);
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $identifier
     * @param  mixed  $instance
     * @return mixed
     */
    public function setInstance(string $identifier, mixed $instance): mixed {
        $this->instances[$identifier] = $instance;

        return $instance;
    }

    public function __get(string $key) {
       return $this[$key];
    }

    /**
    * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool {
        return $this->bound($offset);
    }

    /**
     * @inheritDoc
     * @throws NotFoundException
     * @throws BindingException
     */
    public function offsetGet(mixed $offset): mixed {
        return $this->make($offset);
    }

    /**
    * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->bind($offset, $value instanceof Closure ? $value : fn() => $value);
    }

    /**
    * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void {
        unset($this->bindings[$offset], $this->resolved[$offset], $this->instances[$offset]);
    }

    public function __set(string $key, $value): void {
        $this[$key] = $value;
    }
}