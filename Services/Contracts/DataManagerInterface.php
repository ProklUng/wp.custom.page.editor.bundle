<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Contracts;

/**
 * Interface DataManagerInterface
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Contracts
 *
 * @since 28.03.2021
 */
interface DataManagerInterface
{
    /**
     * Returns DB table name for entity
     *
     * @return string
     */
    public function getTableName() : string;

    /**
     * Entity class.
     *
     * @return object|string
     */
    public function getEntityClass();

    /**
     * Entity name.
     *
     * @return string
     */
    public function getEntityName() : string;

    /**
     * Admin generator class.
     *
     * @return string
     */
    public function getAdminGeneratorClass() : string;

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public function getMap() : array;

    /**
     * Данные сущности по ключу.
     *
     * @param string $field Ключ.
     *
     * @return array
     */
    public function getColumnsData(string $field) : array;

    /**
     * Слушатели событий для сущности.
     *
     * @return array Массив вида [событие => слушатель (объект)].
     */
    public function getListenerEvents() : array;
}
