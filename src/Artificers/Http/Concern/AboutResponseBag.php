<?php declare(strict_types=1);
namespace Artificers\Http\Concern;

use Artificers\Http\Bag\BinaryFileResponse;
use Artificers\Http\Bag\JsonResponse;
use Artificers\Http\Bag\StreamedResponse;
use Artificers\Http\Response;
use Artificers\Utility\Ary;
use Artificers\Utility\Str;
use Closure;
use SplFileInfo;

/**
 * Works with all the available response.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
trait AboutResponseBag {

    /**
     * Create response and send back.
     * @param mixed     $content   Data that will response back to client.
     * @param int       $status     The http status code.
     * @param array     $headers    Headers that will send with the response to the client.
     * @return          Response    Response instance.
     */
    public function back(mixed $content, int $status=200, array $headers=[]): Response {
        return new Response($content, $status, Ary::merge($this->headers, $headers));
    }

    /**
     * Create JSON response and send back.
     * @param mixed     $data           Data that will response back to client.
     * @param int       $status         The http status code.
     * @param array     $headers        Headers that will send with the response to the client.
     * @param int       $options        JSON encoding option.
     * @param bool      $json           Tells the current passing data is json or not. If it is json then set true otherwise set false.
     * @return          JsonResponse    JsonResponse response instance.
     */
    public function json(mixed $data, int $status = 200, array $headers = [], int $options = 0, bool $json = false): JsonResponse {
        return new JsonResponse($data, $status, Ary::merge($this->headers, $headers), $options, $json);
    }

    /**
     * Add header to the current response.
     * @param array  $headers    Headers that will send with the response to the client.
     * @return       $this       Self instance.
     */
    public function with(array $headers): static {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Create stream response and send back.
     * @param Closure   $callback           A closure that will call.
     * @param int       $status             The http status code.
     * @param array     $headers            Headers that will send with the response to the client.
     * @return          StreamedResponse    StreamedResponse response instance.
     */
    public function stream(Closure $callback, int $status = 200, array $headers = []): StreamedResponse {
        return new StreamedResponse($callback, $status, Ary::merge($this->headers, $headers));
    }

    /**
     * Create JSONP response and send back.
     * @param string    $callback       Callback that will call by the backend.
     * @param mixed     $data           Data that will response back to client.
     * @param int       $status         The http status code.
     * @param array     $headers        Headers that will send with the response to the client.
     * @param int       $options        JSON encoding option.
     * @param bool      $json           Tells the current passing data is json or not. If it is json then set true otherwise set false.
     * @return          JsonResponse    JsonResponse response instance.
     */
    public function jsonp(string $callback, mixed $data = [], int $status = 200, array $headers = [], int $options = 0, bool $json = false): JsonResponse {
        $response = $this->json($data, $status, Ary::merge($this->headers, $headers), $options, $json);
        return $response->setCallback($callback);
    }

    /**
     * @param SplFileInfo|string    $file           The file to stream
     * @param string|null           $filename       File name
     * @param array                 $headers        Headers that will send with response
     * @param string                $disposition    The type of Content-Disposition to set automatically with the filename
     * @return BinaryFileResponse                   BinaryFileResponse response instance.
     */
    public function download(SplFileInfo|string $file, string $filename = null, array $headers = [], string $disposition = 'attachment'): BinaryFileResponse {
        $response = new BinaryFileResponse($file, $filename, Ary::merge($headers, $this->headers), $disposition);

        //checking if file name is null
        if(is_null($filename)) return $response;

        //make the content disposition-able
        return $response->setContentDisposition($disposition, $filename, Str::toAscii($filename));
    }
}