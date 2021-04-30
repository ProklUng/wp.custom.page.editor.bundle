<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Bridge;

use Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts\ViewGeneratorInterface;

/**
 * Class PostView
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Bridge
 *
 * @since 30.03.2021
 */
class PostView implements ViewGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function viewList($param): string
    {
        if (!(int)$param) {
            return '';
        }

        $title = get_the_title($param);
        if (!$title) {
            return '';
        }

        return '<div><strong>Post ID</strong>: ' . $param . ' <strong>Title</strong>: ' . $title . '</div>';;
    }

    /**
     * @inheritDoc
     */
    public function viewEditor($param, $payload = null): string
    {
        if (!(int)$param) {
            return '';
        }

        $title = get_the_title($param);
        if (!$title) {
            return '';
        }

        return '<div><strong>Post ID</strong>: ' . $param . ' <strong>Title</strong>: ' . $title . '</div>';
    }
}
