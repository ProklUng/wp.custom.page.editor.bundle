<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Events;

use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class EditFormAfterLoadDatabaseEvent
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Events
 *
 * @since 31.03.2021
 */
class EditFormAfterLoadDatabaseEvent extends Event
{
    /**
     * @var DataManagerInterface $entity Сущность.
     */
    private $entity;

    /**
     * @var array $fields Поля формы.
     */
    private $fields;

    /**
     * @var string $columnName Название столбца.
     */
    private $columnName;

    /**
     * AfterSaveDatabaseEvent constructor.
     *
     * @param DataManagerInterface $entity
     * @param array                $fields
     * @param string               $columnName
     */
    public function __construct(
        DataManagerInterface $entity,
        array $fields = [],
        string $columnName = ''
    ) {
        $this->fields = $fields;
        $this->columnName = $columnName;
        $this->entity = $entity;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @return DataManagerInterface
     */
    public function getEntity(): DataManagerInterface
    {
        return $this->entity;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }
}
