<?php
declare(strict_types=1);
namespace Artificers\Http;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Response class handle http response. It extends Symfony\Component\HttpFoundation\Response base class.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class Response extends SymfonyResponse{

    /**
     * @param string|null   $content    Data that will send back to client.
     * @param int           $status     Http status code.
     * @param array         $headers    Http headers.
     */
    public function __construct(?string $content = '', int $status = 200, array $headers = []) {
        parent::__construct($content, $status, $headers);
    }

}