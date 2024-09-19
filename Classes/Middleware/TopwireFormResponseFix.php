<?php

namespace Topwire\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TopwireFormResponseFix implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Den RequestHandler ausführen, um die Response zu erhalten
        $response = $handler->handle($request);
        
        // Überprüfen, ob die Anforderung eine POST-Anfrage ist
        if ($request->getMethod() !== 'POST') {
            return $response;
        }

        // Überprüfen, ob der Header "Location" in der Response vorhanden ist
        if ($response->hasHeader('Location')) {
            return $response;
        }

        // Überprüfen, ob der Header "Turbo-Frame" im Request vorhanden ist
        if ($request->hasHeader('Turbo-Frame')) {
            return $response;
        }

        // Überprüfen, ob der Header "Accept" den Typ "text/vnd.turbo-stream.html" enthält
        $acceptHeader = $request->getHeaderLine('Accept');
        if (strpos($acceptHeader, 'text/vnd.turbo-stream.html') === false) {
            return $response;
        }

        // Rückgabe der Response mit Status 422 und Nachricht "Form invalid"
        return $response->withStatus(422, 'Form invalid');
    }
}