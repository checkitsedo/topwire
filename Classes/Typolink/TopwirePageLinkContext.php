<?php
declare(strict_types=1);
namespace Topwire\Typolink;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TopwirePageLinkContext
{
    /**
     * @var ContentObjectRenderer
     */
    private $contentObjectRenderer;

    /**
     * @var TypoScriptFrontendController
     */
    private $frontendController;

    public function __construct(
        ContentObjectRenderer $contentObjectRenderer,
        TypoScriptFrontendController $frontendController
    ) {
        $this->contentObjectRenderer = $contentObjectRenderer;
        $this->frontendController = $frontendController;
    }

    /**
     * @return ContentObjectRenderer
     */
    public function getContentObjectRenderer(): ContentObjectRenderer
    {
        return $this->contentObjectRenderer;
    }

    /**
     * @return TypoScriptFrontendController
     */
    public function getFrontendController(): TypoScriptFrontendController
    {
        return $this->frontendController;
    }
}