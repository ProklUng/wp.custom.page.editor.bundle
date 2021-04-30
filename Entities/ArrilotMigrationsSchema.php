<?php

namespace Prokl\WordpressCustomTableEditorBundle\Entities;

use Prokl\WordpressCustomTableEditorBundle\Services\Manager\DataManager;

/**
 * Class ArrilotMigrationsSchema
 * @package Prokl\WordpressCustomTableEditorBundle\Entities
 *
 * @since 29.03.2021
 */
class ArrilotMigrationsSchema extends DataManager
{
    /**
     * @inheritDoc
     */
    public function getTableName() : string
    {
        return 'wp_arrilot_migrations';
    }

    /**
     * @inheritDoc
     */
    public function getEntityName(): string
    {
        return 'Миграции Arrilot';
    }

    /**
     * @inheritDoc
     */
    public function getMap(): array
    {
        return [
            [
                'name' => 'MIGRATION',
                'description' => 'Миграция',
                'type' => 'varchar',
                'length' => 255,
                'nulled' => true,
                'sortable' => true,
                'default' => '',
                'required' => true,
                'placeholder' => true
            ],
        ];
     }
}
