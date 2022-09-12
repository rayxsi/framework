<?php
declare(strict_types=1);
namespace Artificers\Design;


use Artificers\Container\Container;

class DesignPatternFactory {
    private array $factory = [
        "Pipeline" => Pattern\Pipeline::class
    ];

    private Container $container;

    public function __construct(Container $container = null) {
        $this->container = $container;
    }

    public function get(string $key) {
        return key_exists($key, $this->factory) ? new $this->factory[$key]($this->container) : null;
    }
}