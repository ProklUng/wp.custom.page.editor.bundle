<?php

namespace Prokl\WordpressCustomTableEditorBundle\Services\Utils;

use Exception;
use WP_Query;

/**
 * Class WordpressRepository
 * Репозиторий доступа к базе данных Wp.
 * @package Prokl\WordpressCustomTableEditorBundle\Services\Utils
 *
 * @since 26.12.2020 ID случайной картинки. URL случайной картинки.
 */
class WordpressRepository
{
    /**
     * ID случайной картинки.
     *
     * @return integer
     *
     * @since 26.12.2020
     */
    public static function getRandomIdPicture() : int
    {
        $args = [
            'post_type'      => 'attachment',
            'orderby'        => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
        ];

        $query = new WP_Query($args);

        $result = $query->query($args);
        wp_reset_query();

        return current($result)->ID;
    }

    /**
     * URL случайной картинки.
     *
     * @return string
     *
     * @since 26.12.2020
     */
    public static function getUrlRandomPicture() : string
    {
        $idRandomPicture = self::getRandomIdPicture();

        $arData = wp_get_attachment_image_src($idRandomPicture, 'full');

        return $arData[0] ?? '';
    }

    /**
     * ID случайного поста средствами Wp.
     *
     * @param string $postType Тип поста. По умолчанию - post.
     *
     * @return integer
     */
    public static function getRandomIdPostWp($postType = 'post'): int
    {
        $args = [
            'post_type' => $postType,
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
        ];

        $query = new WP_Query();
        $result = $query->query($args);
        wp_reset_query();

        return current($result)->ID;
    }

    /**
     * Массовое получение ID случайных постов.
     *
     * @param integer $qty  Количество.
     * @param string  $type Тип поста. По умолчанию - post.
     *
     * @return array
     *
     * @since 26.12.2020
     */
    public static function getRandomIdPostMassive(int $qty, string $type = 'post') : array
    {
        $result = [];
        for ($i = 0; $i<$qty; $i++) {
            $result[] = self::getRandomIdPostWp($type);
        }

        return $result;
    }

    /**
     * ID случайной страницы.
     *
     * @return integer
     */
    public static function getRandomIdPage(): int
    {
        return self::getRandomIdPostWp( 'page');
    }

    /**
     * Случайный ID поста в категории средствами WP.
     *
     * @param string $categorySlug Метка категории.
     *
     * @return integer
     */
    public static function getRandomIdPostInCategoryWp(string $categorySlug): int
    {
        $args = [
            'post_type' => 'post',
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
            'category_name' => $categorySlug,
        ];

        $query = new WP_Query();

        wp_reset_query();

        return current($query->query($args))->ID;
    }

    /**
     * ID элемента с контентом.
     *
     * @param integer $minLenghth Минимальная длина поста.
     * @param string  $postType   Тип поста. По умолчанию - post.
     *
     * @return integer
     */
    public static function getRandomIdPostWithContentWp(
        int $minLenghth = 650,
        $postType = 'post'
    ): int {
        $args = [
            'post_type' => $postType,
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
        ];

        $query = new WP_Query();

        do {
            $result = current($query->query($args));
        } while (strlen($result->post_content) < $minLenghth);

        wp_reset_query();

        return $result->ID;
    }

    /**
     * ID элемента без контента.
     *
     * @param string  $postType Тип поста. По умолчанию - post.
     *
     * @return integer
     *
     * @since 26.12.2020
     */
    public static function getRandomIdPostWithoutContentWp(
        $postType = 'post'
    ): int {
        $args = [
            'post_type' => $postType,
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
        ];

        $query = new WP_Query();

        do {
            $result = current($query->query($args));
        } while ($result->post_content !== '');

        wp_reset_query();

        return $result->ID;
    }

