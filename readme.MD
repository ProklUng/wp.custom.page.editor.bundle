# Бандл для редактирования кастомных таблиц в Wordpress

## Установка

`composer require `

## Использование

### Создание сущности

Сущность наследуется от `DataManager`.

#### Генераторы view

В описании полей (метод `getMap`) поле `view_generator` - кастомный генератор вывода этого поля в админке.

Текущие генераторы: `postView` (вывод поста), `WpImageView` (картинка из медиа-библиотеки).

Генератор реализует интерфейс `ViewGeneratorInterface` с методами:

 - `viewList($param)` - вид в списке 
 - `viewEditor($param, $payload = null)` - вид на странице редактирования записи
 
 К ним приложены классы `Post` и `WpImage`, реализующие интерфейс `FieldProcessorInterface` с методами:
 
 - `public function beforeSaveDb($data)` - перед сохранением в базу что-то сделать 
 - `public function afterLoadFromDb($data)` - что-то сделать после загрузки из базы перед передачей во view. 

#### События

`getListenerEvents` : список событий сущности. Что-то можно сделать с админкой перед выводом и так далее.

По умолчанию исполняется `EditFormAfterLoadDefaultListener`: первый столбец таблицы становится ссылкой на детальную
страницу редактирования записи. 

Список событий:

- `AfterLoadDatabaseEvent` - после загрузки записи из базы
- `AfterSaveDatabaseEvent` - после сохранения записи в базе
- `BeforeSaveDatabaseEvent` - перед сохранением записи в базе
- `EditFormAfterLoadDatabaseEvent` - перед генерацией столбцов редактора.

```php
class ExampleEntity extends DataManager
{
    /**
     * @inheritDoc
     */
    public function getTableName() : string
    {
        return 'wp_example_custom_table';
    }

    /**
     * @inheritDoc
     */
    public function getEntityName(): string
    {
        return 'Example';
    }

    /**
     * @inheritDoc
     */
    public function getMap(): array
    {
        return [
            [
                'name' => 'new_column',
                'description' => 'Новый столбец',
                'type' => 'varchar',
                'length' => 50,
                'nulled' => false,
                'sortable' => true,
                'default' => '',
                'required' => false,
                'placeholder' => true
            ],
            [
                'name' => 'modified_date',
                'description' => 'Дата модификации',
                'type' => 'datetime',
                'sortable' => true,
                'default' => null,
                'view_type' => 'date',
                'required' => false,
            ],
            [
                'name' => 'description',
                'description' => 'Описание',
                'type' => 'longtext',
                'nulled' => false,
                'sortable' => true,
                'default' => '',
                'view_type' => 'textarea',
                'required' => false,
                'validators' => [
                    new Assert\Length([
                        'min' => 2,
                        'max' => 150,
                        'minMessage' => 'Description must be at least {{ limit }} characters long',
                        'maxMessage' => 'Description cannot be longer than {{ limit }} characters',
                    ])
                ]
            ],
            [
                'name' => 'picture',
                'description' => 'Картинка',
                'type' => 'int',
                'view_type' => 'text',
                'sortable' => true,
                'default' => 0,
                'required' => false,
                'view_generator' => PostView::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getListenerEvents() : array
    {
        return [
            'admin.custom.table.edit_screen_after_load' => new EditFormAfterLoadListener
        ];
    }
}
```
#### Заведения сервиса

Сервис должен быть помечен тэгом `wp_custom_table_editable`.

```yaml
  example_entity:
    class: Local\Services\Database\Entities\ExampleEntity
    tags:
      - { name: wp_custom_table_editable }
```

Все. В админке появится редактор этой таблицы.