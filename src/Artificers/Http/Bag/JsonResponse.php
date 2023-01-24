<?php
declare(strict_types=1);
namespace Artificers\Http\Bag;

use Artificers\Support\JSON;
use Artificers\Treaties\Support\Jsonable;
use JsonSerializable;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

/**
 * Works with json related response.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class JsonResponse extends SymfonyJsonResponse {

    /**
     * @param mixed     $data           Data that will response back to client.
     * @param int       $status         The http status code.
     * @param array     $headers        Headers that will send with the response to the client.
     * @param int       $options        JSON encoding option.
     * @param bool      $json           Tells the current passing data is json or not. If it is json then set true otherwise set false.
     */
    public function __construct(mixed $data = null, int $status = 200, array $headers = [], int $options = 0, bool $json = false) {
        $this->encodingOptions = $options;
        parent::__construct($data, $status, $headers, $json);
    }

    /**
     * @param   bool|null $assoc    Set return data as associative array.
     * @param   int $depth
     * @param   int $options        The JSON encoding option.
     * @return  mixed               JSON decoded data.
     */
    public function getData(?bool $assoc = null, int $depth = 512, int $options = 0): mixed {
        return JSON::parse($this->data, $assoc, $depth, $options);
    }

    /**
     * @inheritDoc
     * @return static   Current instance.
     */
    public function setData(mixed $data = []): static {

       if($data instanceof JsonSerializable) {
           $this->data = JSON::stringify($data->jsonSerialize(), $this->encodingOptions);
       }elseif($data instanceof Jsonable) {
           $this->data = JSON::stringify($data->toJson(), $this->encodingOptions);
       }else {
           $this->data = JSON::stringify($data, $this->encodingOptions);
       }

       return $this->update();
    }

    /**
     * @inheritDoc
     * @return static   Current instance.
     */
    public static function fromJsonString(string $data, int $status = 200, array $headers = []): static {
        return parent::fromJsonString($data, $status, $headers);
    }
}