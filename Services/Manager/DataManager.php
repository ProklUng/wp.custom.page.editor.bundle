<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Manager;

use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;

/**
 * Class DataManager
 * База для классов, описывающих сущность.
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Manager
 *
 * @since 29.03.2021
 */
class DataManager implements DataManagerInterface
{
    /**
     * @inheritDoc
     */
    public function getTableName() : string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getEntityClass()
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEntityName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getAdminGeneratorClass() : string
    {
        return BaseAdminEntityManager::class;
    }

    /**
     * @inheritDoc
     */
    public function getMap(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getColumnsData(string $field) : array
    {
        $map = $this->getMap();
        $result = [];

        foreach ($map as $item) {
            $data = array_key_exists($field, $item) ? $item[$field] : false;
            $result[$item['name']] = $data;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getListenerEvents() : array
    {
        return [];
    }
}
