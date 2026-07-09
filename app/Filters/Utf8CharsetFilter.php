<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Utf8CharsetFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $contentType = strtolower($response->getHeaderLine('Content-Type'));

        if ($contentType === '' || str_starts_with($contentType, 'text/html')) {
            $response->setContentType('text/html', 'UTF-8');
        }

        return null;
    }
}
