<?php

namespace Artificers\Database\Raintter;

use Artificers\Container\Container;
use Artificers\Database\Concern\InteractWithInputValidation;
use Artificers\Database\Lizie\Query\Builder;
use Artificers\Database\Raintter\Concern\HasRelationship;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;

/**
 * @method select(...$columns): Builder
 * @method update(array $data): Builder
 * @method delete(array $data): Builder
 */
abstract class Model {
    use InteractWithInputValidation;
    use HasRelationship;

    public const TABLE_NAME = "";

    public const PRIMARY_KEY = "Id";

    private array $fKeys;

    private MDBConjunction $conjunction;

    public function __construct(MDBConjunction $conjunction) {
        $this->conjunction = $conjunction;
    }

    public function save(): void {

        dump($this->conjunction->transport($this));
    }

    public function join(string $method) {

    }

    public function __set(string $name, $value): void {
       $this->$name = $value;
    }

    public function __call(string $name, array $arguments) {
        if(!method_exists($this, $name)) {
            return $this->conjunction->getQueryBuilder()->table(static::TABLE_NAME)->$name(...$arguments);
        }
    }
}