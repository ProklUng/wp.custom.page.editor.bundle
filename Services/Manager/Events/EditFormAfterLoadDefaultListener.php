<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Manager\Events;

use Prokl\WordpressCustomTableEditorBundle\Services\Events\EditFormAfterLoadDatabaseEvent;

/**
 * Class EditFormAfterLoadDefaultListener
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Manager\Events
 *
 * @since 31.03.2021
 */
class EditFormAfterLoadDefaultListener
{
    /**
     * @param EditFormAfterLoadDatabaseEvent $event Событие.
     *
     * @return void
     */
    public function action(EditFormAfterLoadDatabaseEvent $event) : void
    {
        $column_name = $event->getColumnName();
        $entity = $event->getEntity();
        $fields = $event->getFields();

        // Первый столбец становится ссылкой.
        $firstField = current($entity->getMap());

        if ($firstField !== false && $column_name === $firstField['name']) {
            $fields[$column_name] =  sprintf(
                '<strong><a href="?page=%s_form&id=%s&edit=y">%s</a></strong>',
                $entity->getTableName(), $fields['id'] ?? $fields['ID'],
                $fields[$column_name]
            );

            $event->setFields($fields);
        }
    }
}
