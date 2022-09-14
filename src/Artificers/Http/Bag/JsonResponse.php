<?php
declare(strict_types=1);
namespace Artificers\Http\Bag;

use Artificers\Support\JSON;
use Artificers\Treaties\Support\Jsonable;
use JsonSerializable;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

class JsonResponse extends SymfonyJsonResponse {
    public function __construct(mixed $data = null, int $status = 200, array $headers = [], int $options = 0, bool $json = false) {
        $this->encodingOptions = $options;
        parent::__construct($data, $status, $headers, $json);
    }

    /**
     * @param bool|null $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    public function getData(?bool $assoc = null, int $depth = 512, int $options = 0): mixed {
        return JSON::parse($this->data, $assoc, $depth, $options);
    }

    /**
     * @inheritDoc
     * @return $this
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
     * @return static
     */
    public static function fromJsonString(string $data, int $status = 200, array $headers = []): static {
        return parent::fromJsonString($data, $status, $headers);
    }
}