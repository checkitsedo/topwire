<?php

declare(strict_types=1);

namespace Topwire\Context\Attribute;

use Topwire\Context\Attribute;

class Plugin implements Attribute
{
    public string $pluginSignature;
    public string $extensionName;
    public string $pluginName;
    public string $pluginNamespace;
    public ?string $actionName;
    public bool $isOverride;
    public ?string $forRecord;
    public ?int $forPage;

    public function __construct(
        string $extensionName,
        string $pluginName,
        string $pluginNamespace,
        ?string $actionName = null,
        bool $isOverride = false,
        ?string $forRecord = null,
        ?int $forPage = null
    ) {
        $this->extensionName = $extensionName;
        $this->pluginName = $pluginName;
        $this->pluginNamespace = $pluginNamespace;
        $this->actionName = $actionName;
        $this->isOverride = $isOverride;
        $this->forRecord = $forRecord;
        $this->forPage = $forPage;
        $this->pluginSignature = strtolower($extensionName . '_' . $pluginName);
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
            'pluginSignature' => $this->pluginSignature,
            'extensionName' => $this->extensionName,
            'pluginName' => $this->pluginName,
            'pluginNamespace' => $this->pluginNamespace,
            'actionName' => $this->actionName,
            'isOverride' => $this->isOverride,
            'forRecord' => $this->forRecord,
            'forPage' => $this->forPage,
        ];
    }
}