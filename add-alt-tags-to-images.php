<?php
/**
 * Plugin Name: Add Alt Tags to Images
 * Plugin URI: https://github.com/graitsol/wp-plugins
 * Description: Automatically adds alt attributes to images based on their file names if they are missing.
 * Version: 1.0.0
 * Author: Graitsol
 * Author URI: https://graitsol.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: add-alt-tags-to-images
 * Domain Path: /languages
 */

// Если этот файл вызывается напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

function add_alt_tags_to_images_v2($content) {

    // Регулярное выражение для поиска тегов <img>
    $pattern = '/<img([^>]*)src="(.*?)"(.*?)([^>]*)>/is';

    // Выполняем поиск и замену
    $content = preg_replace_callback($pattern, function($matches) {
        // Определение переменных
        $img_tag = '<img' . $matches[1] . 'src="' . $matches[2] . '"' . $matches[3] . $matches[4] . '>';

        // Если атрибут alt уже присутствует, ничего не делаем
        if (strpos($img_tag, ' alt="') !== false) {
            return $img_tag;
        }

        // Ищем ближайший заголовок или описание внутри родительского или соседнего элемента
        $sibling_pattern = '/<([a-z]+)[^>]*>(?:(?!<\/\1>).)*' . preg_quote($img_tag, '/') . '(?:(?!<\/\1>).)*<\/\1>/is';
        if (preg_match($sibling_pattern, $content, $sibling_matches) &&
            (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $sibling_matches[0], $header_matches) ||
            preg_match('/<div class="(video-description|description)"[^>]*>(.*?)<\/div>/i', $sibling_matches[0], $header_matches))) {
            $alt_text = $header_matches[1];
        } else {
            $alt_text = '';
        }

        // Добавляем атрибут alt к тегу <img>
        $new_img_tag = str_replace('<img', '<img alt="' . esc_attr($alt_text) . '"', $img_tag);

        return $new_img_tag;
    }, $content);

    return $content;
}

// Добавляем фильтр для изменения содержимого
add_filter('the_content', 'add_alt_tags_to_images_v2');
