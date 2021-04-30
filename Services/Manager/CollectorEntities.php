<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Manager;

use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use wpdb;

/**
 * Class CollectorEntities
 * @package Prokl\WordpressCustomTableEditorBundle\Services
 *
 * @since 29.03.2021
 */
class CollectorEntities
{
    /**
     * @var wpdb $wpdb WPDB.
     */
    private $wpdb;

    /**
     * @var array $entities Сущности, помеченные тэгом wp_custom_table_editable.
     */
    private $entities = [];

    /**
     * @var EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    private $eventDispatcher;

    /**
     * @var BaseEventsManager $baseEventsManager События по умолчанию.
     */
    private $baseEventsManager;

    /**
     * CollectorEntities constructor.
     *
     * @param wpdb                     $wpdb              WPDB.
     * @param mixed                    ...$entities       Сущности, помеченные тэгом wp_custom_table_editable.
     * @param EventDispatcherInterface $eventDispatcher   Event dispatcher.
     * @param BaseEventsManager        $baseEventsManager События по умолчанию.
     */
    public function __construct(
        wpdb $wpdb,
        EventDispatcherInterface $eventDispatcher,
        BaseEventsManager $baseEventsManager,
        ...$entities
    ) {
        $this->wpdb = $wpdb;

        foreach ($entities as $entity) {
            $iterator = $entity->getIterator();
            $this->entities[] = iterator_to_array($iterator);
        }

        $this->entities = array_merge([], ...$this->entities);
        $this->eventDispatcher = $eventDispatcher;
        $this->baseEventsManager = $baseEventsManager;
    }

    /**
     * Инициализация.
     *
     * @return void
     */
    public function init() : void
    {
        $this->baseEventsManager->init();

        foreach ($this->entities as $entity) {
            $initializer = new AddAdminEntity(
                $this->wpdb,
                $entity,
                $this->eventDispatcher
            );

            $this->initEventsListeners($entity);
        }
    }

    /**
     * Инициализировать слушателей событий из класса сущности.
     *
     * @param DataManagerInterface $entity Сущность.
     * @param string               $method Метод.
     *
     * @return void
     *
     * @since 31.03.2021
     */
    private function initEventsListeners(DataManagerInterface $entity, string $method = 'action') : void
    {
        $listeners = $entity->getListenerEvents();

        if (count($listeners) === 0) {
            return;
        }

        foreach ($listeners as $eventName => $listener) {
            if (!method_exists($listener, $method)) {
                throw new RuntimeException(
                    sprintf(
                        'Method %s in object of class %s not exists',
                        $method,
                        get_class($listener)
                    )
                );
            }

            $this->eventDispatcher->addListener(
                $eventName,
                [$listener, $method]
            );
        }
    }
}
