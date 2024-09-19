<?php

declare(strict_types=1);

namespace Topwire\Context\Attribute;

use Topwire\Context\Attribute;

class Section implements Attribute
{
    public string $sectionName;

    public function __construct(string $sectionName)
    {
        $this->sectionName = $sectionName;
    }

    public function getCacheId(): string
    {
        return '';
    }

    public static function denormalize(array $data, array $context = []): ?Attribute
    {
        return null;
    }

    public function jsonSerialize(): array
    {
        return [
            'sectionName' => $this->sectionName,
        ];
    }
}