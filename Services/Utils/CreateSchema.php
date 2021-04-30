<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Utils;

use wpdb;

/**
 * Class CreateSchema
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Utils
 *
 * @since 05.04.2021
 */
class CreateSchema
{
    /**
     * @var string $table Название таблицы.
     */
    private $table;

    /**
     * @var wpdb $wpdb WPDB.
     */
    private $wpdb;

    /**
     * CreatorTable constructor.
     *
     * @param wpdb   $wpdb       WPDB.
     * @param string $table_name Название таблицы.
     */
    public function __construct(wpdb $wpdb, string $table_name = '')
    {
        $this->wpdb = $wpdb;
        $this->table = $table_name;
    }

    /**
     * @param string $table
     *
     * @return void
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * Существует ли таблица?
     *
     * @return boolean
     *
     * @since 28.03.2021
     */
    public function existTable() : bool
    {
        return $this->wpdb->get_var("SHOW TABLES LIKE '".$this->table."'") === $this->table;
    }

    /**
     * @return array
     */
    public function getTableDescription(): array
    {
        $arResult = [];

        $sql = 'SELECT * ';
        $sql .= 'FROM INFORMATION_SCHEMA.COLUMNS ';
        $sql .= 'WHERE TABLE_SCHEMA = "'.$this->wpdb->dbname.'" ';
        $sql .= 'AND TABLE_NAME = "'.$this->table.'";';

        $result = $this->wpdb->get_results($sql);

        if (!$result) {
            throw new \RuntimeException(
              'Database error: ' . $this->wpdb->last_error
            );
        }

        foreach ($result as $columnData) {
            $arResult[] = [
              'name' => $columnData->COLUMN_NAME,
              'type' => $columnData->DATA_TYPE,
              'column_type' => $columnData->COLUMN_TYPE,
              'nulled' => $columnData->IS_NULLABLE !== 'NO',
              'default' => $columnData->COLUMN_DEFAULT,
              'autoincrement' => $columnData->EXTRA === 'auto_increment',
            ];
        }

        return $arResult;
    }
}
