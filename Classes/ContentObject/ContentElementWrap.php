<?php
namespace Topwire\ContentObject;

use Topwire\ContentObject\Exception\InvalidTableContext;
use Topwire\Context\TopwireContext;
use Topwire\Context\TopwireContextFactory;
use Topwire\Turbo\Frame;
use Topwire\Turbo\FrameOptions;
use Topwire\Turbo\FrameRenderer;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectStdWrapHookInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentElementWrap implements ContentObjectStdWrapHookInterface
{
    /**
     * @param string $content
     * @param array<mixed> $configuration
     * @return string
     */
    public function stdWrapPreProcess($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        return $content;
    }

    /**
     * @param string $content
     * @param array<mixed> $configuration
     * @return string
     */
    public function stdWrapOverride($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        return $content;
    }

    /**
     * @param string $content
     * @param array<mixed> $configuration
     * @return string
     */
    public function stdWrapProcess($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        return $content;
    }

    /**
     * @param string $content
     * @param array<mixed> $configuration
     * @return string
     */
    public function stdWrapPostProcess($content, array $configuration, ContentObjectRenderer &$parentObject)
    {
        if ((int)$parentObject->stdWrapValue('turboFrameWrap', $configuration, 0) === 0) {
            return $content;
        }
        if ($parentObject->getRequest()->getAttribute('topwire') instanceof TopwireContext) {
            // Frame wrap is done by TOPWIRE content object automatically
            return $content;
        }
        if ($parentObject->getCurrentTable() !== 'tt_content') {
            throw new InvalidTableContext('"stdWrap.turboFrameWrap" can only be used for table "tt_content"', 1671124640);
        }

        $controller = $GLOBALS['TSFE'];  // Statt getRequest()
        assert($controller instanceof TypoScriptFrontendController);

        $path = $configuration['turboFrameWrap.']['path'] ?? $this->determineRenderingPath($controller, $parentObject, $configuration);
        $record = $parentObject->currentRecord;

        $contextFactory = new TopwireContextFactory($controller);
        $context = $contextFactory->forPath($path, $record);
        $scopeFrame = (bool)$parentObject->stdWrapValue('scopeFrame', $configuration['turboFrameWrap.'] ?? [], 1);
        $frameId = $parentObject->stdWrapValue('frameId', $configuration['turboFrameWrap.'] ?? [], null);
        
        // Klassische Parameter-Übergabe statt benannter Parameter
        $frame = new Frame(
            (string)($frameId ?? $parentObject->currentRecord),
            true, // wrapResponse
            $scopeFrame ? $context->scope : null
        );
        
        $showWhenFrameMatches = (bool)$parentObject->stdWrapValue('showWhenFrameMatches', $configuration['turboFrameWrap.'] ?? [], false);
        $requestedFrame = $GLOBALS['TSFE']->getRequest()->getAttribute('topwireFrame');
        
        if ($scopeFrame
            && $showWhenFrameMatches
            && (
                !$requestedFrame instanceof Frame
                || $requestedFrame->id !== $frame->id
            )
        ) {
            return '';
        }

        $context = $context->withAttribute('frame', $frame);
        
        return (new FrameRenderer())->render(
            $frame, // Frame
            $content, // Content
            new FrameOptions(
                (bool)$parentObject->stdWrapValue('propagateUrl', $configuration['turboFrameWrap.'] ?? [], 0),
                (bool)$parentObject->stdWrapValue('morph', $configuration['turboFrameWrap.'] ?? [], 0)
            ),
            $scopeFrame ? $context : null
        );
    }

    /**
     * @param array<mixed> $configuration
     * @throws InvalidTableContext
     * @throws ContentRenderingException
     */
    private function determineRenderingPath(TypoScriptFrontendController $controller, ContentObjectRenderer $cObj, array $configuration): string
    {
        $frontendTypoScript = $GLOBALS['TSFE']->getRequest()->getAttribute('frontend.typoscript');
        $setup = $frontendTypoScript?->getSetupArray();
        if (!$frontendTypoScript instanceof FrontendTypoScript) {
            // TYPO3 v11 compatibility
            $setup = $controller->tmpl->setup;
        }
        if (!isset($setup['tt_content'], $configuration['turboFrameWrap.'])) {
            throw new InvalidTableContext('"stdWrap.turboFrameWrap" can only be used for table "tt_content", typoscript setup missing!', 1687873940);
        }

        $frameWrapConfig = $configuration['turboFrameWrap.'];
        $paths = [
            'tt_content.',
            'tt_content.' . $cObj->data['CType'] . '.',
            'tt_content.' . $cObj->data['CType'] . '.20.',
        ];

        if ($cObj->data['CType'] === 'list') {
            $paths[] = 'tt_content.' . $cObj->data['CType'] . '.20.' . $cObj->data['list_type'] . '.';
        }

        foreach ($paths as $path) {
            try {
                $potentialWrapConfig = ArrayUtility::getValueByPath($setup, $path . '/stdWrap./turboFrameWrap.');
                if ($potentialWrapConfig === $frameWrapConfig) {
                    return rtrim(str_replace('./', '.', $path), '.');
                }
            } catch (MissingArrayPathException $e) {
                $potentialWrapConfig = [];
            }
        }

        return 'tt_content';
    }
}