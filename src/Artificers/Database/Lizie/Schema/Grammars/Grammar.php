<?php

namespace Artificers\Database\Lizie\Schema\Grammars;

use Artificers\Database\Lizie\Schema\Arranger;
use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Table;

abstract class Grammar {

    /**
     * Arrange the table columns.
     * @param Table $table
     * @param array $columns
     * @return array
     * @throws ForeignKeyException
     */
    protected function arrangeColumns(Table $table, array $columns = []): array {
        return (new Arranger($table, $this, $columns))->arrange();
    }

    /**
     * Maps all prepared columns into comma separated string.
     *
     * @param array $columns
     * @param string $prefix
     * @return string
     */
    protected function colMapToString(array $columns, string $prefix = ''): string {
        return implode(",{$prefix} ", $columns);
    }
}