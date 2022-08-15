<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie;

final class Type {
    /**
     * Represents the SQL NULL data type.
     */
    public const PARAM_NULL = 0;

    /**
     * Represents the SQL INTEGER data type.
     */
    public const PARAM_INT = 1;

    /**
     * Represents the SQL CHAR, VARCHAR, or other string data type.
     */
    public const PARAM_STR = 2;

    /**
     * Represents the SQL large object data type.
     */
    public const PARAM_LOB = 3;

    /**
     * Represents a boolean data type.
     */
    public const PARAM_BOOL = 5;

    private function __construct() {

    }
}