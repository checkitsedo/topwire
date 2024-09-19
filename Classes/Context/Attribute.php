<?php

declare(strict_types=1);

namespace Topwire\Context;

interface Attribute extends \JsonSerializable
{
    public function getCacheId(): string;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $context
     * @return Attribute|null
     */
    public static function denormalize(array $data, array $context = []): ?Attribute;
}