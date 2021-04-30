<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Manager;

use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validation;
use wpdb;

/**
 * Class AddAdminEntity
 * @package Prokl\WordpressCustomTableEditorBundle\Services
 *
 * @since 28.03.2021
 */
class AddAdminEntity
{
    /**
     * @var DataManagerInterface $entity Сущность.
     */
    private $entity;

    /**
     * @var wpdb $wpdb WPDB.
     */
    private $wpdb;

    /**
     * @var string $adminGeneratorClass Класс генератора админки.
     */
    private $adminGeneratorClass;

    /**
     * @var EventDispatcherInterface $eventDispatcher Event dispatcher.
     */
    private $eventDispatcher;

    /**
     * AddAdminEntity constructor.
     *
     * @param wpdb                     $wpdb            WPDB.
     * @param DataManagerInterface     $entity          Сущность.
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher.
     *
     * @throws RuntimeException Таблица не существует.
     */
    public function __construct(
        wpdb $wpdb,
        DataManagerInterface $entity,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entity = $entity;
        $this->wpdb = $wpdb;
        $this->eventDispatcher = $eventDispatcher;

        $this->adminGeneratorClass = $this->entity->getAdminGeneratorClass();

        if (!$this->existTable($this->entity->getTableName())) {
            throw new RuntimeException(
               sprintf('Таблица %s не существует', $this->entity->getTableName())
            );
        }

        add_action('admin_menu', [$this, 'menu']);
    }

    /**
     * @return void
     */
    public function menu(): void
    {
        add_menu_page(
            $this->entity->getEntityName(),
            $this->entity->getEntityName(),
            'activate_plugins',
            $this->entity->getTableName(),
            [$this, 'pageHandler']
        );

        add_submenu_page(
            $this->entity->getTableName(),
            $this->entity->getEntityName(),
            $this->entity->getEntityName(),
            'activate_plugins',
            $this->entity->getTableName(),
            [$this, 'pageHandler']
        );

        add_submenu_page(
            $this->entity->getTableName(),
            'Maintain ' . $this->entity->getTableName(),
            'Add new',
            'activate_plugins',
            $this->entity->getTableName(). '_form',
            [$this, 'formPageHandler']
        );
    }

    /**
     * @return void
     */
    public function pageHandler(): void
    {
        // AdminEntityInterface не может быть сервисом из-за очередности загрузки
        // и прочих wordpress заморочек.
        $table = new $this->adminGeneratorClass(
            $this->entity,
            $this->wpdb,
            $this->eventDispatcher
        );

        $table->prepare_items();

        $message = '';
        if ($table->current_action() === 'delete') {
            $message = '<div class="updated below-h2" id="message"><p>'.sprintf('Items deleted: %d',
                    count($_REQUEST['id'])).'</p></div>';
        }
        ?>
      <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php echo $this->entity->getEntityName() ?>
              <a class="add-new-h2"
                       href="<?php echo get_admin_url(
                           get_current_blog_id(), 'admin.php?page=' .$this->entity->getTableName().'_form'); ?>">
                Add new
              </a>
        </h2>
          <?php echo $message; ?>

        <form id="persons-table" method="GET">
          <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <?php $table->display() ?>
        </form>

