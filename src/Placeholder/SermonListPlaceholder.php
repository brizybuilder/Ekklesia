<?php

namespace BrizyEkklesia\Placeholder;

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class SermonListPlaceholder extends PlaceholderAbstract
{
    const NAME = 'ekk_sermon_list';

    public function getValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        $attributes = $placeholder->getAttributes();

        $features    = empty($attributes['features']) ? '' : 'features';
        $nonfeatures = empty($attributes['nonfeatures']) ? '' : 'nonfeatures';

        if ($features) {
            $nonfeatures = '';
        } elseif ($nonfeatures) {
            $features = '';
        }

        if (!$content = $this->monkCMS->get([
            'module'        => 'sermon',
            'display'       => 'list',
            'order'         => 'recent',
            'emailencode'   => 'no',
            'howmany'       => isset($attributes['howmany']) && is_numeric($attributes['howmany']) ? (int) round($attributes['howmany']) : 9,
            'page'          => isset($_GET['mc-page']) ? $_GET['mc-page'] : 1,
            'find_category' => (isset($attributes['category']) && $attributes['category'] != 'all') ? $attributes['category'] : '',
            'find_group'    => (isset($attributes['group']) && $attributes['group'] != 'all') ? $attributes['group'] : '',
            'find_series'   => (isset($attributes['series']) && $attributes['series'] != 'all') ? $attributes['series'] : '',
            'features'      => $features,
            'nonfeatures'   => $nonfeatures,
            'show'          => "__audioplayer__",
            'after_show'    => '__pagination__',
        ])) {
            return '';
        }

        $twigHtml = file_get_contents(
            __DIR__ . '/../views/sermon_list.html.twig'
        );

        $template = $this->twig->createTemplate($twigHtml);

        return htmlspecialchars_decode(
            $template->render(
                [
                    'content'                 => $content,
                    'column_count'            => $attributes['column_count'] ?? '3',
                    'column_count_tablet'     => $attributes['column_count_tablet'] ?? '2',
                    'column_count_mobile'     => $attributes['column_count_mobile'] ?? '1',
                    'show_pagination'         => $attributes['show_pagination'],
                    'features'                => $features,
                    'nonfeatures'             => $nonfeatures,
                    'group'                   => $attributes['group'],
                    'category'                => $attributes['category'],
                    'series'                  => $attributes['series'],
                    'show_images'             => $attributes['show_images'],
                    'show_video'              => $attributes['show_inline_video'],
                    'show_audio'              => $attributes['show_inline_audio'],
                    'show_media_links'        => $attributes['show_media_links'],
                    'show_title'              => $attributes['show_title'],
                    'show_date'               => $attributes['show_date'],
                    'show_category'           => $attributes['show_category'],
                    'show_group'              => $attributes['show_group'],
                    'show_series'             => $attributes['show_series'],
                    'show_preacher'           => $attributes['show_preacher'],
                    'show_passage'            => $attributes['show_passage'],
                    'show_meta_headings'      => $attributes['show_meta_headings'],
                    'show_preview'            => $attributes['show_preview'],
                    'detail_page_button_text' => $attributes['detail_page_button_text'] ?? '',
                    'detail_url'              => $attributes['detail_url'] ? $this->replacer->replacePlaceholders(urldecode($attributes['detail_url']), $context) : false,
                    'sticky_space'            => $attributes['sticky_space'] ? (int)$attributes['sticky_space'] : '0',
                    'show_meta_icons'         => $attributes['show_meta_icons']
                ]
            ),
            ENT_COMPAT | ENT_HTML401
        );
    }
}
