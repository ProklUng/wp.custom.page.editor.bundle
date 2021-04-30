<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Utils;

use LogicException;
use RuntimeException;
use wpdb;

/**
 * Class SeedDatabase
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Utils
 *
 * @since 08.04.2021
 */
class SeedDatabase
{
    /**
     * @var wpdb $wpdb WPDB.
     */
    private $wpdb;

    /**
     * @var string $table Название таблицы.
     */
    private $table = '';

    /**
     * @var string $prefix Префикс таблиц.
     */
    private $prefix = 'wp_';

    /**
     * SeedDatabase constructor.
     *
     * @param wpdb $wpdb WPDB.
     */
    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * Вставить из фикстуры.
     *
     * @param array $fixture Фикстура.
     *
     * @return void
     * @throws LogicException | RuntimeException
     */
    public function fromFixture(array $fixture) : void
    {
        if (count($fixture) === 0) {
            return;
        }

        foreach ($fixture as $item) {
            $this->insert($item);
        }
    }

    /**
     * Вставить запись.
     *
     * @param array $data Данные.
     *
     * @return void
     * @throws LogicException | RuntimeException
     */
    public function insert(array $data) : void
    {
        if ($this->table === '') {
            throw new LogicException(
              'Таблица не задана.'
            );
        }

        $result = $this->wpdb->insert($this->table, $data);
        if ($result === false) {
            throw new RuntimeException(
              'Вставка данных не задалась: ' . $this->wpdb->last_error
            );
        }
    }

    /**
     * Очистить таблицу.
     *
     * @return void
     */
    public function truncate() : void
    {
        if ($this->table === '') {
            throw new LogicException(
                'Таблица не задана.'
            );
        }

        $result = $this->wpdb->query("TRUNCATE TABLE `$this->table`");
        if ($result === false) {
            throw new RuntimeException(
                'Очистка таблицы не задалась.'
            );
        }
    }

    /**
     * @param string $table Название таблицы.
     *
     * @return SeedDatabase
     */
    public function setTable(string $table): SeedDatabase
    {
        $this->table = $this->prefix ? $this->prefix . $table : $table;

        return $this;
    }

    /**
     * @param string $prefix Префикс таблиц.
     *
     * @return SeedDatabase
     */
    public function setPrefix(string $prefix): SeedDatabase
    {
        $this->prefix = $prefix;

        return $this;
    }

}
