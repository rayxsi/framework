<?php

namespace Artificers\Events\Dispatcher;

use Artificers\Container\Container;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Artificers\Treaties\Events\EventDispatcherTreaties;
use Artificers\Treaties\Events\EventListenerProviderTreaties;
use Artificers\Treaties\Events\EventTreaties;
use Artificers\Utility\Ary;
use Closure;
use InvalidArgumentException;
use ReflectionException;


class EventDispatcher implements EventDispatcherTreaties {
    private EventListenerProviderTreaties $listener;
    private Container $container;

    public function __construct(EventListenerProviderTreaties $listener, Container $container) {
        $this->listener = $listener;
        $this->container = $container;
    }


    /**
     * @inheritDoc
     * @param object $event
     * @return EventTreaties
     * @throws BindingException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function dispatch(object $event): EventTreaties|null {
        //1. Check event is the interface of EventTreaties and event propagation is stopped??
        if($event instanceof EventTreaties && $event->isPropagationStopped()) {
            return $event;
        }

        $params = array_merge($this->listener->getAllParams(), ['event'=>$event]);

        foreach($this->listener->getListenersForEvent($event) as $listener) {

            //we can pass single listener. Like this UserServiceListener::class. Rayxsi call by default handle method of listener. We can also use user defined method
            //like this = 'UserServiceListener@User_defined_method'
            //we can pass multiple listeners. Like this using array = [UserServiceListener::class, UserServiceListener2::class]. Default call to handle method of listeners.
            //we can pass multiple listeners with user defined handler. Like this using array = ['UserServiceListener@store', 'UserServiceListener2@method'].

            if(is_string($listener)) {
                $this->call($listener, $params, 'handle');
            }

            if(Ary::isArr($listener)) {
                foreach($listener as $actionListener) {

                    if(is_string($actionListener)) {
                        $this->call($actionListener, $params, 'handle');
                    }else {
                        throw new InvalidArgumentException('Multiple listener must be array of string.');
                    }
                }
            }

           if($listener instanceof Closure) {
               $this->call($listener, $params);
           }

        }

        return null;
    }


    /**
     * @throws ReflectionException
     * @throws NotFoundException
     * @throws BindingException
     */
    protected function call($listener, $params=[], $defaultMethod=null) {
        try {
            $this->container->call($listener, $params, $defaultMethod);
        } catch (BindingException|NotFoundException $e) {
            $qualifiedClass = "RayxsiApp\\Listeners\\".$listener;
            $this->container->call($qualifiedClass, $params, $defaultMethod);
        } catch (ReflectionException $e) {
            throw new BindingException($e->getMessage(), $e->getCode());
        }
    }
}