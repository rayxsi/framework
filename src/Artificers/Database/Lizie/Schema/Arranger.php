<?php

namespace Artificers\Database\Lizie\Schema;

use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Grammars\Grammar;
use Artificers\Utilities\Ary;

class Arranger {
    private array $preparedColumns;

    private array $pkColumns = [];

    private array $fKeyClauses = [];
    private array $pKeyClauses = [];

    private Grammar $grammar;
    private Table $table;

    /**
     * @throws ForeignKeyException
     */
    public function __construct(Table $table, Grammar $grammar) {
        $this->table = $table;
        $this->grammar = $grammar;
        $this->prepare($table, $grammar);
    }

    public function arrange(): array {
        if(count($this->pkColumns) === 1) {
            $this->pKeyClauses[] = $this->grammar->compilePrimaryKey($this->pkColumns);
        }else {
            $this->pKeyClauses[] = $this->grammar->compilePrimaryKeyConstraint($this->table, $this->pkColumns);
        }

        return Ary::merge($this->preparedColumns, $this->pKeyClauses, $this->fKeyClauses);
    }

    /**
     * @throws ForeignKeyException
     */
    private function prepare(Table $table, Grammar $grammar): void {
        $columns = $table->getColumns();
        $attributes = "";

        foreach($columns as $column) {
            if(!empty($name = $column->getName())) {
                $attributes = $name;
            }

            if(!empty($type = $column->getType())) {
                $attributes .= " {$type}({$column->getWidth()})";
            }

            if($column->isNullable()) {
                $attributes .= " {$grammar->compileNotNull()}";
            }

            if($column->isAutoIncrement()) {
                $attributes .= " {$grammar->compileAutoIncrement()}";
            }

            if($column->isUnique()) {
                $attributes .= " {$grammar->compileUnique()}";
            }

            if(!empty($default = $column->getDefault())) {
                $attributes .= " {$grammar->compileDefault($default)}";
            }

            if(!empty($condition = $column->getCheckable())) {
                $attributes .= " {$grammar->compileCheck($condition)}";
            }

            if($column->isPrimaryKey()) {
                $this->pkColumns[] = $column->getName();
            }

            if($column->isForeignKey()) {
                if(empty($column->getReferences())) {
                    throw new ForeignKeyException("Foreign key references is not set.");
                }

                $this->fKeyClauses[] = $grammar->compileForeignKey($this->table, $column);
            }

            $this->preparedColumns[] = $attributes;
            $attributes = "";
        }
    }
}