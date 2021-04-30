<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\FixtureGenerators;

use Prokl\WordpressCustomTableEditorBundle\Services\Utils\WordpressRepository;

/**
 * Class RealPost
 * Случайный реальный пост из базы.
 * @package Prokl\WordpressCustomTableEditorBundle\Services\FixtureGenerators
 *
 * @since 08.04.2021
 */
class RealPost implements Contracts\FixtureGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate()
    {
        return WordpressRepository::getRandomIdPostWp();
    }
}
