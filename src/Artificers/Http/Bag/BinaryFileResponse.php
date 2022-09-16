<?php
declare(strict_types=1);
namespace Artificers\Http\Bag;

use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;

/**
 * BinaryFileResponse represents an HTTP response delivering a file.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class BinaryFileResponse extends SymfonyBinaryFileResponse {

    /**
     * @param SplFileInfo|string    $file           The file to stream
     * @param string|null           $filename       File name
     * @param array                 $headers        Headers that will send with response
     * @param string                $disposition    The type of Content-Disposition to set automatically with the filename
     */
    public function __construct(SplFileInfo|string $file, string $filename = null, array $headers = [], string $disposition = 'attachment') {
        parent::__construct($file, 200, $headers, true, $disposition);
    }
}