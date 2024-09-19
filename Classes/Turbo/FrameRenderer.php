<?php
declare(strict_types=1);
namespace Topwire\Turbo;

use Topwire\Context\TopwireContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

class FrameRenderer
{
    public function render(Frame $frame, string $content, ?FrameOptions $options = null, ?TopwireContext $context = null): string
    {
        $tagBuilder = new TagBuilder('turbo-frame');
        $tagBuilder->setContent($content);
        $tagBuilder->addAttribute('id', $frame->id);
        
        if ($context !== null) {
            $tagBuilder->addAttribute('data-topwire-context', $context->toHashedString());
        }
        
        if ($options !== null) {
            if ($options->propagateUrl === true) {
                $tagBuilder->addAttribute('data-turbo-action', 'advance');
            }
            if ($options->morph === true) {
                $tagBuilder->addAttribute('data-topwire-morph', 'true');
            }
            if ($options->src !== null && $options->src !== '') {
                $tagBuilder->addAttribute('src', $options->src);
            }
            if ($options->target !== null && $options->target !== '') {
                $tagBuilder->addAttribute('target', $options->target);
            }
            if ($options->pageTitle !== null && $options->pageTitle !== '') {
                $tagBuilder->addAttribute('data-topwire-page-title', $options->pageTitle);
            }
            if (!empty($options->additionalAttributes)) {
                foreach ($options->additionalAttributes as $name => $value) {
                    $tagBuilder->addAttribute($name, $value);
                }
            }
        }

        return $tagBuilder->render();
    }
}