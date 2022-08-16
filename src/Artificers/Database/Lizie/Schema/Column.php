<?php

namespace Artificers\Database\Lizie\Schema;

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

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function type(string $type): static {
        $this->type = $type;
        return $this;
    }

    public function nullable(): static {
        $this->nullable = true;
        return $this;
    }

    public function unique(): static {
        $this->unique = true;
        return $this;
    }

    public function autoIncrement(): static {
        $this->autoIncrement = true;
        return $this;
    }

    public function width(int $width): static {
        $this->width = $width;
        return $this;
    }

    public function primaryKey(): static {
        $this->primaryKey = true;
        return $this;
    }

    public function foreignKey(): static {
        $this->foreignKey = true;
        return $this;
    }

    public function referenceTo(string $tableName, string $columnName): static {
        $this->references = "{$tableName}({$columnName})";
        return $this;
    }

    public function checkAble(mixed $condition): static {
        $this->checkAble = $condition;
        return $this;
    }

    public function default(mixed $value): static {
        $this->default = $value;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getWidth(): string {
        return (string)$this->width;
    }

    public function isNullable(): bool {
        return $this->nullable;
    }

    public function isUnique(): bool {
        return $this->unique;
    }

    public function isPrimaryKey(): bool {
        return $this->primaryKey;
    }

    public function isForeignKey(): bool {
        return $this->foreignKey;
    }

    public function isIndex(): bool {
        return $this->indexing;
    }

    public function getCheckAble(): mixed {
        return $this->checkAble;
    }

    public function isAutoIncrement(): bool {
        return $this->autoIncrement;
    }

    public function getDefault(): mixed {
        return $this->default;
    }

    public function getReferences(): string {
        return $this->references;
    }
}