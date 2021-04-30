<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Utils;

use Exception;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use Prokl\WordpressCustomTableEditorBundle\Services\Contracts\DataManagerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class FixtureGenerator
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Utils
 *
 * @since 08.04.2021
 */
class FixtureGenerator
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * @var ServiceLocator $locator Сервисы, помеченные тэгом fixture_generator.item.
     */
    private $locator;

    /**
     * FixtureGenerator constructor.
     *
     * @param ServiceLocator $locator Сервисы, помеченные тэгом fixture_generator.item.
     */
    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
        $this->faker = Factory::create();
    }

    /**
     * @param DataManagerInterface $schema
     * @param integer              $count
     *
     * @return array
     * @throws RuntimeException | Exception
     */
    public function fromSchema(DataManagerInterface $schema, int $count = 1) : array
    {
        if ($count < 0) {
            throw new RuntimeException(
              'Количество запрашиваемых фикстур не может быть меньше нуля.'
            );
        }

        $result = [];
        $arSchema = $schema->getMap();

        for ($i = 1; $i<= $count; $i++) {
            $result[] = $this->getFixtureItem($arSchema);
        }

        return $result;
    }

    /**
     * @param array $schema Схема.
     *
     * @return array
     * @throws RuntimeException | InvalidArgumentException | Exception
     */
    private function getFixtureItem(array $schema) : array
    {
        $result = [];
        foreach ($schema as $fieldData) {
            if (!array_key_exists('name', $fieldData)) {
                throw new RuntimeException(
                    'Отсутствует необходимое поле name.'
                );
            }

            $typeField = 'varchar';
            if (array_key_exists('type', $fieldData)) {
                $typeField = $fieldData['type'];
            }

            $nameField = $fieldData['name'];

            // Фикстуры из генератора, приложенного к сущности.
            if (array_key_exists('fixture_generator', $fieldData)) {
                // Алиас сервиса.
                if (strpos($fieldData['fixture_generator'], '@') === 0) {
                    $serviceId = str_replace('@', '', $fieldData['fixture_generator']);
                    if ($this->locator->has($serviceId)) {
                        /** @var FixtureGeneratorInterface $service */
                        $service = $this->locator->get($serviceId);
                        $result[$nameField] = $service->generate();
                        continue;
                    }

                    throw new InvalidArgumentException(
                      sprintf(
                          'Для поля %s в качестве генератора фикстур указан несуществующий сервис %s',
                          $nameField,
                          $serviceId
                      )
                    );
                }

                // Контейнер - по имени класса.
                if ($this->locator->has($fieldData['fixture_generator'])) {
                    /** @var FixtureGeneratorInterface $generator */
                    $generator = $this->locator->get($fieldData['fixture_generator']);
                    $result[$nameField] = $generator->generate();
                    continue;
                }

                /** @var FixtureGeneratorInterface $generator */
                $generator = new $fieldData['fixture_generator'];
                $result[$nameField] = $generator->generate();
                continue;
            }

            if ($typeField === 'varchar') {
                $length = array_key_exists('length', $fieldData) ? $fieldData['length'] : 50;
                $result[$nameField] = $this->faker->text($length);
                continue;
            }

            if ($typeField === 'timestamp') {
                $result[$nameField] = $this->faker->date('Y-m-d H:m:s');
                continue;
            }

            if ($typeField === 'datetime') {
                $result[$nameField] = $this->faker->date('Y-m-d H:m:s');
                continue;
            }

            if ($typeField === 'longtext') {
                $length = array_key_exists('length', $fieldData) ? $fieldData['length'] : 65000;
                $result[$nameField] = $length >=5 ? $this->faker->text($length)
                    : $this->generateRandomString($length);
                $result[$nameField] = $this->faker->text($length);
                continue;
            }

            if ($typeField === 'tinytext') {
                $length = array_key_exists('length', $fieldData) ? $fieldData['length'] : 255;
                $result[$nameField] = $length >=5 ? $this->faker->text($length)
                    : $this->generateRandomString($length);
                $result[$nameField] = $this->faker->text($length);
                continue;
            }

            if ($typeField === 'mediumtext') {
                $length = array_key_exists('length', $fieldData) ? $fieldData['length'] : 65000;
                $result[$nameField] = $length >=5 ? $this->faker->text($length)
                                                    : $this->generateRandomString($length);
                continue;
            }

            if ($typeField === 'text') {
                $length = array_key_exists('length', $fieldData) ? $fieldData['length'] : 65000;
                $result[$nameField] = $length >=5 ? $this->faker->text($length)
                    : $this->generateRandomString($length);
                $result[$nameField] = $this->faker->text($length);
                continue;
            }

            if ($typeField === 'boolean') {
                $result[$nameField] = (string)$this->faker->boolean();
                continue;
            }

            if ($typeField === 'int') {
                $result[$nameField] = $this->faker->numberBetween(1, 65000);
                continue;
            }

            if ($typeField === 'tinyInt') {
                $result[$nameField] = $this->faker->numberBetween(1, 127);
                continue;
            }

            if ($typeField === 'bigInt') {
                $result[$nameField] = $this->faker->numberBetween(1, 12147483647);
                continue;
            }
        }

        return $result;
    }

    /**
     * Случайная строка (Фэйкер отказывается генерить строки меньше 5 символов длиной).
     *
     * @param integer $length Длина нужной строки.
     * @param string  $src    Альтернативный набор символов.
     *
     * @return string
     * @throws Exception
     */
    private function generateRandomString(int $length = 25, string $src = '') : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($src !== '') {
            $characters = $src;
        }

        $charactersLength = strlen($characters);

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
