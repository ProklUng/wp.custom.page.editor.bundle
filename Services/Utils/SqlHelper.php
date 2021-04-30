<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Utils;

use wpdb;

/**
 * Class SqlHelper
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Utils
 *
 * @since 28.03.2021
 */
class SqlHelper
{
    /**
     * @var wpdb $wpdb
     */
    private $wpdb;

    /**
     * SqlHelper constructor.
     *
     * @param wpdb $wpdb WPDB.
     */
    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * Get prefix this WP installation uses for tables.
     */
    public function getPrefix() : string
    {
        return $this->wpdb->prefix;
    }

    /**
     * Create new table in database.
     *
     * @param string $table name to create.
     * @param string $columns_sql parsed in Schema.
     * How WP wants this done:
     *
     * @link https://codex.wordpress.org/Creating_Tables_with_Plugins
     *
     * @return array
     */
    public function createTable(string $table, string $columns_sql): array
    {
        $charset_collate = $this->wpdb->get_charset_collate();

        // Parse SQL from columns.
        $sql = 'CREATE TABLE ' . $table . ' ( ' . $columns_sql . ' ) ' . $charset_collate . ';';

        /**
         * @psalm-suppress MissingFile
         */
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute using WordPress functions.
        return dbDelta($sql);
    }

    /**
     * Drop table from database.
     *
     * @param string $table name to create.
     *
     * @return boolean|int
     */
    public function dropTable(string $table)
    {
        // Parse SQL from columns.
        $sql = 'DROP TABLE  IF EXISTS ' . $table . ';';

        // Execute using WPDB.
        return $this->wpdb->query($sql);
    }

    /**
     * Insert record into DB table.
     *
     * @param string $table name.
     * @param array $attributes fields.
     *
     * @return integer
     */
    public function insert(string $table, array $attributes) : int
    {
        $this->wpdb->insert($table, $attributes);

        return $this->wpdb->insert_id;
    }

    /**
     * Update record in DB.
     *
     * @param string $table to use.
     * @param array $attributes to use.
     * @param string $primary_key to use.
     * @param integer $id to delete.
     *
     * @return void
     */
    public function update(string $table, array $attributes, string $primary_key, int $id): void
    {
        $this->wpdb->update(
            $table,
            $attributes,
            [$primary_key => $id],
        );
    }

    /**
     * Delete record from DB.
     *
     * @param string  $table       Table.
     * @param string  $primary_key Primary key.
     * @param integer $id          ID to delete.
     *
     * @return void
     */
    public function delete(string $table, string $primary_key, int $id): void
    {
        $this->wpdb->delete(
            $table,
            [$primary_key => $id],
        );
    }

    /**
     * Get results by column name & value.
     *
     * @param string $table       Nname.
     * @param string $column_name  Column name.
     * @param string $column_value Column to query.
     * @return array
     */
    public function getResults(string $table, string $column_name, string $column_value): array
    {
        return $this->wpdb->get_results("SELECT * FROM $table WHERE $column_name = $column_value", ARRAY_A);
    }

    /**
     * Get all results from table.
     *
     * @param string $table name.
     * @return array
     */
    public function getAllResults(string $table) : array
    {
        return $this->wpdb->get_results("SELECT * FROM $table", ARRAY_A);
    }
}
