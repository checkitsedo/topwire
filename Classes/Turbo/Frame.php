<?php
declare(strict_types=1);
namespace Topwire\Turbo;

use Topwire\Context\Attribute;

class Frame implements Attribute
{
    private const ID_SEPARATOR_TOKEN = '__';
    private string $id;

    public function __construct(
        public string $baseId,
        public bool $wrapResponse,
        public ?string $scope,
    ) {
        $this->id = $baseId
            . ($scope === null ? '' : self::ID_SEPARATOR_TOKEN . $scope);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $context
     * @return self
     */
    public static function denormalize(array $data, array $context = []): self
    {
        return new Frame(
            $data['baseId'],
            isset($data['wrapResponse']) ? $data['wrapResponse'] : false,
            isset($data['scope']) ? $data['scope'] : (isset($context['context']) ? $context['context']['scope'] : null)
        );
    }

    public function getCacheId(): string
    {
        return $this->wrapResponse ? $this->baseId : '';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            'baseId' => $this->baseId,
        ];
        if ($this->wrapResponse) {
            $data['wrapResponse'] = $this->wrapResponse;
            $data['scope'] = $this->scope;
        }
        return $data;
    }
}