      </div>
        <?php
    }

    /**
     * Обработчик формы.
     *
     * @return void
     */
    public function formPageHandler(): void
    {
        $table_name = $this->entity->getTableName();
        $message = $notice = '';

        // this is default $item which will be used for new records
        $default = $this->entity->getColumnsData('default');

        // here we are verifying does this request is post back and have correct nonce
        if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
            // combine our default item with request params
            $item = shortcode_atts($default, $_REQUEST);
            $item['id'] = $_REQUEST['id'];
            // validate data, and if all ok save item to database
            // if id is zero insert otherwise update
            $item_valid = $this->validate($item);
            if (count($item_valid) === 0) {
                // Загрузка файлов.
                if (isset($_FILES)) {
                    foreach ($item as $fieldName => $value) {
                        $fileId = $this->fileSave($fieldName);
                        if ($fileId !== 0) {
                            $item[$fieldName] = $fileId;
                        }
                    }
                }

                $eventBeforeSave = new BeforeSaveDatabaseEvent($item);
                $this->eventDispatcher->dispatch(
                    $eventBeforeSave,
                    'admin.custom.table.before.save'
                );
                $item = $eventBeforeSave->getFields();

                $item = $this->beforeSaveDB($item);

                $eventAfterSave = new AfterSaveDatabaseEvent($item);
                $this->eventDispatcher->dispatch(
                    $eventAfterSave,
                    'admin.custom.table.after.save'
                );

                $item = $eventAfterSave->getFields();

                if ((int)$item['id'] === 0) {
                    $result = $this->wpdb->insert($table_name, $item);
                    $item['id'] = $this->wpdb->insert_id;
                    if ($result !== false) {
                        $message = 'Item was successfully saved';
                    } else {
                        $notice = 'There was an error while saving item';
                    }
                } else {
                    $result = $this->wpdb->update($table_name, $item, ['id' => $item['id']]);
                    if ($result !== false) {
                        $message = 'Item was successfully updated';
                    } else {
                        $notice = 'There was an error while updating item';
                    }
                }
            } else {
                // if $item_valid not true it contains error message(s)
                $notice = implode(',', $item_valid);
            }
        } else {
            // if this is not post back we load item to edit or give new one to create
            $item = $default;
            if (isset($_REQUEST['id'])) {
                $item = $this->wpdb->get_row(
                    $this->wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A
                );

                if (!$item) {
                    $item = $default;
                    $notice = 'Item not found';
                }
            }
        }

        // here we adding our custom meta box
        add_meta_box(
            $this->entity->getTableName() . '_form_meta_box',
            $this->entity->getEntityName(),
            [$this, 'formMetaBoxHandler'],
            $this->entity->getTableName(),
            'normal',
            'default'
        );

        ?>
      <div class="wrap">
        <?php if (isset($_GET['edit'])) :?>
            <h2>Editing</h2>
        <?php else : ?>
            <h2>Add new</h2>
        <?php endif?>

        <h2><?php echo $this->entity->getEntityName() ?> <a class="add-new-h2"
                                      href="<?php echo get_admin_url(get_current_blog_id(),
                                          'admin.php?page=' . $this->entity->getTableName()); ?>">back to list</a>
        </h2>

          <?php if (!empty($notice)) : ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
          <?php endif; ?>
          <?php if (!empty($message)) : ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
          <?php endif; ?>

        <form id="form" method="POST" enctype= "multipart/form-data">
          <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
          <input type="hidden" name="id" value="<?php echo $item['id'] ?? $item['ID'] ?>"/>

          <div class="metabox-holder" id="poststuff">
            <div id="post-body">
              <div id="post-body-content">
                  <?php do_meta_boxes($this->entity->getTableName(), 'normal', $item); ?>
                <input type="submit" value="Save" id="submit" class="button-primary" name="submit">
              </div>
            </div>
          </div>
        </form>
      </div>
        <?php
    }

    /**
     * This function renders our custom meta box.
     *
     * @param array $item Row.
     *
     * @return void
     */
    public function formMetaBoxHandler(array $item): void
    {
        if (isset($_GET['edit'])) {
            $item = $this->afterLoadDB($item);

            $event = new AfterLoadDatabaseEvent($item);
            $this->eventDispatcher->dispatch(
                $event,
                'admin.custom.table.after_load_db'
            );
            $item = $event->getFields();
        }?>
      <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>

        <?php foreach ($item as $nameField => $value) :
            $dataField = $this->getFieldData($nameField);
            if (count($dataField) === 0) {
                continue;
            }?>

          <tr class="form-field">
            <th valign="top" scope="row">
              <label for="<?php echo $dataField['name']?>">
                  <?php echo $dataField['description']?>
              </label>
            </th>
            <td>

             <?php if ($dataField['view_type'] === 'textarea') :?>
                 <textarea id="<?php echo $dataField['name']?>"
                        name="<?php echo $dataField['name']?>"
                        style="width: 95%;
                        <?php if ($this->getFieldTransformer($dataField['name'])) :?>
                            border-width: 2px;
                        <?php endif;?>
                        "
                        value="<?php echo isset($_GET['edit']) ? esc_attr((string)$value) : $dataField['default'] ?>"
                        size="50"
                        class="code"
                        <?php if ($dataField['required'] === true) :?>
                            required
                        <?php endif?>
                 >
                 </textarea>
             <?php else :?>
               <input id="<?php echo $dataField['name']?>"
                      name="<?php echo $dataField['name']?>"
                      type="<?=$dataField['view_type'] ?? 'text'?>"
                      style="width: 95%;
                      <?php if ($this->getFieldTransformer($dataField['name'])) :?>
                            border-width: 2px;
                      <?php endif;?>
                      "
                      value="<?php echo isset($_GET['edit']) ? esc_attr((string)$value) : $dataField['default'] ?>"
                      size="50"
                      class="code"
                      <?php if (array_key_exists('placeholder', $dataField) && $dataField['placeholder'] === true) : ?>
                          placeholder="<?php echo $dataField['description']?>"
                      <?php endif;?>
                      <?php if ($dataField['required'] === true) :?>
                            required
                      <?php endif?>
                />
             <?php endif;?>

             <?php if (!empty($item['rendered_view'][$nameField])) :?>
                    <?php echo $item['rendered_view'][$nameField]; ?>
             <?php endif;?>

            </td>
          </tr>
        <?php endforeach;?>

        </tbody>
      </table>
        <?php
    }

    /**
     * Валидация.
     *
     * @param array $item
     *
     * @return array Массив с ошибками. Пусто, если все OK.
     */
    public function validate(array $item) : array
    {
        $validator = Validation::createValidator();
        $result = [];

        foreach ($item as $field => $value) {
            $validators = $this->getFieldValidator($field);
            if (count($validators) === 0) {
                continue;
            }

            $violations = $validator->validate($value, $validators);
            if (count($violations) >0) {
                foreach ($violations as $violation) {
                    $result[$violation->getPropertyPath()][] = $violation->getMessage();
                }
            }
        }

        return $result;
    }

    /**
     * Операции перед сохранением полей в базу.
     *
     * @param array $data $_POST массив.
     *
     * @return array
     */
    private function beforeSaveDB(array $data) : array
    {
        foreach ($data as $name => $value) {
            $transformer = $this->getFieldTransformer($name);
            if (class_exists($transformer)) {
                /** @var FieldProcessorInterface $object Трансформер. */
                $object = new $transformer;
                $data[$name] = $object->beforeSaveDb($value);
            }
        }

        return $data;
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
                $data['rendered_view'][$name] = $generator->viewEditor($data[$name], ['id' => $name]);
            }
        }

        return $data;
    }

    /**
     * Данные на поле по коду (из мэппера).
     *
     * @param string $field Код поля.
     *
     * @return array
     */
    private function getFieldData(string $field): array
    {
        foreach ($this->entity->getMap() as $item) {
            if ($item['name'] === $field) {
                return $item;
            }
        }

        return [];
    }

    /**
     * Валидаторы на поле по коду (из мэппера).
     *
     * @param string $field Код поля.
     *
     * @return array
     */
    private function getFieldValidator(string $field): array
    {
        foreach ($this->entity->getMap() as $item) {
            if ($item['name'] === $field) {
                if (empty($item['validators'])) {
                    return [];
                }

                return $item['validators'];
            }
        }

        return [];
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

    /**
     * Существует ли таблица?
     *
     * @param string $table Название таблицы.
     *
     * @return boolean
     *
     * @since 29.03.2021
     */
    private function existTable(string $table) : bool
    {
        return $this->wpdb->get_var("SHOW TABLES LIKE '".$table."'") === $table;
    }

    /**
     * Загрузка файла.
     *
     * @param string $field Поле.
     *
     * @return integer ID файла или 0 в случае неудачи.
     */
    private function fileSave(string $field) : int
    {
        $key = 'file_' . $field;
        if (!isset($_FILES[$key])) {
            return 0 ;
        }

        $wordpress_upload_dir = wp_upload_dir();
        $image = $_FILES[$key];
        $new_file_path = $wordpress_upload_dir['path'] . '/' . $image['name'];
        $new_file_mime = mime_content_type( $image['tmp_name'] );

        $i = 0;
        if ($image['error']) {
            return 0;
        }

        while (file_exists($new_file_path)) {
            $new_file_path = $wordpress_upload_dir['path'] . '/' . $i . '_' . $image['name'];
        }

        // looks like everything is OK
        if (move_uploaded_file( $image['tmp_name'], $new_file_path ) ) {
            $upload_id = wp_insert_attachment( [
                'guid'           => $new_file_path,
                'post_mime_type' => $new_file_mime,
                'post_title'     => preg_replace( '/\.[^.]+$/', '', $image['name'] ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            ], $new_file_path );

            // wp_generate_attachment_metadata() won't work if you do not include this file
            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            // Generate and save the attachment metas into the database
            wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );

            return $upload_id;
        }

        return 0;
    }
}
