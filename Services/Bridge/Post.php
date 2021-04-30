<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Bridge;

use Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts\FieldProcessorInterface;

/**
 * Class Post
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Bridge
 *
 * @since 29.03.2021
 */
class Post implements FieldProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function beforeSaveDb($data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function afterLoadFromDb($data)
    {
        return $data;
    }
}
