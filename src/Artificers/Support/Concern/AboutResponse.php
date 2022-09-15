<?php
declare(strict_types=1);
namespace Artificers\Support\Concern;

use Artificers\Http\Request;
use Artificers\Support\Illusion\Response;
use Artificers\Treaties\Support\Jsonable;
use Artificers\Treaties\Support\Stringable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait AboutPrepareResponse {

    /**
     * Prepare response based on response data.
     * @param Request $request
     * @param mixed $response
     * @return SymfonyResponse
     */
    public function prepareResponse(Request $request, mixed $response): SymfonyResponse {
        if($response instanceof Stringable) {
            $response = Response::with(["Content-Type"=>"text/html"])->back($response->toString(), 200);
        }elseif(($response instanceof Jsonable || $response instanceof JsonSerializable)
            && !$response instanceof SymfonyResponse) {

            $response = Response::with(["Content-Type"=>"application/json"])->json($response, 200);
        }elseif(!$response instanceof SymfonyResponse) {
            $response = Response::with(["Content-Type"=>"text/html"])->back($response, 200);
        }

        return $response->prepare($request);
    }
}