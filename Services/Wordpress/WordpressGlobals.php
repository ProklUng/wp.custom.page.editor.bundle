<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Wordpress;

use wpdb;

/**
 * Class WordpressGlobals
 * Глобальные переменные Wordpress.
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Wordpress
 */
class WordpressGlobals
{
    /**
     * wpdb.
     *
     * @return wpdb
     */
    public function wpdb() : wpdb
    {
        if (array_key_exists('wpdb', $GLOBALS)
            &&
            $GLOBALS['wpdb'] !== null
        ) {
            return $GLOBALS['wpdb'];
        }

        $wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $GLOBALS['wpdb'] = $wpdb;

        return $wpdb;
    }
}
