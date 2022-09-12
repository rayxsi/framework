<?php
declare(strict_types= 1);

namespace Artificers\Foundation\Config;

use ErrorException;

class ErrorHandling {
    /**
     *Error Handle. Convert all errors to exception
     * @param $errno
     * @param $message
     * @param $filename
     * @param $lineno
     * @return void
     * @throws ErrorException
     */

    public static function errorHandling($errno, $message, $filename, $lineno): void {

        throw new ErrorException($message, 0, 1, $filename, $lineno);
    }

    /**
     *Exception Handle.
     * @param $exception
     * @return void
     * @throws ErrorException
     */
    public static function exceptionHandling($exception): void {
        $code = $exception->getCode() !== 404 ? 500 : $exception->getCode();
        http_response_code($code);

        if((bool)$_ENV['DEBUG_MODE'] === true) {
            echo "<h1 style='color: red;'>Fatal Error: </h1>";
            echo "<p>Uncaught exception: " . get_class($exception) . "</p>";
            echo "<p style='color: #323539'>Message: " . $exception->getMessage() . "</p>";
            echo "<p>Stack trace: " . $exception->getTraceAsString() . "</p>";
            echo "<p>Thrown in " . $exception->getFile() . " in line " . $exception->getLine() . "</p>";
        }else {
            $errorLog = LOG_DIR . DS . date("Y-m-d H:is") . ".log";
            ini_set('error_log', $errorLog);
            $message = "Uncaught exception: " . get_class($exception);
            $message .= "with message " . $exception->getMessage();
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in " . $exception->getFile() . " on line " . $exception->getLine();

            error_log($message);
        }
    }
}