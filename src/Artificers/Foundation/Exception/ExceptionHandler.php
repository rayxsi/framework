<?php declare(strict_types=1);
namespace Artificers\Foundation\Exception;

use Artificers\Container\Container;
use Artificers\Database\Lizie\Exception\AbstractException;
use Artificers\Database\Lizie\Exception\DriverRequiredException;
use Artificers\Database\Lizie\Exception\LizieException;
use Artificers\Database\Lizie\Exception\UnknownDriverException;
use Artificers\Events\Exception\ListenerNotFoundException;
use Artificers\Events\Exception\NotValidMethodException;
use Artificers\Http\Exception\HttpException;
use Artificers\Http\Exception\NotFoundHttpException;
use Artificers\Http\Request;
use Artificers\Support\Concern\AboutResponse;
use Artificers\Support\Reflector;
use Artificers\Treaties\Exception\ExceptionHandler as ExceptionHandlerTreaties;
use Artificers\Treaties\Http\Exception\HttpExceptionTreaties;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExceptionHandler implements ExceptionHandlerTreaties {
    use AboutResponse;

    private Container $container;

    private array $frameCoreExceptions = [
        //Database Exceptions
        AbstractException::class,
        DriverRequiredException::class,
        LizieException::class,
        UnknownDriverException::class,
        //Http Exceptions
        HttpException::class,
        NotFoundHttpException::class,
        //Routing Exceptions
        NotFoundHttpException::class,
        //Events Exceptions
        ListenerNotFoundException::class,
        NotValidMethodException::class,
        //Application Exceptions
        ApplicationFailedException::class
    ];

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function throw(Request $request, Throwable $e): Response {
        return $this->render($request, $e);
    }

    public function report(Throwable $e): void {
        if(!$this->shouldReport($e)) {
            return;
        }
    }

    protected function shouldReport(throwable $e): bool {
        return in_array(Reflector::getClassName($e), $this->frameCoreExceptions);
    }

    public function render(Request $request, Throwable $e):  Response {
        return $this->responseWithSymfony($request, $e);
    }

    protected function getSymfonyHtmlErrorHandler() : HtmlErrorRenderer {
        $debug = filter_var(env('APP_DEBUG_MODE', false), FILTER_VALIDATE_BOOLEAN);
        return new HtmlErrorRenderer($debug);
    }

    public function responseWithSymfony(Request $request, Throwable $e): Response {
        if(!$e instanceof HttpExceptionTreaties) {
            $symfonyFlattenExp = $this->getSymfonyHtmlErrorHandler()->render($e);
            $e = new HttpException($symfonyFlattenExp->getStatusCode(), $symfonyFlattenExp->getAsString());//convert errors into http exception
        }

        return $this->prepareResponse($request, $e);
    }
}