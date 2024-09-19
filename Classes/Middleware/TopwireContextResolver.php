<?php

declare(strict_types=1);

namespace Topwire\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Topwire\Context\ContextDenormalizer;
use Topwire\Context\TopwireContext;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Routing\PageArguments;

class TopwireContextResolver implements MiddlewareInterface
{
    private FrontendInterface $cache;

    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = null;
        $contextString = $request->getQueryParams()[TopwireContext::argumentName] ?? $request->getHeaderLine(TopwireContext::headerName);
        if ($contextString !== '') {
            $context = TopwireContext::fromUntrustedString($contextString, new ContextDenormalizer());
        }

        $pageArguments = $request->getAttribute('routing');
        if (!$context instanceof TopwireContext || !$pageArguments instanceof PageArguments) {
            return $this->addVaryHeader($handler->handle($request->withAttribute('topwire', null)));
        }

        $cacheId = $context->cacheId;
        $frame = $context->getAttribute('frame');
        if ($context->contextRecord->pageId !== $pageArguments->getPageId()) {
            if (!$this->isPageBoundaryCrossingAllowed($context, $request)) {
                return $this->addVaryHeader($handler->handle($request->withAttribute('topwire', null)));
            }
            $context = null;
        }

        $newStaticArguments = array_merge(
            $pageArguments->getStaticArguments(),
            [TopwireContext::argumentName => $cacheId]
        );

        $modifiedPageArguments = new PageArguments(
            $pageArguments->getPageId(),
            $pageArguments->getPageType(),
            $pageArguments->getRouteArguments(),
            $newStaticArguments,
            $pageArguments->getDynamicArguments()
        );

        $request = $request
            ->withAttribute('routing', $modifiedPageArguments)
            ->withAttribute('topwire', $context)
            ->withAttribute('topwireFrame', $frame);

        return $this->addVaryHeader($this->trackPageBoundaries($handler->handle($request), $context));
    }

    private function addVaryHeader(ResponseInterface $response): ResponseInterface
    {
        $varyHeader = $response->getHeader('Vary');
        $varyHeader[] = TopwireContext::headerName;
        return $response->withAddedHeader('Vary', $varyHeader);
    }

    private function isPageBoundaryCrossingAllowed(TopwireContext $context, RequestInterface $request): bool
    {
        if (Environment::getContext()->isDevelopment()) {
            return true;
        }
        $allowedUris = $this->cache->get($this->getCacheIdentifier($context));
        $allowedUris = $allowedUris === false ? [] : $allowedUris;
        return isset($allowedUris[(string)$request->getUri()]);
    }

    private function trackPageBoundaries(ResponseInterface $response, ?TopwireContext $context): ResponseInterface
    {
        if (!$context instanceof TopwireContext || $response->getHeaderLine('Location') === '') {
            return $response;
        }
        $cacheIdentifier = $this->getCacheIdentifier($context);
        $allowedUris = $this->cache->get($cacheIdentifier);
        $allowedUris = $allowedUris === false ? [] : $allowedUris;
        $allowedUris[$response->getHeaderLine('Location')] = true;
        $this->cache->set(
            $cacheIdentifier,
            $allowedUris,
            ['pageId_' . $context->contextRecord->pageId]
        );
        return $response;
    }

    private function getCacheIdentifier(TopwireContext $context): string
    {
        return 'topwire_' . $context->contextRecord->pageId;
    }
}