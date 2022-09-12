<?php

namespace Artificers\Database\Lizie\Schema;

use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Grammars\Grammar;
use Artificers\Utility\Ary;

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
    public function __construct(Table $table, Grammar $grammar, array $columns = []) {
        $this->table = $table;
        $this->grammar = $grammar;
        $this->prepare($table, $grammar, $columns);
    }

    /**
     * Arrange all the columns and primary key constraint.
     * @return array
     */
    public function arrange(): array {
        if(count($this->pkColumns) === 1) {
            $this->pKeyClauses[] = $this->grammar->burnPrimaryKey($this->pkColumns);
        }

        if(count($this->pkColumns) > 1) {
            $this->pKeyClauses[] = $this->grammar->burnPrimaryKeyConstraint($this->table, $this->pkColumns);
        }

        return Ary::merge($this->preparedColumns, $this->pKeyClauses, $this->fKeyClauses);
    }

    /**
     * Prepare all the table columns to appropriate SQL style.
     * @param Table $table
     * @param Grammar $grammar
     * @param array $columns
     * @throws ForeignKeyException
     */
    private function prepare(Table $table, Grammar $grammar, array $columns=[]): void {
        $columns = empty($columns)? $table->getColumns() : $columns;
        $attributes = "";

        foreach($columns as $column) {
            if(!empty($name = $column->getName())) {
                $attributes = $name;
            }

            if(!empty($type = $column->getType())) {

                if($type === ColumnType::ENUM || $type === ColumnType::SET) {
                    $attributes .= " {$type}(".implode(',', $column->getOptions()).")";
                }else if($type !== ColumnType::DTIME && $type !== ColumnType::TIME && $type !== ColumnType::TIMESTAMP) {
                    $attributes .= " {$type}({$column->getWidth()})";
                }else {
                    $attributes .= " {$type}";
                }
            }

            if($column->isNullable()) {
                $attributes .= " {$grammar->burnNullAble()}";
            }else {
                $attributes .= " {$grammar->burnNotNullAble()}";
            }

            if($column->isAutoIncrement()) {
                $attributes .= " {$grammar->burnAutoIncrement()}";
            }

            if($column->isUnique()) {
                $attributes .= " {$grammar->burnUnique()}";
            }

            if(!empty($default = $column->getDefault())) {
                $attributes .= " {$grammar->burnDefault($default)}";
            }

            if(!empty($condition = $column->getCheckable())) {
                $attributes .= " {$grammar->burnCheck($condition)}";
            }

            if($column->isPrimaryKey()) {
                $this->pkColumns[] = $column->getName();
            }

            if($column->isForeignKey()) {
                if(empty($column->getReferences())) {
                    throw new ForeignKeyException("Foreign key references is not set.");
                }

                $this->fKeyClauses[] = $grammar->burnForeignKey($this->table, $column);
            }

            $this->preparedColumns[] = $attributes;
            $attributes = "";
        }
    }
}