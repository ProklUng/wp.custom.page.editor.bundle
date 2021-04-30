<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Manager;

use Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts\FieldProcessorInterface;
use Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts\ViewGeneratorInterface;
use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\AdminEntityInterface;
use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;
use Prokl\WordpressCustomTableEditorBundle\Services\Events\EditFormAfterLoadDatabaseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WP_List_Table;
use wpdb;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class BaseAdminEntityManager
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Manager
 *
 * @since 28.03.2021
 */
class BaseAdminEntityManager extends WP_List_Table implements AdminEntityInterface
{
    /**
     * @var DataManagerInterface $entity Сущность.
     */
    private $entity;

    /**
     * @var EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    private $eventDispatcher;

    /**
     * @var wpdb $wpdb WPDB.
     */
    private $wpdb;

    /**
     * @var string $table_name Название таблицы.
     */
    private $table_name;

    /**
     * @var integer $rowAtScreen Количество строк на экране.
     */
    private $rowAtScreen = 25;

    /**
     * @param DataManagerInterface     $entity          Сущность.
     * @param wpdb                     $wpdb            WPDB.
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    public function __construct(
        DataManagerInterface $entity,
        wpdb $wpdb,
        EventDispatcherInterface $eventDispatcher
    ) {
        global $status, $page;

        $this->entity = $entity;
        $this->wpdb = $wpdb;
        $this->eventDispatcher = $eventDispatcher;

        $this->table_name = $this->entity->getTableName();

        parent::__construct([
            'singular' => $this->entity->getEntityName(),
            'plural' => $this->entity->getEntityName(),
            false,
            $this->entity->getTableName()
        ]);
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param array  $item        Row (key, value array).
     * @param string $column_name String (key).
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        $transformedFields = $this->afterLoadDB($item);

        $event = new EditFormAfterLoadDatabaseEvent($this->entity, $transformedFields, $column_name);
        $this->eventDispatcher->dispatch(
            $event,
            'admin.custom.table.edit_screen_after_load'
        );

        $transformedFields = $event->getFields();

        return $transformedFields[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed.
     *
     * @param array $item Row (key, value array).
     *
     * @return string
     */
    public function column_name(array $item) : string
    {
        // Получение названия первого столбца.
        $maps = current($this->entity->getMap());
        $keyColumn = '';
        if ($maps) {
            $keyColumn = array_key_first($maps);
        }

        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = [
            'edit' => sprintf(
                '<a href="?page=%s_form&id=%s">%s</a>',
                $this->entity->getTableName(),
                $item['id'] ?? $item['ID'],
                'Edit'
            ),
            'delete' => sprintf(
                '<a href="?page=%s&action=delete&id=%s">%s</a>',
                $_REQUEST['page'],
                $item['id'] ?? $item['ID'],
                'Delete'
            ),
        ];

        return sprintf('%s %s',
            $maps ? $item[$keyColumn] : '',
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders.
     *
     * @param array $item Row (key, value array).
     *
     * @return string
     */
    public function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id'] ?? $item['ID']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description.
     *
     * @return array
     */
    public function get_columns() : array
    {
        $descriptions = $this->entity->getColumnsData('description');

        return array_merge(
            ['cb' => '<input type="checkbox" />'], //Render a checkbox instead of text
            $descriptions
        );
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort.
     *
     * @return array
     */
    public function get_sortable_columns() : array
    {
        return $this->entity->getColumnsData('sortable');
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any.
     *
     * @return array
     */
    public function get_bulk_actions() : array
    {
        return [
            'delete' => 'Delete',
        ];
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part.
     */
    public function process_bulk_action() : void
    {
        if ($this->current_action() === 'delete') {
            $ids = $_REQUEST['id'] ?? [];
            if (is_array($ids)) {
                $ids = implode(',', $ids);
            }

            if (!empty($ids)) {
                $this->wpdb->query("DELETE FROM $this->table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table.
     *
     * @return void
     */
    public function prepare_items() : void
    {
        $per_page = $this->rowAtScreen;

        $columns = $this->get_columns();

        $hidden = [];
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = [$columns, $hidden, $sortable];

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = (int)$this->wpdb->get_var("SELECT COUNT(id) FROM $this->table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, ($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && array_key_exists($_REQUEST['orderby'],
                $this->get_sortable_columns())) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'],
                ['asc', 'desc'])) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        /** @psalm-suppress PossiblyInvalidPropertyAssignmentValue */
        $this->items = $this->wpdb->get_results(
            $this->wpdb->prepare("SELECT * FROM $this->table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $per_page, $paged),
            ARRAY_A
        );

        // [REQUIRED] configure pagination
        $this->set_pagination_args([
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ]);
    }

    /**
     * Операции после загрузки из базы.
     *
     * @param array $data
     *
     * @return array
     */
    private function afterLoadDB(array $data) : array
    {
        foreach ($data as $name => $value) {
            $transformer = $this->getFieldTransformer($name);
            if (class_exists($transformer)) {
                /** @var FieldProcessorInterface $object Трансформер. */
                $object = new $transformer;
                $data[$name] = $object->afterLoadFromDb($value);
            }
        }

        // Опциональное view.
        foreach ($data as $name => $value) {
            $viewGenerator = $this->getFieldView($name);
            if ($viewGenerator && class_exists($viewGenerator)) {
                /** @var ViewGeneratorInterface $generator */
                $generator = new $viewGenerator;
                $data[$name] = $generator->viewList($data[$name]);
            }
        }

        return $data;
    }

    /**
     * Трансформер на поле по коду (из мэппера).
     *
     * @param string $field Код поля.
     *
     * @return string
     */
    private function getFieldTransformer(string $field) : string
    {
        foreach ($this->entity->getMap() as $item) {
            if ($item['name'] === $field) {
                if (empty($item['transformer'])) {
                    return '';
                }

                return $item['transformer'];
            }
        }

        return '';
    }

    /**
     * Трансформер на поле по коду (из мэппера).
     *
     * @param string $field Код поля.
     *
     * @return string
     */
    private function getFieldView(string $field) : string
    {
        foreach ($this->entity->getMap() as $item) {
            if ($item['name'] === $field) {
                if (empty($item['view_generator'])) {
                    return '';
                }

                return $item['view_generator'];
            }
        }

        return '';
    }
}
