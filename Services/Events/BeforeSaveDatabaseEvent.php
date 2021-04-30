<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Events;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class BeforeSaveDatabaseEvent
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Events
 *
 * @since 30.03.2021
 */
class BeforeSaveDatabaseEvent extends Event
{
    /**
     * @var array
     */
    private $fields;

    /**
     * BeforeSaveDatabaseEvent constructor.
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
