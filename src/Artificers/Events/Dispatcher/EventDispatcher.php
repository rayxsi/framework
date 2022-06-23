<?php

namespace Artificers\Events\Dispatcher;

use Artificers\Container\Container;
use Artificers\Events\NotValidMethodException;
use Artificers\Supports\Reflector;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Artificers\Treaties\Events\EventDispatcherTreaties;
use Artificers\Treaties\Events\EventListenerProviderTreaties;
use Artificers\Treaties\Events\EventTreaties;
use Artificers\Utilities\Ary;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;


class EventDispatcher implements EventDispatcherTreaties {
    private EventListenerProviderTreaties $listener;
    private Container $container;

    public function __construct(EventListenerProviderTreaties $listener, Container $container) {
        $this->listener = $listener;
        $this->container = $container;
    }


    /**
     * @inheritDoc
     * @return mixed
     * @throws NotValidMethodException
     * @throws ReflectionException
     */
    public function dispatch(object $event): mixed {
        //1. Check event is the interface of EventTreaties and event propagation is stopped??
        if($event instanceof EventTreaties && $event->isPropagationStopped()) {
            return $event;
        }

        foreach($this->listener->getListenersForEvent($event) as $listener) {

            //we can pass single listener. Like this UserServiceListener::class. Mechanix call by default handle method of listener. We can also use user defined method
            //like this = 'UserServiceListener@User_defined_method'
            //we can pass multiple listeners. Like this using array = [UserServiceListener::class, UserServiceListener2::class]. Default call to handle method of listeners.
            //we can pass multiple listeners with user defined handler. Like this using array = ['UserServiceListener@store', 'UserServiceListener2@method'].

            if(is_string($listener)) {
                $this->resolveWithIdentifier($listener, $event);
            }

            if(Ary::isArr($listener)) {
                foreach($listener as $actionListener) {
                    if(is_string($actionListener)) {
                        $this->resolveWithIdentifier($actionListener, $event);
                    }
                }
            }

           if($listener instanceof Closure) {
               $listener($event);
           }

        }

        return $this;
    }

    /**
     * Resolve if listener is string.
     * @param string $listener
     * @param object $event
     * @throws NotValidMethodException
     * @throws ReflectionException
     */
    private function resolveWithIdentifier(string $listener,object $event) {
        [$controller, $method] = str_contains($listener, '@') ? explode('@', $listener) : [$listener, 'handle']; //default is handle method.
        $resolvedDependencies = $this->resolveDependenciesOfActionMethod($controller, $method, $event);

        try {
            $object = $this->container[$controller];
        }catch (BindingException|NotFoundException $e) {
            //if it's from Mechanix app by developers
            $qualifiedClass = "RayxsiApp\\Listeners\\".$controller;
            $object = $this->container[$qualifiedClass];
        }

        try {
            call_user_func([$object, $method], ...$resolvedDependencies);
        }catch(NotValidMethodException $e) {
            throw new NotValidMethodException("Class {get_class($object)} does not have a method [$method]", $e->getCode());
        }
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function resolveDependenciesOfActionMethod(string $controller, string $method, object $event): array
    {
        $reflector = new ReflectionClass($controller);
        $dependencies = $reflector->getMethod($method)->getParameters();

        $buildStack = [];

        foreach($dependencies as $dependency) {

          $buildStack[] = is_null($className = Reflector::getParamClassName($dependency)) ? $this->resolveUnionType($dependency, $event) : $this->container[$className];
        }

        return $buildStack;
    }

    /**
     * @throws Exception
     */
    private function resolveUnionType(ReflectionParameter $param, object $event): mixed {
        $name = $param->getName();

        if($name === 'event') return $event;

        $paramFromListener = $this->listener->getAllParams();

        if(empty($paramFromListener)) {
            return null;
        }

        return $paramFromListener[$name] ?? throw new Exception('Parameter should be the same name');
    }
}