<?php

namespace Artificers\Network\Http;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse{

    public function __construct(?string $content = '', int $status = 200, array $headers = []) {
        parent::__construct($content, $status, $headers);
    }

}