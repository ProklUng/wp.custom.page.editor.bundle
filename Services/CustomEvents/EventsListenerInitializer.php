<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\CustomEvents;

use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class EventsListenerInitializer
 * Инициализатор кастомных слушателей событий.
 * @package Prokl\WordpressCustomTableEditorBundle\Services
 *
 * @since 31.03.2021
 */
class EventsListenerInitializer
{
    /**
     * @var array $listeners Слушатели.
     */
    private $listeners;

    /**
     * @var EventDispatcherInterface $dispatcher Event dispatcher.
     */
    private $dispatcher;

    /**
     * @var string $eventName Название события.
     */
    private $eventName;

    /**
     * EventsListenerInitializer constructor.
     *
     * @param EventDispatcherInterface $dispatcher   Event dispatcher.
     * @param string                   $eventName    Название события.
     * @param mixed                    ...$listeners Слушатели.
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        string $eventName,
        ...$listeners
    ) {
        $result = [];
        foreach ($listeners as $directive) {
            $iterator = $directive->getIterator();
            $result[] = iterator_to_array($iterator);
        }

        $this->listeners = array_merge([], ...$result);
        $this->dispatcher = $dispatcher;
        $this->eventName = $eventName;
    }

    /**
     * Инициализация слушателей событий.
     *
     * @return void
     */
    public function init() : void
    {
        foreach ($this->listeners as $listener) {
            $this->connectListener(
                $this->eventName,
                $listener,
                'action'
            );
        }
    }

    /**
     * Добавить слушателя событий.
     *
     * @param string $eventName Название события.
     * @param object $listener  Слушатель.
     * @param string $method    Метод.
     *
     * @return void
     * @throws RuntimeException Метод слушателя не существует.
     */
    private function connectListener(string $eventName, object $listener, string $method = 'action') : void
    {
        if (!method_exists($listener, $method)) {
            throw new RuntimeException(
                sprintf(
                    'Method %s in object of class %s not exists',
                    $method,
                    get_class($listener)
                )
            );
        }

        $this->dispatcher->addListener(
            $eventName,
            [$listener, $method]
        );
    }
}
