<?php
declare(strict_types=1);
namespace Artificers\Treaties\Container;

use Closure;
use Psr\Container\ContainerInterface;
use TypeError;

interface ContainerTreaties extends ContainerInterface {
    /**
     * Register a binding with the container.
     *
     * @param  string  $identifier
     * @param Closure|string|null $concrete
     * @param  bool  $shared
     * @return void
     * @throws TypeError
     */
    public function bind(string $identifier, Closure|string $concrete = null, bool $shared = false): void ;

    /**
     * Determine if the given abstract type has been bound.
     * @param  string  $identifier
     * @return bool
     */
    public function bound(string $identifier): bool;

    /**
     * Determine if a given string is an alias.
     *
     * @param  string  $name
     * @return bool
     */
    public function isAlias(string $name): bool;

    /**
     *Check if identifier is resolved or not.
     *
     * @param string $identifier
     * @return bool
     */
    public function isResolved(string $identifier): bool;

    /**
     *Resolve the identifier from the container.
     *
     * @param string $identifier
     * @param array $params
     * @return mixed
     * @throws BindingException
     * @throws NotFoundException
     */
    public function make(string $identifier, array $params = []): mixed;

    /**
     * Resolve the given type from the container.
     *
     * @param string|callable $identifier
     * @param array $params
     * @return mixed
     * @throws BindingException
     * @throws NotFoundException
     */
    public function resolve(string|callable $identifier, array $params = []): mixed;

    /**
     *Return bool if singleton.
     *
     * @param string $identifier
     * @return bool
     */
    public function isShared(string $identifier): bool;

    /**
     *Return alias for given identifier.
     *
     * @param string|callable $identifier
     * @return string
     */
    public function getAlias(string|callable $identifier): string;

    /**
     *Return concrete for given identifier if exists in bindings array.
     *
     * @param string $identifier
     * @return Closure|string
     */
    public function getConcrete(string $identifier): Closure|string;

    /**
     *Check if buildable or not. Return true or false.
     *
     * @param string $identifier
     * @param Closure|string $concrete
     * @return bool
     */
    public function isBuildable(string $identifier, Closure|string $concrete): bool;

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param string|callable $concrete
     * @return object
     * @throws BindingException
     * @throws NotFoundException
     */
    public function build(string|callable $concrete): object;

    /**
     *Bind variadic parameters.
     *
     * @param string $when
     * @param string $identifier
     * @param Closure|array $concrete
     * Callback must have to return an array.
     *
     * @return void
     */
    public function variadicPropBinding(string $when, string $identifier, Closure|array $concrete): void;

    /**
     * Register a singleton binding with the container.
     *
     * @param  string  $identifier
     * @param Closure|string|null $concrete
     * @param  bool  $shared
     * @return void
     * @throws TypeError
     */
    public function singleton(string $identifier, Closure|string $concrete = null, bool $shared = false): void;
}