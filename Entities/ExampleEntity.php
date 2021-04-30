<?php

namespace Prokl\WordpressCustomTableEditorBundle\Entities;

use Prokl\WordpressCustomTableEditorBundle\Services\Bridge\PostView;
use Prokl\WordpressCustomTableEditorBundle\Services\Events\Listeners\EditFormAfterLoadListener;
use Prokl\WordpressCustomTableEditorBundle\Services\Manager\DataManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ExampleEntity
 * @package Prokl\WordpressCustomTableEditorBundle\Entities
 *
 * @since 28.03.2021
 * @since 08.04.2021 Поле генератора фикстур.
 */
class ExampleEntity extends DataManager
{
    /**
     * @inheritDoc
     */
    public function getTableName() : string
    {
        return 'wp_example_custom_table';
    }

    /**
     * @inheritDoc
     */
    public function getEntityName(): string
    {
        return 'Example';
    }

    /**
     * @inheritDoc
     */
    public function getMap(): array
    {
        return [
            [
                'name' => 'new_column',
                'description' => 'Новый столбец',
                'type' => 'varchar',
                'length' => 50,
                'nulled' => false,
                'sortable' => true,
                'default' => '',
                'required' => false,
                'placeholder' => true
            ],
            [
                'name' => 'modified_time',
                'description' => 'Время модификации',
                'type' => 'timestamp',
                'sortable' => true,
                'default' => null,
                'view_type' => 'date',
                'required' => false,
            ],
            [
                'name' => 'modified_date',
                'description' => 'Дата модификации',
                'type' => 'datetime',
                'sortable' => true,
                'default' => null,
                'view_type' => 'date',
                'required' => false,
            ],
            [
                'name' => 'description',
                'description' => 'Описание',
                'type' => 'longtext',
                'length' => 150,
                'nulled' => false,
                'sortable' => true,
                'default' => '',
                'view_type' => 'textarea',
                'required' => false,
                'validators' => [
                    new Assert\Length([
                        'min' => 2,
                        'max' => 150,
                        'minMessage' => 'Description must be at least {{ limit }} characters long',
                        'maxMessage' => 'Description cannot be longer than {{ limit }} characters',
                    ])
                ],
                'fixture_generator' => '@example_entity.length_fixture_generator'
            ],
            [
                'name' => 'qty',
                'description' => 'Картинка',
                'type' => 'int',
                'view_type' => 'text',
                'sortable' => true,
                'default' => 0,
                'required' => false,
                //'transformer' => WpImage::class,
                //'view_generator' => WpImageView::class
                // 'transformer' => Post::class,
                'view_generator' => PostView::class,
                // 'fixture_generator' => RealImage::class
            ],
            [
                'name' => 'tiny_integer',
                'description' => 'Микроскопия',
                'type' => 'tinyInt',
                'sortable' => true,
                'default' => 0,
                'required' => false,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getListenerEvents() : array
    {
        return [
            'admin.custom.table.edit_screen_after_load' => new EditFormAfterLoadListener
        ];
    }
}
