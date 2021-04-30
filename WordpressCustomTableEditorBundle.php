<?php

namespace Prokl\WordpressCustomTableEditorBundle;

use Prokl\WordpressCustomTableEditorBundle\DependencyInjection\WordpressCustomTableEditorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class WordpressCustomTableEditorBundle
 * @package Prokl\WordpressCustomTableEditorBundle
 *
 * @since 30.04.2021
 */
final class WordpressCustomTableEditorBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new WordpressCustomTableEditorExtension();
        }

        return $this->extension;
    }
}
