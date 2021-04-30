<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Events;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AfterLoadDatabaseEvent
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Events
 *
 * @since 31.03.2021
 */
class AfterLoadDatabaseEvent extends Event
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
