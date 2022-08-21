<?php

namespace Artificers\Database\Lizie\Schema;

use Artificers\Utilities\Ary;

class Column {
    private string $name;

    private string $type = ColumnType::VARCHAR;

    private int $width = 255;

    private bool $nullable = false;

    private bool $primaryKey = false;

    private bool $foreignKey = false;

    private bool $autoIncrement = false;

    private bool $unique = false;

    private mixed $checkAble = false;

    private mixed $default = false;

    private string $references = "";

    private bool $changeMarker = false;

    private bool $enum = false;

    private array $enumOptions = [];

    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Set the column type.
     * @param string $type
     * @return $this
     */
    public function type(string $type): static {
        $this->type = $type;
        return $this;
    }

    /**
     * Set null constraint.
     * @return $this
     */
    public function nullable(): static {
        $this->nullable = true;
        return $this;
    }

    /**
     * Set unique constraint.
     * @return $this
     */
    public function unique(): static {
        $this->unique = true;
        return $this;
    }

    /**Set auto increment constraint.
     * @return $this
     */
    public function autoIncrement(): static {
        $this->autoIncrement = true;
        return $this;
    }

    /**
     * Set length of a column.
     * @param int $width
     * @return $this
     */
    public function width(int $width): static {
        $this->width = $width;
        return $this;
    }

    /**
     * Set Enum and Set type values.
     * @param ...$args
     * @return $this
     */
    public function options(...$args): static {
        $this->enum = true;
        $this->enumOptions = Ary::map($args, fn($elem)=>"'".$elem."'");

        return $this;
    }

    /**
     * Set the column as change marker to modify existing column with this same name.
     * @return $this
     */
    public function change(): static {
        $this->changeMarker = true;

        return $this;
    }

    /**
     * Set a column as primary key.
     *
     * @return $this
     */
    public function primaryKey(): static {
        $this->primaryKey = true;
        return $this;
    }

    /**
     * Set a column as foreign key.
     * @return $this
     */
    public function foreignKey(): static {
        $this->foreignKey = true;
        return $this;
    }

    /**
     * Set reference table name and it's column.
     * @param string $tableName
     * @param string $columnName
     * @return $this
     */
    public function referenceTo(string $tableName, string $columnName): static {
        $this->references = "{$tableName}({$columnName})";
        return $this;
    }

    /**
     * Set check constraint.
     * @param mixed $condition
     * @return $this
     */
    public function checkAble(mixed $condition): static {
        $this->checkAble = $condition;
        return $this;
    }

    /**
     * Set default constraint.
     * @param mixed $value
     * @return $this
     */
    public function default(mixed $value): static {
        $this->default = $value;
        return $this;
    }

    /**
     * Return the current column name.
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Return the current column type.
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * Return the current column length.
     * @return string
     */
    public function getWidth(): string {
        return (string)$this->width;
    }

    /**
     * Check if nullable or not.
     *
     * @return bool
     */
    public function isNullable(): bool {
        return $this->nullable;
    }

    /**
     * Check if unique or not.
     * @return bool
     */
    public function isUnique(): bool {
        return $this->unique;
    }

    /**
     * Check if set primary key constraint.
     * @return bool
     */
    public function isPrimaryKey(): bool {
        return $this->primaryKey;
    }

    /**
     * Check if set foreign key constraint.
     * @return bool
     */
    public function isForeignKey(): bool {
        return $this->foreignKey;
    }

    /**
     * Check if set check constraint.
     * @return mixed
     */
    public function getCheckAble(): mixed {
        return $this->checkAble;
    }

    /**
     * Check if current column is auto incremented or not.
     * @return bool
     */
    public function isAutoIncrement(): bool {
        return $this->autoIncrement;
    }

    /**
     * Check if current column is marked for modifying existing column.
     * @return bool
     */
    public function isMarkedAsChange(): bool {
        return $this->changeMarker;
    }

    /**
     * Returns the default value.
     * @return mixed
     */
    public function getDefault(): mixed {
        return $this->default;
    }

    /**
     * Returns the reference information.
     * @return string
     */
    public function getReferences(): string {
        return $this->references;
    }

    /**
     * Returns the Enum and Set's options
     * @return array
     */
    public function getOptions(): array {
        return $this->enumOptions;
    }
}