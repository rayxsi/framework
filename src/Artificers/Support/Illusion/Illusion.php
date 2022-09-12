<?php

namespace Artificers\Support\Illusion;

use Artificers\Foundation\Rayxsi;
use RuntimeException;


abstract class Illusion {
    protected static Rayxsi $rXsiApp;

    protected static array $cachedInstances = [];

    protected static bool $cache = true;

    /**
     * Get the registered illusion name.
     *
     * @return string
     * @throws RuntimeException
     */
    protected static function getIllusionAccessor(): string {
        //This should change when custom we will make custom error generator
        throw new RuntimeException('Illusion does not implement getIllusionAccessor method.');
    }

    /**
     *Get the Illusion root.
     *
     * @return mixed|void
     */
    protected static function getIllusionRoot() {
        return static::resolvedIllusionInstance(static::getIllusionAccessor());
    }

    /**
     * Resolve the illusion root instance from the container.
     *
     * @param string $name
     * @return mixed|void
     */
    protected static function resolvedIllusionInstance(string $name) {
        if(isset(static::$cachedInstances[$name])) {
            return static::$cachedInstances[$name];
        }

        if(static::$rXsiApp) {
            if(static::$cache) {
               return static::$cachedInstances[$name] = static::$rXsiApp[$name];
            }

            return static::$rXsiApp[$name];
        }
    }

    /**
     * Set the Mechanix application.
     *
     * @param Rayxsi $app
     * @return void
     */
    public static function setIllusionApplication(Rayxsi $app): void {
        static::$rXsiApp = $app;
    }

    /**
     * Remove cached instance.
     *
     * @param string $name
     * @return void
     */
    public static function removeCachedInstance(string $name): void {
        unset(static::$cachedInstances[$name]);
    }

    /**
     * Remove all cached instances.
     *
     * @return void
     */
    public static function removeAllCachedInstances(): void {
        static::$cachedInstances = [];
    }

    public static function __callStatic(string $method, array $args) {
        $object = static::getIllusionRoot();

        if(!$object)
            throw new RuntimeException('A illusion root has not been set.');

        return $object->$method(...$args);
    }
}