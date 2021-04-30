<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Manager;

use Prokl\WordpressCustomTableEditorBundle\Services\Manager\Events\EditFormAfterLoadDefaultListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class BaseEventsManager
 * Подписчики на события по умолчанию.
 * @package Prokl\WordpressCustomTableEditorBundle\Services
 *
 * @since 31.03.2021
 */
class BaseEventsManager
{
    /**
     * @var EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    private $eventDispatcher;

    /**
     * @var string[][] $defaultListeners Слушатели по умолчанию.
     */
    private $defaultListeners = [
        'admin.custom.table.edit_screen_after_load' => [
            EditFormAfterLoadDefaultListener::class
        ]
    ];

    /**
     * BaseEventsManager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Подвязка подписчиков событий.
     *
     * @param string $method Метод слушателя.
     *
     * @return void
     */
    public function init(string $method = 'action') : void
    {
        foreach ($this->defaultListeners as $eventName => $arListener) {
            foreach ($arListener as $listener) {
                $this->eventDispatcher->addListener(
                    $eventName,
                    [new $listener, $method],
                    10
                );
            }
        }
    }
}
