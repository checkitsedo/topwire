<?php
declare(strict_types=1);
namespace Topwire\Typolink;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\PageLinkBuilder;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class TopwirePageLinkBuilder extends PageLinkBuilder
{
    /**
     * @var AbstractTypolinkBuilder|null
     */
    private $originalPageLinkBuilder;

    /**
     * @var TopwirePageLinkContext
     */
    private $pageLinkContext;

    public function __construct(
        ContentObjectRenderer $contentObjectRenderer,
        TypoScriptFrontendController $typoScriptFrontendController = null
    ) {
        parent::__construct($contentObjectRenderer, $typoScriptFrontendController);

        $defaultLinkBuilderClass = $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['overriddenDefault'] ?? null;
        if (is_string($defaultLinkBuilderClass)
            && is_subclass_of($defaultLinkBuilderClass, AbstractTypolinkBuilder::class)
        ) {
            $this->originalPageLinkBuilder = GeneralUtility::makeInstance(
                $defaultLinkBuilderClass,
                $contentObjectRenderer,
                $typoScriptFrontendController
            );
        } else {
            $this->originalPageLinkBuilder = null;
        }

        $this->pageLinkContext = new TopwirePageLinkContext($contentObjectRenderer, $this->getTypoScriptFrontendController());
    }

    /**
     * @param array<mixed> $linkDetails
     * @param array<mixed> $conf
     * @throws UnableToLinkException
     */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): LinkResultInterface
    {
        $linkDetails['topwirePageLinkContext'] = $this->pageLinkContext;
        if ($this->originalPageLinkBuilder !== null) {
            return $this->originalPageLinkBuilder->build($linkDetails, $linkText, $target, $conf);
        }
        return parent::build($linkDetails, $linkText, $target, $conf);
    }
}