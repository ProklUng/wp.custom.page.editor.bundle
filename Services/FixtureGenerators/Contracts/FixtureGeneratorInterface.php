<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\FixtureGenerators\Contracts;

/**
 * Interface FixtureGeneratorInterface
 * @package Prokl\WordpressCustomTableEditorBundle\Services\FixtureGenerators\Contracts
 *
 * @since 08.04.2021
 */
interface FixtureGeneratorInterface
{
    /**
     * Сгенерировать фикстуру для поля.
     *
     * @return mixed
     */
    public function generate();
}
