<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts;

/**
 * Interface ViewGeneratorInterface
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts
 *
 * @since 29.03.2021
 */
interface ViewGeneratorInterface
{
    /**
     * @param mixed $param
     *
     * @return string
     */
    public function viewList($param) : string;

    /**
     * @param mixed $param
     * @param mixed $payload
     *
     * @return string
     */
    public function viewEditor($param, $payload = null): string;
}
