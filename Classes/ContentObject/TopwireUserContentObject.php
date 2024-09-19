<?php
namespace Topwire\ContentObject;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\UserContentObject;

class TopwireUserContentObject extends UserContentObject
{
    public const NAME = 'USER';

    /**
     * @param array<mixed> $conf
     * @return string
     */
    public function render($conf = [])
    {
        // Holen der topwire-Daten aus der cObj-Elternstruktur
        $contextAndRequestFix = $this->cObj->parentRecord['data']['topwire'] ?? null;
        
        // Sicherstellen, dass es ein ContentObjectRenderer ist und die Daten existieren
        if ($this->cObj instanceof ContentObjectRenderer && is_array($contextAndRequestFix)) {
            // Setze Attribute im Request über TSFE, da direkte Request-Manipulation in v10.4 nicht unterstützt wird
            $request = $GLOBALS['TSFE']->getRequest();
            if ($request) {
                $request = $request
                    ->withAttribute('routing', $contextAndRequestFix['routing'])
                    ->withAttribute('topwire', $contextAndRequestFix['context']);
                $GLOBALS['TSFE']->setRequest($request); // Setze den neuen Request im TSFE
                $this->cObj->setRequest($request); // Aktualisiere den Request im ContentObjectRenderer
            }
        }

        // Rufe das Elternrendering auf (Standardverhalten von USER)
        return parent::render($conf);
    }
}