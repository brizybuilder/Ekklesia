<?php

namespace BrizyEkklesia\Placeholder;

use BrizyEkklesia\HelperTrait;
use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class SermonLayoutPlaceholder extends PlaceholderAbstract
{
    use HelperTrait;

    const NAME = 'ekk_sermon_layout';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        $options = [
            'show_group_filter'       => false,
            'group_filter_heading'    => 'Group',
            'show_category_filter'    => false,
            'category_filter_heading' => 'Category',
            'show_series_filter'      => false,
            'series_filter_heading'   => 'Series',
            'show_speaker_filter'     => false,
            'speaker_filter_heading'  => 'Speaker',
            'show_search'             => false,
            'search_placeholder'      => 'Search',
            'search_value'            => '',
            'column_count'            => 3,
            'column_count_tablet'     => 2,
            'column_count_mobile'     => 1,
            'show_pagination'         => false,
            'show_images'             => true,
            'show_video'              => false,
            'show_audio'              => true,
            'show_inline_video'       => true,
            'show_inline_audio'       => true,
            'show_media_links'        => true,
            'show_title'              => true,
            'show_date'               => true,
            'show_category'           => true,
            'show_group'              => true,
            'show_series'             => true,
            'show_preacher'           => true,
            'show_passage'            => true,
            'show_meta_headings'      => false,
            'show_preview'            => false,
            'detail_page_button_text' => false,
            'howmany'                 => 9,
            'sticky_space'            => 0,
            'defaultCategory'         => '',
            'show_meta_icons'         => false,
            'parent_category'         => '',
            'group_slug'              => '',
        ];

        $settings = array_merge($options, $placeholder->getAttributes());

        extract($settings);

        $baseURL         = (strtok($_SERVER["REQUEST_URI"],'?') !== FALSE) ? strtok($_SERVER["REQUEST_URI"],'?') : $_SERVER["REQUEST_URI"];
        $filterCountArr  = array($show_group_filter, $show_category_filter, $show_series_filter, $show_speaker_filter);
        $filterCount     = count(array_filter($filterCountArr));
        $detail_url      = $settings['detail_page'] ? $this->replacer->replacePlaceholders(urldecode($settings['detail_page']), $context) : false;
        $cms             = $this->monkCMS;
        $page            = max(1, (int) ($_GET['mc-page'] ?? 1));
        $category_filter = isset($_GET['mc-category']) ? $_GET['mc-category'] : '';
        $requestGroup    = $_GET['mc-group'] ?? false;
        $series_filter   = isset($_GET['mc-series']) ? $_GET['mc-series'] : '';
        $speaker_filter  = isset($_GET['mc-speaker']) ? $_GET['mc-speaker'] : '';
        $search    = $_GET['mc-search'] ?? '';

        if (empty($_GET)) {
            $search = $search_value;
        }

	    if (!isset($_GET['mc-category']) && $defaultCategory) {
		    $category_filter = $defaultCategory;
	    }

        $categories = [];
        if ($show_category_filter) {
            $rawCategories = $cms->get([
                'module'     => 'sermon',
                'order'      => 'title',
                'display'    => 'categories',
                'find_group' => $group_slug ?: $requestGroup,
            ]);

            if (!empty($rawCategories['show'])) {
                foreach ($rawCategories['show'] as $category) {
                    if (!$parent_category || $parent_category == $category['slug'] || $parent_category === $category['bid']) {
                        $categories[$category['slug']] = $category['name'];
                    }
                }
            }
        }

        if ($show_group_filter && !$group_slug) {
            $groups = $cms->get([
                'module'  => 'sermon',
                'display' => 'list',
                'groupby' => 'group',
                'order'   => 'title',
            ]);

            if (!isset($groups['group_show'])) {
                $groups['group_show'] = [];
            }
        }

        if ($show_series_filter) {
            $series = $cms->get([
                'module'     => 'sermon',
                'display'    => 'list',
                'groupby'    => 'series',
                'order'      => 'title',
                'find_group' => $group_slug ?: $requestGroup
            ]);

            if (!isset($series['group_show'])) {
                $series['group_show'] = [];
            }
        }

        if ($show_speaker_filter) {
            $speakers = $cms->get([
                'module'     => 'sermon',
                'display'    => 'list',
                'groupby'    => 'preacher',
                'order'      => 'title',
                'find_group' => $group_slug ?: $requestGroup
            ]);

            if (!isset($speakers['group_show'])) {
                $speakers['group_show'] = [];
            }

            $speakers['group_show'] = array_reverse($speakers['group_show']);
        }

        if (!empty($search)) {
            $content = $cms->get([
                'module'        => 'search',
                'display'       => 'results',
                'howmany'       => (int)$howmany,
                'offset'        => ($page - 1) * (int)$howmany,
                'keywords'      => $search,
                'find_module'   => 'sermon',
                'after_show'    => '__pagination__',
                'find_category' => $category_filter,
            ]);

            if (!empty($content['show'])) {
                foreach ($content['show'] as &$itemShow) {
                    $itemShow = $cms->get([
                        'module'      => 'sermon',
                        'display'     => 'detail',
                        'emailencode' => 'no',
                        'find'        => $itemShow['slug'],
                        'show'        => "__audioplayer__",
                    ])['show'];
                }
            }
        } else {
            $content = $cms->get([
                'module'        => 'sermon',
                'display'       => 'list',
                'order'         => 'recent',
                'emailencode'   => 'no',
                'page'          => $page,
                'howmany'       => $howmany,
                'find_category' => $category_filter,
                'find_group'    => $group_slug ?: $requestGroup,
                'find_series'   => $series_filter,
                'find_preacher' => $speaker_filter,
                'show'          => "__audioplayer__",
                'after_show'    => '__pagination__'
            ]);
        }

        ?>

        <div class="brz-sermonLayout__filter">
            <form id="brz-sermonLayout__filter--form" name="brz-sermonLayout__filter--form" class="brz-sermonLayout__filter--form" action="<?= $baseURL ?>" data-count="<?= $filterCount ?>">
                <?php if ($show_group_filter && !empty($groups['group_show']) && !$group_slug): ?>
                    <div class="brz-sermonLayout__filter--form-selectWrapper">
                        <select name="mc-group" class='sorter' >
                            <option value=""><?= $group_filter_heading ?></option>
                            <option value="">All</option>
                            <?php
                            foreach ($groups['group_show'] as $group) {
                                echo "<option value=\"{$group['slug']}\"";
                                if ($requestGroup == $group['slug']) {
                                    echo " selected";
                                }
                                echo ">{$group['title']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($show_category_filter): ?>
                    <div class="brz-sermonLayout__filter--form-selectWrapper">
                        <select name="mc-category" class='sorter'>
                            <option value=""><?= $category_filter_heading ?></option>
                            <option value="">All</option>
                            <?php
                                foreach ($categories as $slug => $categoryTitle) {
                                    echo "<option value=\"$slug\"".($category_filter == $slug ? ' selected' : '').">$categoryTitle</option>";
                                }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($show_series_filter): ?>
                    <div class="brz-sermonLayout__filter--form-selectWrapper">
                    <select name="mc-series" class='sorter' >
                        <option value=""><?= $series_filter_heading ?></option>
                        <option value="">All</option>
                        <?php
                        foreach($series['group_show'] as $serie)
                        {
                            echo "<option value=\"{$serie['slug']}\"";
                            if(isset($_GET['mc-series']) && $_GET['mc-series'] == $serie['slug'])
                            {
                                echo " selected";
                            }
                            echo ">{$serie['title']}</option>";
                        }
                        ?>
                    </select>
                    </div>
                <?php endif; ?>

                <?php if($show_speaker_filter): ?>
                    <div class="brz-sermonLayout__filter--form-selectWrapper">
                    <select name="mc-speaker" class='sorter' >
                        <option value=""><?= $speaker_filter_heading ?></option>
                        <option value="">All</option>
                        <?php
                        foreach($speakers['group_show'] as $speaker)
                        {
                            echo "<option value=\"{$speaker['slug']}\"";
                            if(isset($_GET['mc-speaker']) && $_GET['mc-speaker'] == $speaker['slug'])
                            {
                                echo " selected";
                            }
                            echo ">{$speaker['title']}</option>";
                        }
                        ?>
                    </select>
                    </div>
                <?php endif; ?>

            </form>

            <?php if($show_search): ?>
                <form method="get" id="brz-sermonLayout__filter--form-search" name="brz-sermonLayout__filter--form-search" class="brz-sermonLayout__filter--form-search" action="<?= $baseURL ?>" data-count="<?= $filterCount ?>">
                    <fieldset>
                        <input type="text" id="ekklesia360_sermon_layout_search_term" name="mc-search" value="<?= $search ?>" placeholder="<?= $search_placeholder ?>" />
                        <button type="submit" id="ekklesia360_sermon_layout_search_submit" value=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="brz-icon-svg" data-type="fa" data-name="search"><path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path></svg></button>
                    </fieldset>
                </form>
            <?php endif; ?>
        </div>

        <?php
            if (!empty($_GET['mc-search'])) {
                echo "<h5 class=\"brz-sermonLayout-results-heading\"><a href=\"$baseURL\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 352 512\" class=\"brz-icon-svg\" data-type=\"fa\" data-name=\"times\"><path d=\"M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z\"></path></svg></a> Search results for \"$search\"</h5>";
            }
        ?>

        <div class="brz-sermonLayout__container">
            <?php
            if (!empty($content['show'])) {
                foreach ($content['show'] as $item) {
                    echo "<div class=\"brz-sermonLayout__item\">";

                        if ($show_images && $item['imageurl'] && !$show_video) {
                            if ($detail_url) {
                                echo "<a href=\"{$detail_url}?mc-slug={$item['slug']}\">";
                            }
                            echo "<div class=\"brz-ministryBrands__item--media\"><img src=\"{$item['imageurl']}\" alt=\"\" /></div>";
                            if ($detail_url) {
                                echo "</a>";
                            }
                        }

                        if ($show_video) {
                            if ($item['videoembed']) {
                                echo "<div class=\"brz-ministryBrands__item--media\">{$item['videoembed']}</div>";
                            } elseif ($item['videourl']) {
                                $videoext = pathinfo($item['videourl'], PATHINFO_EXTENSION);
                                echo "<div class=\"brz-ministryBrands__item--media\">";
                                echo "<video src=\"{$item['videourl']}\" controls preload=\"none\" width=\"1024\" height=\"576\" poster=\"{$item['imageurl']}\" type=\"video/{$videoext}\"><p>The Video could not be played. Please <a href=\"{$item['videourl']}\" target=\"_blank\">download it here</a>.</p></video>";
                                echo "</div>";
                            } elseif ($show_images && $item['imageurl']) {
                                echo "<div class=\"brz-ministryBrands__item--media\"><img src=\"{$item['imageurl']}\" alt=\"\" /></div>";
                            }
                        }
                        if ($show_audio && $item['audiourl']) {
                            echo "<div class=\"brz-sermonLayout__item--media--audio\">";
                            echo "<audio src=\"{$item['audiourl']}\" controls preload=\"none\"></audio>";
                            echo "</div>";
                        }

                        if ($show_title) {
                            echo "<h4 class=\"brz-sermonLayout__item--meta--title brz-ministryBrands__item--meta-title\">";
                            if ($detail_url) {
                                echo "<a href=\"{$detail_url}?mc-slug={$item['slug']}\">";
                            }
                            echo "{$item['title']}";
                            if ($detail_url) {
                                echo "</a>";
                            }
                            echo "</h4>";
                        }

                    if($show_date && $item['date'])
                    {
                        echo "<h6 class=\"brz-sermonLayout__item--meta brz-ministryBrands__item--meta-date\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M96 32V64H48C21.5 64 0 85.5 0 112v48H448V112c0-26.5-21.5-48-48-48H352V32c0-17.7-14.3-32-32-32s-32 14.3-32 32V64H160V32c0-17.7-14.3-32-32-32S96 14.3 96 32zM448 192H0V464c0 26.5 21.5 48 48 48H400c26.5 0 48-21.5 48-48V192z\"></path></svg>
</span>";
                            else echo "<span>Date: </span>";
                        }
                        echo "<span>{$item['date']}</span>";
                            echo "</h6>";
                        }
                    if($show_category && $item['category'])
                    {
                        echo "<h6 class=\"brz-sermonLayout__item--meta brz-ministryBrands__item--meta-category\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M0 48V487.7C0 501.1 10.9 512 24.3 512c5 0 9.9-1.5 14-4.4L192 400 345.7 507.6c4.1 2.9 9 4.4 14 4.4c13.4 0 24.3-10.9 24.3-24.3V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48z\"></path></svg>
</span>";
                            else echo "<span>Category: </span>";
                        }
                        echo "<span>{$item['category']}</span>";
                            echo "</h6>";
                        }
                    if($show_group && $item['group'])
                    {
                        echo "<h6 class=\"brz-sermonLayout__item--meta brz-ministryBrands__item--meta-group\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 640 512\"><path fill=\"currentColor\" d=\"M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z\"></path></svg>
</span>";
                            else echo "<span>Group: </span>";
                        }
                        echo "<span>{$item['group']}</span>";
                            echo "</h6>";
                        }
                    if($show_series && $item['series'])
                    {
                        echo "<h6 class=\"brz-sermonLayout__item--meta brz-ministryBrands__item--meta-series\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M40 48C26.7 48 16 58.7 16 72v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V72c0-13.3-10.7-24-24-24H40zM192 64c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zM16 232v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V232c0-13.3-10.7-24-24-24H40c-13.3 0-24 10.7-24 24zM40 368c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V392c0-13.3-10.7-24-24-24H40z\"></path></svg>
</span>";
                            else echo "<span>Series: </span>";
                        }
                        echo "<span>{$item['series']}</span>";
                            echo "</h6>";
                        }
                    if($show_preacher && $item['preacher'])
                    {
                        echo "<h6 class=\"brz-sermonLayout__item--meta brz-ministryBrands__item--meta-preacher\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z\"></path></svg>
</span>";
                            else echo "<span>Speaker: </span>";
                        }
                        echo "<span>{$item['preacher']}</span>";
                            echo "</h6>";
                        }
                    if($show_passage && $item['passages'])
                    {
                        echo "<h6 class=\"brz-sermonLayout__item--meta-passages brz-ministryBrands__item--meta-passage\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M96 0C43 0 0 43 0 96V416c0 53 43 96 96 96H384h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V384c17.7 0 32-14.3 32-32V32c0-17.7-14.3-32-32-32H384 96zm0 384H352v64H96c-17.7 0-32-14.3-32-32s14.3-32 32-32zM208 80c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v48h48c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272V304c0 8.8-7.2 16-16 16H224c-8.8 0-16-7.2-16-16V192H160c-8.8 0-16-7.2-16-16V144c0-8.8 7.2-16 16-16h48V80z\"></path></svg>
</span>";
                            else echo "<span class='brz-sermonLayout__item--meta'>Passages: </span>";
                        }
                        echo "<span class=\"brz-ministryBrands__item--meta-passage-content\">{$item['passages']}</span>";
                            echo "</h6>";
                        }

                        if ($show_media_links) {
                            echo "<ul class=\"brz-sermonLayout__item--media\">";
                            if ($item['videoplayer']) {
                                $item['videoplayer'] = preg_replace('/<a(.+?)>.+?<\/a>/i',
                                    "<a$1><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\" class=\"brz-icon-svg\" data-type=\"fa\" data-name=\"desktop\"><path d=\"M528 0H48C21.5 0 0 21.5 0 48v320c0 26.5 21.5 48 48 48h192l-16 48h-72c-13.3 0-24 10.7-24 24s10.7 24 24 24h272c13.3 0 24-10.7 24-24s-10.7-24-24-24h-72l-16-48h192c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zm-16 352H64V64h448v288z\"></path></svg></a>",
                                    $item['videoplayer']);
                                echo "<li class=\"brz-sermonLayout__item--media_videoplayer\">{$item['videoplayer']}</li>";
                            }

                            if ($item['audioplayer']) {
                                $item['audioplayer'] = preg_replace('/<a(.+?)>.+?<\/a>/i',
                                    "<a$1><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\" class=\"brz-icon-svg\" data-type=\"fa\" data-name=\"volume-up\"><path d=\"M215.03 71.05L126.06 160H24c-13.26 0-24 10.74-24 24v144c0 13.25 10.74 24 24 24h102.06l88.97 88.95c15.03 15.03 40.97 4.47 40.97-16.97V88.02c0-21.46-25.96-31.98-40.97-16.97zm233.32-51.08c-11.17-7.33-26.18-4.24-33.51 6.95-7.34 11.17-4.22 26.18 6.95 33.51 66.27 43.49 105.82 116.6 105.82 195.58 0 78.98-39.55 152.09-105.82 195.58-11.17 7.32-14.29 22.34-6.95 33.5 7.04 10.71 21.93 14.56 33.51 6.95C528.27 439.58 576 351.33 576 256S528.27 72.43 448.35 19.97zM480 256c0-63.53-32.06-121.94-85.77-156.24-11.19-7.14-26.03-3.82-33.12 7.46s-3.78 26.21 7.41 33.36C408.27 165.97 432 209.11 432 256s-23.73 90.03-63.48 115.42c-11.19 7.14-14.5 22.07-7.41 33.36 6.51 10.36 21.12 15.14 33.12 7.46C447.94 377.94 480 319.54 480 256zm-141.77-76.87c-11.58-6.33-26.19-2.16-32.61 9.45-6.39 11.61-2.16 26.2 9.45 32.61C327.98 228.28 336 241.63 336 256c0 14.38-8.02 27.72-20.92 34.81-11.61 6.41-15.84 21-9.45 32.61 6.43 11.66 21.05 15.8 32.61 9.45 28.23-15.55 45.77-45 45.77-76.88s-17.54-61.32-45.78-76.86z\"></path></svg></a>",
                                    $item['audioplayer']);
                                echo "<li class=\"brz-sermonLayout__item--media_audioplayer\">{$item['audioplayer']}</li>";
                            }

                            if ($item['notes']) {
                                echo "<li class=\"brz-sermonLayout__item--media_notes\"><a href=\"{$item['notes']}\" target=\"_blank\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\" class=\"brz-icon-svg\" data-type=\"fa\" data-name=\"file-alt\"><path d=\"M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm64 236c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-64c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-72v8c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12zm96-114.1v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z\"></path></svg></a></li>";
                            }
                            echo "</ul>";
                        }

                    if ($show_preview && $item['preview']) {
                            echo '<p class="brz-sermonLayout__item--preview">';
                        echo $this->excerpt($item['preview']);
                        echo '</p>';
                        }

                        if ($detail_url && $detail_page_button_text) {
                            echo "<p class=\"brz-sermonLayout__item--detail-button\"><a href=\"{$detail_url}?mc-slug={$item['slug']}\" class=\"brz-button-link brz-button brz-size-sm\"><span class=\"brz-button-text\">{$detail_page_button_text}</span></a></p>";
                        }

                    echo "</div>";
                }
            } else {
                echo '<p class="brz-sermonLayout-no-results">There are no sermons available.</p>';
            }
            ?>
        </div>
        <?php
        if ($show_pagination && !empty($content['after_show']['pagination'])) {
            $pagination = str_replace('id="pagination"', 'class="brz-ministryBrands__pagination"', $content['after_show']['pagination']);
            $pagination = str_replace('page=', 'mc-page=', $pagination);

            if (!empty($_GET['mc-group'])) {
                $pagination = str_replace('?', "?mc-group={$_GET['mc-group']}&", $pagination);
            }

            if ($category_filter && !empty($_GET['mc-category'])) {
                $pagination = str_replace('?', "?mc-category=$category_filter&", $pagination);
            }

            if (!empty($_GET['mc-series'])) {
                $pagination = str_replace('?', "?mc-series={$_GET['mc-series']}&", $pagination);
            }

            if (!empty($_GET['mc-speaker'])) {
                $pagination = str_replace('?', "?mc-speaker={$_GET['mc-speaker']}&", $pagination);
            }

            if (!empty($search)) {
                $pagination = str_replace('?', "?mc-search=$search&", $pagination);
            }

            echo $pagination;
        }
    }
}