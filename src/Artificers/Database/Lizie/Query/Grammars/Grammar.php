<?php

namespace Artificers\Database\Lizie\Query\Grammars;

use Artificers\Utility\Ary;

abstract class Grammar {

    /**
     * Convert array elements into string with specified separator.
     * @param array $elements
     * @param string $separator
     * @return string
     */
    protected function _mapToString(array $elements, string $separator = ''): string {
        return implode($separator, $elements);
    }

    /**
     * Prepare conditional clause.
     * @param array|bool $clauses
     * @return array
     */
    protected function _prepareClause(array|bool $clauses): array {
        $conditions = [];

        if($clauses) {
            foreach($clauses as $clause) {
                if(isset($clause[1])) {
                    $operator = $clause[1];
                    unset($clause[1]);
                    $conditions = Ary::merge($conditions, (array)implode($operator, $clause));
                }else {
                    $conditions = Ary::merge($conditions, $clause);
                }
            }
        }

        return $conditions;
    }

    /**
     * Extract the table name and alias.
     * @param array $data
     * @return array
     */
    protected function extractTableAndAlias(array & $data): array {
        $tblProps = [];

        if(isset($data['table']) && isset($data['as'])) {
            $tblProps["name"] = $data['table'];
            $tblProps["alias"] = $data['as'];
            unset($data["table"]);
            unset($data["as"]);
        }

        return $tblProps;
    }
}