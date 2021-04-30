<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Events;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AfterSaveDatabaseEvent
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Events
 *
 * @since 30.03.2021
 */
class AfterSaveDatabaseEvent extends Event
{
    /**
     * @var array $fields
     */
    private $fields;

    /**
     * AfterSaveDatabaseEvent constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
