<?php

namespace Prokl\WordpressCustomTableEditorBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class WordpressCustomTableEditorExtension
 * @package Prokl\WordpressCustomTableEditorBundle\DependencyInjection
 *
 * @since 30.04.2021
 */
class WordpressCustomTableEditorExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('entities.yaml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'wordpress-custom-table-editor';
    }
}
