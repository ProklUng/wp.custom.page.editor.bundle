<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Bridge;

use Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts\FieldProcessorInterface;

/**
 * Class WpImage
 * Пример трансформера.
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Bridge
 *
 * @since 29.03.2021
 */
class WpImage implements FieldProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function beforeSaveDb($data)
    {
        if (!is_numeric($data)) {
            // Поиск в базе картинки по URL
            $id = $this->getIDimageByUrl($data);
            if ($id) {
                return $id;
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function afterLoadFromDb($data)
    {
        if (!(int)$data) {
            return $data;
        }

        return (string)wp_get_attachment_image_url((int)$data, 'full');
    }

    /**
     * ID картинки в базе по URL и использованием Corcel.
     *
     * @param mixed $attachment_url URL картинки.
     *
     * @return integer
     */
    private function getIDimageByUrl($attachment_url): int
    {
        $upload_dir_paths = wp_upload_dir();

        if ($attachment_url === ''
            ||
            strpos($attachment_url, $upload_dir_paths['baseurl']) === false
        ) {
            return 0;
        }

        $pattern = '/-\d+x\d+(?=\.(jpg|jpeg|webp|png|gif)$)/i';
        $paths = $upload_dir_paths['baseurl'] . '/';

        $attachment_url = preg_replace($pattern, '', $attachment_url);
        if ($attachment_url === null) {
            return 0;
        }

        $attachment_url = str_replace($paths, '', $attachment_url);

        $attachmenId = attachment_url_to_postid($attachment_url);

        return $attachmenId ?: 0;
    }
}
