<?php

namespace Artificers\Database\Lizie\Schema\Grammars;

use Artificers\Database\Lizie\Schema\Arranger;
use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Table;

abstract class Grammar {

    /**
     * @throws ForeignKeyException
     */
    protected function arrangeColumns(Table $table): array {
        return (new Arranger($table, $this))->arrange();
    }

    protected function colMapToString(array $columns): string {
        return implode(", ", $columns);
    }
}