<?php
declare(strict_types=1);
namespace Artificers\Support;

use InvalidArgumentException;

class JSON {
    protected static int $encodingOption;

    /**
     * Encode into JSON.
     * @param mixed $data
     * @param int $options
     * @param int $depth
     * @return bool|string
     */
    public static function stringify(mixed $data, int $options = 0, int $depth = 512): bool|string {
        static::$encodingOption = $options;

        $jsonData = json_encode($data, $options, $depth);

        if(!static::isValidJson(json_last_error())) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $jsonData;
    }

    /**
     * Decode JSON data.
     * @param string $json
     * @param bool|null $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    public static function parse(string $json, ?bool $assoc = null, int $depth = 512, int $options = 0): mixed {
        return json_decode($json, $assoc, $depth, $options);
    }

    /**
     * Check data that was converted into JSON is valid.
     * @param int $jsonError
     * @return bool
     */
    protected static function isValidJson(int $jsonError): bool {
        if($jsonError === JSON_ERROR_NONE) {
            return true;
        }

        return static::checkEncodingOption(JSON_PARTIAL_OUTPUT_ON_ERROR) && in_array($jsonError, [
                JSON_ERROR_DEPTH,
                JSON_ERROR_INF_OR_NAN,
                JSON_ERROR_RECURSION,
                JSON_ERROR_SYNTAX,
                JSON_ERROR_UNSUPPORTED_TYPE,
                JSON_ERROR_STATE_MISMATCH,
                JSON_ERROR_UTF8,
                JSON_ERROR_UTF16,
                JSON_ERROR_CTRL_CHAR
            ]);
    }

    /**
     * Check JSON encoding option.
     * @param $option
     * @return bool
     */
    protected static function checkEncodingOption($option): bool {
        return (bool) (static::$encodingOption === $option);
    }
}