<?php
declare(strict_types=1);
namespace Topwire\Context;

use Topwire\Context\Exception\TableNameNotFound;

class ContextRecord implements \JsonSerializable
{
    public string $tableName;
    public int $id;
    public int $pageId;

    public function __construct(
        string $tableName,
        int $id,
        int $pageId
    ) {
        $this->tableName = $tableName;
        $this->id = $id;
        $this->pageId = $pageId;

        if (!isset($GLOBALS['TCA'][$tableName])) {
            throw new TableNameNotFound(sprintf('Table name "%s" is invalid', $tableName, 1671023687));
        }
    }

    /**
     * @return array{tableName: string, id: int, pageId: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'tableName' => $this->tableName,
            'id' => $this->id,
            'pageId' => $this->pageId,
        ];
    }
}