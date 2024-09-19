<?php
declare(strict_types=1);

namespace Topwire\Context;

use Psr\Http\Message\ServerRequestInterface;

class TopwireContext implements \JsonSerializable
{
    public const headerName = 'Topwire-Context';
    public const argumentName = 'tx_topwire';

    public string $scope;
    public string $cacheId;

    /**
     * @var array<string, Attribute>
     */
    private array $attributes = [];

    private RenderingPath $renderingPath;
    private ContextRecord $contextRecord;

    public function __construct(
        RenderingPath $renderingPath,
        ContextRecord $contextRecord,
        ?string $cacheId = null
    ) {
        $this->renderingPath = $renderingPath;
        $this->contextRecord = $contextRecord;
        $this->scope = md5(
            $this->renderingPath->jsonSerialize()
            . $this->contextRecord->tableName
            . $this->contextRecord->id
        );
        $this->cacheId = $cacheId ?? ($this->scope . $this->contextRecord->pageId);
    }

    public static function fromUntrustedString(string $untrustedString, ContextDenormalizer $denormalizer): self
    {
        $decodedString = TopwireHash::fromUntrustedString($untrustedString)->secureString;
        $data = \json_decode($decodedString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON data: ' . json_last_error_msg());
        }

        return $denormalizer->denormalize($data);
    }

    public static function isRequestSubmitted(?ServerRequestInterface $request): bool
    {
        return isset($request) && (isset($request->getQueryParams()[self::argumentName]) || $request->hasHeader(self::headerName));
    }

    public function toHashedString(): string
    {
        $json = \json_encode($this);
        if ($json === false) {
            throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }
        return (new TopwireHash($json))->hashedString;
    }

    public function withContextRecord(ContextRecord $contextRecord): self
    {
        $context = new self($this->renderingPath, $contextRecord);
        $context->attributes = $this->attributes;
        return $context;
    }

    public function withAttribute(string $name, Attribute $attribute): self
    {
        $newContext = new self(
            $this->renderingPath,
            $this->contextRecord,
            $this->cacheId . $attribute->getCacheId()
        );
        $newContext->attributes = $this->attributes;
        $newContext->attributes[$name] = $attribute;
        return $newContext;
    }

    public function getAttribute(string $name): ?Attribute
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @return array{renderingPath: RenderingPath, contextRecord: ContextRecord, attributes?: array<string, Attribute>}
     */
    public function jsonSerialize(): array
    {
        $normalizedContext = [
            'renderingPath' => $this->renderingPath,
            'contextRecord' => $this->contextRecord,
        ];
        $attributes = array_filter(
            $this->attributes,
            function (Attribute $attribute): bool {
                return $attribute->jsonSerialize() !== null;
            }
        );
        if ($attributes !== []) {
            $normalizedContext['attributes'] = $attributes;
        }
        return $normalizedContext;
    }
}