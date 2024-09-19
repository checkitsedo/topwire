<?php
declare(strict_types=1);
namespace Topwire\Context;

use Topwire\ViewHelpers\ContextViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

class ContextStack
{
    /**
     * @var TopwireContext[]
     */
    private array $stack = [];
    private ContextStack $currentStack;

    public function __construct(ViewHelperVariableContainer $variableContainer)
    {
        $this->currentStack = $variableContainer->get(ContextViewHelper::class, ContextViewHelper::currentTopwireContext) ?? $this;
        if ($this->currentStack === $this) {
            $variableContainer->add(ContextViewHelper::class, ContextViewHelper::currentTopwireContext, $this);
        }
    }

    public function push(TopwireContext $context): void
    {
        $this->currentStack->stack[] = $context;
    }

    public function pop(): TopwireContext
    {
        if (count($this->currentStack->stack) < 1) {
            // @todo use more specific exception
            throw new \LogicException('Cannot pop off component info from empty stack', 1671619657);
        }
        return array_pop($this->currentStack->stack);
    }

    public function current(): ?TopwireContext
    {
        if ($this->currentStack->stack === []) {
            return null;
        }
        return $this->currentStack->stack[array_key_last($this->currentStack->stack)];
    }
}