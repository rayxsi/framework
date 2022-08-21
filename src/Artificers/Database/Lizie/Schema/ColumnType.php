<?php

namespace Artificers\Database\Lizie\Schema;

final class ColumnType {

    public const CHAR = "CHAR";
    public const VARCHAR = "VARCHAR";
    public const BINARY = "BINARY";
    public const VARBINARY = "VARBINARY";

    /**
     * Tiny binary large object data type.
     *
     * @const TBLOB
     */
    public const TBLOB = "TINYBLOB";

    /**
     * Medium binary large object data type.
     *
     * @const MBLOB
     */
    public const MBLOB = "MEDIUMBLOB";

    /**
     * Large binary large object data type.
     *
     * @const LBLOB
     */
    public const LBLOB = "LONGBLOB";

    public const BLOB = "BLOB";

    /**
     * Tiny text data type.
     *
     * @const TTEXT
     */
    public const TTEXT = "TINYTEXT";

    /**
     * Medium text data type.
     *
     * @const MTEXT
     */
    public const MTEXT = "MEDIUMTEXT";

    /**
     * Large text data type.
     *
     * @const LTEXT
     */
    public const LTEXT = "LONGTEXT";

    public const TEXT = "TEXT";

    public const ENUM = "ENUM";

    public const SET = "SET";

    public const INT = "INT";

    /**
     * Tiny integer data type.
     *
     * @const TINT
     */
    public const TINT = "TINYINT";

    /**
     * Medium integer data type.
     *
     * @const MINT
     */
    public const MINT = "MEDIUMINT";

    /**
     * Small integer data type.
     *
     * @const SINT
     */
    public const SINT = "SMALLINT";

    /**
     * Big integer data type.
     *
    * @const BINT
     */
    public const BINT = "BIGINT";

    public const DOUBLE = "DOUBLE";
    public const FLOAT = "FLOAT";

    public const BIT = "BIT";
    public const BOOL = "BOOL";

    public const DATE = "DATE";
    public const TIME = "TIME";

    /**
     * Date time data type.
     *
     * @const DTIME
     */
    public const DTIME = "DATETIME";

    public const YEAR = "YEAR";

    public const TIMESTAMP = "TIMESTAMP";

    private function __construct(){}
}