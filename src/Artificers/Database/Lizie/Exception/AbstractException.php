<?php
declare(strict_types=1);
namespace Artificers\Database\Lizie\Exception;

use Artificers\Treaties\Database\Driver\Exception as ExceptionTreaties;
use Exception as BaseException;

class AbstractException extends BaseException implements ExceptionTreaties {

}