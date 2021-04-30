<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts;

/**
 * Interface FieldProcessorInterface
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts
 *
 * @since 29.03.2021
 */
interface FieldProcessorInterface
{
    /**
     * Перед сохранением в базу.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function beforeSaveDb($data);

    /**
     * После загрузки из базы.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function afterLoadFromDb($data);
}