    /**
     * ID поста с картинкой. Средствами Wordpress.
     *
     * @param string $postType
     * @param array $categories
     *
     * @return integer
     */
    public static function getRandomIdPostPictureWp(
        $postType = 'post',
        $categories = []
    ): int {
        $args = [
            'post_type' => $postType,
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => '_thumbnail_id',
                ],
            ],
            'category__in' => $categories
        ];

        $query = new WP_Query();
        $result = $query->query($args);

        wp_reset_query();

        return current($result)->ID;
    }

    /**
     * ID поста без картинки. Средствами Wordpress.
     *
     * @param string $postType
     *
     * @return integer
     */
    public static function getRandomIdPostWithoutPictureWp($postType = 'post'): int
    {
        $args = [
            'post_type' => $postType,
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => '_thumbnail_id',
                    'value' => '?',
                    'compare' => 'NOT EXISTS'
                ],
            ],
        ];

        $query = new WP_Query();
        $result = $query->query($args);

        return current($result)->ID;
    }

    /**
     * ID случайной категории.
     *
     * @return integer
     * @throws Exception
     */
    public static function getRandomCategoryId(): int
    {
        $categories = get_categories();
        do {
            $index = random_int(0, count($categories));

            $obRandomCategory = $categories[$index] ?? null;
        } while ($obRandomCategory->cat_ID === null);

        return $obRandomCategory->cat_ID;
    }

    /**
     * Случайный пост по мета полю.
     *
     * @param string $metaKey   Мета поле.
     * @param mixed  $metaValue Значение.
     *
     * @return integer
     *
     * @since 26.12.2020
     */
    public static function getRandomIdByMetaValue(
        string $metaKey,
        $metaValue
    ): int {
        $args = [
            'post_type' => 'post',
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => $metaKey,
                    'value' => $metaValue,
                    'compare' => '='
                ],
            ],
        ];

        $query = new WP_Query();
        $result = $query->query($args);

        return current($result)->ID;
    }

    /**
     * Случайный пост по мета полю. Отрицание.
     *
     * @param string $metaKey Мета поле.
     *
     * @return integer
     *
     * @since 26.12.2020
     */
    public static function getRandomIdByNotMetaValue(
        string $metaKey
    ): int {
        $args = [
            'post_type' => 'post',
            'orderby' => 'rand',
            'posts_per_page' => 1,
            'post_status' => 'published',
            'nopaging' => false,
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => $metaKey,
                    'compare' => 'NOT EXISTS'
                ],
            ],
        ];

        $query = new WP_Query();
        $result = $query->query($args);

        return current($result)->ID;
    }

    /**
     * Слаг тэга с картинками.
     *
     * @return string
     *
     * @since 26.12.2020
     */
    public static function getTagWithTagPicture() : string
    {
        $idPost =  self::getRandomIdPostPictureWp(
            'tagspicture'
        );

        return get_post_field('post_title', $idPost);
    }

    /**
     * Случайный тэг без картинки.
     *
     * @return string
     * @throws Exception
     *
     * @since 26.12.2020
     */
    public static function getRandomTagSlugWithoutPicture() : string
    {
        do {
            $slug = self::getRandomTagSlug();

            $args = [
                'post_type' => 'tagspicture',
                'orderby' => 'rand',
                'posts_per_page' => 1,
                'post_status' => 'published',
                'post_title' => $slug,
                'nopaging' => false,
                'no_found_rows' => true,
                'meta_query' => [
                    [
                        'key' => '_thumbnail_id',
                    ],
                ],
            ];

            $query = new WP_Query();
            $result = $query->query($args);

            wp_reset_query();
        } while (current($result)->ID === null);

        return $slug;
    }

    /**
     * Случайный тэг.
     *
     * @return string
     * @throws Exception
     */
    public static function getRandomTagSlug() : string
    {
        $alltags = get_tags();

        return $alltags[random_int(0, count($alltags))]->slug;
    }

    /**
     * Случайный ID поста с заполненным ACF полем video_element.
     *
     * @return integer
     *
     * @since 29.12.2020
     */
    public static function getIdPostWithAcfVideo() : int
    {
            $args = [
                'post_type' => 'post',
                'orderby' => 'rand',
                'posts_per_page' => 1,
                'post_status' => 'published',
                'nopaging' => false,
                'no_found_rows' => true,
                'meta_query' => [
                    [
                        'key' => 'video_element',
                        'value' => '',
                        'compare' => '<>'
                    ],
                ],
            ];

            $query = new WP_Query();
            $result = $query->query($args);

            wp_reset_query();

            return current($result)->ID ?: 0;
    }
}
