<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\FixtureGenerators;

use Prokl\WordpressCustomTableEditorBundle\Services\Utils\WordpressRepository;

/**
 * Class RealImage
 * Случайная реальная картинка из базы.
 * @package Prokl\WordpressCustomTableEditorBundle\Services\FixtureGenerators
 *
 * @since 08.04.2021
 */
class RealImage implements Contracts\FixtureGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate()
    {
        return WordpressRepository::getRandomIdPicture();
    }
}
