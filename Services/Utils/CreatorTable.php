<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Utils;

use Exception;
use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;
use RuntimeException;
use wpdb;

/**
 * Class CreatorTable
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Utils
 *
 * @since 18.03.2021
 * @since 29.03.2021 Создание таблицы из схемы сущности.
 * @since 30.03.2021 Тип поля tinyText, mediumText, bigInt, boolean.
 */
class CreatorTable
{
    /**
     * @var string $table Название таблицы.
     */
    private $table;

    /**
     * @var array $columns Столбцы.
     */
    private $columns = [];

    /**
     * @var string $_sql SQL query.
     */
    private $_sql = '';

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
     * @param DataManagerInterface $schema Схема сущности.
     *
     * @return boolean
     * @throws Exception
     *
     * @since 29.03.2021
     */
    public function createFromSchema(DataManagerInterface $schema): bool
    {
        $this->table = $schema->getTableName();
        foreach ($schema->getMap() as $fieldData) {
            if (!array_key_exists('name', $fieldData)) {
                throw new RuntimeException(
                    'Отсутствует необходимое поле name.'
                );
            }

            $typeField = 'varchar';
            if (array_key_exists('type', $fieldData)) {
                $typeField = $fieldData['type'];
            }

            if (!method_exists($this, $typeField)) {
                throw new RuntimeException(
                  'Обработчик поля типа ' . $typeField . ' не существует.'
                );
            }

            if ($typeField === 'varchar') {
                $this->varchar(
                    $fieldData['name'],
                    $fieldData['length'] ?? 10,
                    $fieldData['nulled'] ? '' :  'nulled',
                );
                continue;
            }

            if ($typeField === 'timestamp') {
                $this->timestamp(
                    $fieldData['name']
                );
                continue;
            }

            if ($typeField === 'datetime') {
                $this->datetime(
                    $fieldData['name']
                );
                continue;
            }

            if ($typeField === 'longtext') {
                $this->longtext(
                    $fieldData['name'],
                    $fieldData['nulled'] ? '' :  'nulled',
                );
                continue;
            }

            if ($typeField === 'tinytext') {
                $this->tinytext(
                    $fieldData['name'],
                    $fieldData['nulled'] ? '' :  'nulled',
                );
                continue;
            }

            if ($typeField === 'mediumtext') {
                $this->mediumtext(
                    $fieldData['name'],
                    $fieldData['nulled'] ? '' :  'nulled',
                );
                continue;
            }

            if ($typeField === 'boolean') {
                $this->boolean(
                    $fieldData['name'],
                    $fieldData['default'] ?:  '0',
                );
                continue;
            }

            if ($typeField === 'int') {
                $this->int(
                    $fieldData['name']
                );
                continue;
            }

            if ($typeField === 'tinyInt') {
                $this->tinyInt(
                    $fieldData['name']
                );
                continue;
            }

            if ($typeField === 'bigInt') {
                $this->bigInt(
                    $fieldData['name']
                );
                continue;
            }
        }

        $this->createColumns();

        return $this->createTable();
    }

