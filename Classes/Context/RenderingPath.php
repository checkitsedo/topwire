<?php
declare(strict_types=1);
namespace Topwire\Context;

class RenderingPath implements \JsonSerializable
{
    private string $renderingPath;

    public function __construct(string $renderingPath)
    {
        $this->renderingPath = $renderingPath;
    }

    public function jsonSerialize(): string
    {
        return $this->renderingPath;
    }
}