<?php
declare(strict_types=1);

namespace Topwire\Context;

use Topwire\Turbo\Frame;

class ContextDenormalizer
{
    private const ATTRIBUTE_MAP = [
        'frame' => Frame::class,
    ];

    /**
     * @param array{renderingPath: string, contextRecord: array{tableName: string, id: int, pageId: int}, attributes?: array<string, mixed>} $data
     * @throws Exception\TableNameNotFound
     */
    public function denormalize(array $data): TopwireContext
    {
        // Create RenderingPath and ContextRecord without named arguments
        $renderingPath = new RenderingPath($data['renderingPath']);
        $contextRecord = new ContextRecord(
            $data['contextRecord']['tableName'],
            $data['contextRecord']['id'],
            $data['contextRecord']['pageId']
        );

        $context = new TopwireContext(
            $renderingPath,
            $contextRecord
        );

        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $name => $attributeData) {
                if (isset(self::ATTRIBUTE_MAP[$name])) {
                    /** @var Attribute $className */
                    $className = self::ATTRIBUTE_MAP[$name];
                    $attribute = $className::denormalize($attributeData, ['context' => $context]);
                    if ($attribute instanceof Attribute) {
                        $context = $context->withAttribute($name, $attribute);
                    }
                }
            }
        }

        return $context;
    }
}