    /**
     * @param string  $column_name Название столбца.
     * @param integer $length      Длина.
     * @param string  $not_null    Nulled.
     * @param mixed   $default     По умолчанию.
     *
     * @return void
     */
    public function varchar(
        string $column_name = 'name',
        int $length = 10,
        string $not_null = '',
        $default = null
    ): void {
        $not_null = $not_null ? 'NOT NULL' : 'NULL';
        $default = $default !== null ? 'default \''.$default.'\'' : '';
        $sql = "$column_name varchar($length) $not_null $default";
        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     * @param string $not_null    Nulled.
     * @param string $default     По умолчанию.
     *
     * @return void
     */
    public function longtext(string $column_name = 'text', string $not_null = '', string $default = ''): void
    {
        $not_null = $not_null ? 'NOT NULL' : 'NULL';
        $sql = "$column_name longtext $not_null";
        if ($not_null) {
            $sql .= " default '$default'";
        }

        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     * @param string $not_null    Nulled.
     * @param string $default     По умолчанию.
     *
     * @return void
     */
    public function tinytext(string $column_name = 'text', string $not_null = '', string $default = ''): void
    {
        $not_null = $not_null ? 'NOT NULL' : 'NULL';
        $sql = "$column_name tinytext $not_null";
        if ($not_null) {
            $sql .= " default '$default'";
        }

        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     * @param string $not_null    Nulled.
     * @param string $default     По умолчанию.
     *
     * @return void
     */
    public function mediumtext(string $column_name = 'mediumtext', string $not_null = '', string $default = ''): void
    {
        $not_null = $not_null ? 'NOT NULL' : 'NULL';
        $sql = "$column_name medium $not_null";
        if ($not_null) {
            $sql .= " default '$default'";
        }

        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     *
     * @return void
     */
    public function int(string $column_name = 'integer'): void
    {
        $sql = "$column_name int(11) NOT NULL";
        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     *
     * @return void
     */
    public function tinyInt(string $column_name = 'tinyint'): void
    {
        $sql = "$column_name TINYINT(1) NOT NULL";
        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     *
     * @return void
     */
    public function bigInt(string $column_name = 'bigint'): void
    {
        $sql = "$column_name BIGINT(20) NOT NULL";
        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     * @param string $default     По умолчанию.
     *
     * @return void
     */
    public function boolean(string $column_name = 'boolean', $default = '0'): void
    {
        $sql = "$column_name BOOLEAN NULL default '$default'";
        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     * @param mixed  $default     По умолчанию.
     *
     * @return void
     */
    public function datetime(string $column_name = 'created', $default = '0000-00-00 00:00:00'): void
    {
        $default = $default ?? current_time('mysql');
        $sql = "$column_name datetime NOT NULL default '$default'";
        $this->columns[] = $sql;
    }

    /**
     * @param string $column_name Название столбца.
     * @param mixed  $default     По умолчанию.
     *
     * @return void
     */
    public function timestamp(string $column_name = 'timestamp', $default = '0000-00-00 00:00:00'): void
    {
        $default = $default ?? current_time('mysql');
        $sql = "$column_name timestamp NOT NULL default '$default'";
        $this->columns[] = $sql;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function createColumns(string $id = 'id'): void
    {
        $collate = '';

        if ($this->wpdb->has_cap('collation')) {
            $collate = $this->wpdb->get_charset_collate();
        }

        $columns = implode(',', $this->columns);

        if (count($this->columns) === 0) {
            $this->_sql = "CREATE TABLE `$this->table`
				(
					`$id` int(11) NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`$id`)
				) 
				$collate;";

        } else {
            $this->_sql = "CREATE TABLE `$this->table`
				(
					`$id` int(11) NOT NULL AUTO_INCREMENT,
					$columns,
					PRIMARY KEY (`$id`)
				) 
				$collate;";
        }
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
     * Существует ли столбец в таблице?
     *
     * @param string $table Таблица.
     * @param string $column Столбец.
     *
     * @return boolean
     *
     * @throws Exception
     * @since 30.03.2021
     */
    public function columnExists(string $table, string $column) : bool
    {
        $exists = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SHOW COLUMNS FROM `$table` LIKE '$column'",
            )
        );

        $this->checkError();

        if (empty($exists)) {
            return false;
        }

        return true;
    }

    /**
     * Создать таблицу.
     *
     * @return boolean
     * @throws Exception
     */
    public function createTable(): bool
    {
        if ($this->_sql === '') {
            return false;
        }

        if ($this->wpdb->get_var("SHOW TABLES LIKE '".$this->table."'") !== $this->table) {
            $this->wpdb->query($this->_sql);
            $this->checkError();

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getQuery() : string
    {
        return $this->_sql;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function addColumns(): void
    {
        if (count($this->columns) >= 1) {
            foreach ($this->columns as $key => $value) {
                $array_words = explode(' ', $value);
                $column_name = $array_words[0];
                $add_column = $this->wpdb->get_results(
                    "SELECT COLUMN_NAME 
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE table_name = '$this->table'
						AND column_name = '$column_name'"
                );

                if (empty($add_column)) {
                    $this->wpdb->query(
                        "ALTER TABLE $this->table ADD $value"
                    );
                    $this->checkError();
                }
            }
        }
    }

    /**
     * Check data base error.
     *
     * @since 30.03.2021
     *
     * @return void
     * @throws Exception If wpdb error.
     */
    private function checkError() : void
    {
        if (!$this->wpdb->last_error ) {
            return;
        }

        $error_msg = $this->wpdb->last_error;
        throw new Exception($error_msg);
    }
}
