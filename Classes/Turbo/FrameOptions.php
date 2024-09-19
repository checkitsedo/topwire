<?php
declare(strict_types=1);
namespace Topwire\Turbo;

class FrameOptions
{
    public bool $wrapResponse;
    public ?string $src;
    public ?string $target;
    public bool $propagateUrl;
    public bool $morph;
    public ?string $pageTitle;
    public array $additionalAttributes;

    /**
     * @param array<string, string> $additionalAttributes
     */
    public function __construct(
        bool $wrapResponse = false,
        ?string $src = null,
        ?string $target = null,
        bool $propagateUrl = false,
        bool $morph = false,
        ?string $pageTitle = null,
        array $additionalAttributes = []
    ) {
        $this->wrapResponse = $wrapResponse;
        $this->src = $src;
        $this->target = $target;
        $this->propagateUrl = $propagateUrl;
        $this->morph = $morph;
        $this->pageTitle = $pageTitle;
        $this->additionalAttributes = $additionalAttributes;
    }
}