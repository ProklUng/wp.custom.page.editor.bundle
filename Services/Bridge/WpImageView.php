<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Bridge;

use LogicException;
use Prokl\WordpressCustomTableEditorBundle\Services\Bridge\Contracts\ViewGeneratorInterface;

/**
 * Class WpImageView
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Bridge
 *
 * @since 29.03.2021
 */
class WpImageView implements ViewGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function viewList($param): string
    {
        if (!is_numeric($param)) {
            return '<img style="max-width:150px" src="' . $param . '">';
        }
        if ((int)$param !== 0 && !wp_get_attachment_image_url($param)) {
            throw new LogicException(
                'Картинка с ID ' . $param . ' не существует.'
            );
        }

        $url =  (string)wp_get_attachment_image_url($param);

        return '<img style="max-width:150px" src="' . $url . '">';
    }

    /**
     * @inheritDoc
     */
    public function viewEditor($param, $payload = null): string
    {
        if (!is_numeric($param)) {
            return '<div><img style="margin-top: .5rem; max-width:150px" src="' . $param . '"/>
                         <input style="margin-bottom: 1rem" type="file" id="file_'. $payload['id'] .'" 
                    name="file_'. $payload['id'] .'">   
                    </div>';
        }

        if ((int)$param !== 0 && !wp_get_attachment_image_url($param, 'full')) {
            throw new LogicException(
                'Картинка с ID ' . $param . ' не существует.'
            );
        }

        $url = (string)wp_get_attachment_image_url($param, 'full');

        return '<div><img style="margin-top: .5rem; max-width:150px" src="' . $url . '"/>
                    <input style="margin-bottom: 1rem" type="file" id="file_'. $payload['id'] .'" 
                    name="file_'. $payload['id'] .'">
        </div>';
    }
}
