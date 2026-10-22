<?php

namespace BrizyEkklesia\Placeholder;

use BrizyEkklesia\HelperTrait;
use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class ArticleLayoutPlaceholder extends PlaceholderAbstract
{
    use HelperTrait;

    const NAME = 'ekk_article_layout';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        $options = [
            'show_category'                => false,
            'parent_category'              => 'articles',
            'show_category_filter'         => true,
            'category_filter_parent'       => 'childrens-ministry',
            'category_filter_heading'      => 'Category',
            'show_category_filter_add1'    => false,
            'category_filter_parent_add1'  => '',
            'category_filter_heading_add1' => 'Category',
            'show_category_filter_add2'    => false,
            'category_filter_parent_add2'  => '',
            'category_filter_heading_add2' => 'Category',
            'show_category_filter_add3'    => false,
            'category_filter_parent_add3'  => '',
            'category_filter_heading_add3' => 'Category',
            'show_group_filter'            => true,
            'group_filter_heading'         => 'Group',
            'show_search'                  => true,
            'search_placeholder'           => 'Search',
            'show_pagination'              => true,
            'show_series_filter'           => true,
            'series_filter_heading'        => 'Series',
            'show_author_filter'           => true,
            'author_filter_heading'        => 'Author',
            'howmany'                      => 3,
            'show_images'                  => true,
            'show_video'                   => false,
            'show_audio'                   => false,
            'show_media_links'             => false,
            'show_title'                   => true,
            'show_date'                    => false,
            'show_group'                   => false,
            'show_series'                  => false,
            'show_author'                  => false,
            'show_meta_headings'           => false,
            'show_preview'                 => false,
            'detail_page_button_text'      => '',
            'detail_page'                  => '',
            'show_meta_icons'        => false
        ];

        $settings = array_merge($options, $placeholder->getAttributes());
        $cms      = $this->monkCMS;

        extract($settings);

        $baseURL     = strtok($_SERVER["REQUEST_URI"], '?') !== FALSE ? strtok($_SERVER["REQUEST_URI"], '?') : $_SERVER["REQUEST_URI"];
        $filterCount = count(array_filter([$show_category_filter, $show_category_filter_add1, $show_category_filter_add2, $show_category_filter_add3, $show_group_filter, $show_series_filter, $show_author_filter]));
        $detail_url  = $detail_page ? $this->replacer->replacePlaceholders(urldecode($detail_page), $context) : '';
        $page        = isset($_GET['mc-page']) ? $_GET['mc-page'] : 1;

        // the api omits empty lists; default them so usort()/count() below always get arrays
        $categories        = ($cms->get(['module' => 'article', 'display' => 'categories']) ?: []) + ['show' => [], 'level3' => []];
        $categories_parent = ($cms->get(['module' => 'article', 'display' => 'categories', 'parent_category' => $parent_category,]) ?: []) + ['level1' => []];
        $groups            = $cms->get(['module' => 'article', 'display' => 'list', 'groupby' => 'group', 'find_parent_category' => $parent_category,]);
        $series            = ($cms->get(['module' => 'article', 'display' => 'list', 'groupby' => 'series', 'find_parent_category' => $parent_category,]) ?: []) + ['group_show' => []];
        $authors           = ($cms->get(['module' => 'article', 'display' => 'list', 'groupby' => 'author', 'find_parent_category' => $parent_category,]) ?: []) + ['group_show' => []];

        if (isset($_GET['mc-search_term'])) {
            $content    = [];
            $search_arr = $cms->get([
                'module'        => 'search',
                'display'       => 'results',
                'howmany'       => '100',
                'find_category' => $parent_category,
                'keywords'      => $_GET['mc-search_term'],
                'find_module'   => 'article',
                'hide_module'   => 'media',
            ]);

            foreach ($search_arr['show'] as $search) {
                $item = $cms->get([
                    'module'      => 'article',
                    'display'     => 'detail',
                    'emailencode' => 'no',
                    'show'        => "__videoplayer fullscreen='true'__",
                    'show'        => "__audioplayer__",
                    'find'        => $search['slug'],
                ]);
                $content['show'][] = $item['show'];
            }
        } else {
            $content = $cms->get([
                'module'               => 'article',
                'display'              => 'list',
                'order'                => 'recent',
                'emailencode'          => 'no',
                'howmany'              => '100',
                'find_parent_category' => $parent_category,
                'find_group'           => isset($_GET['mc-group']) ? $_GET['mc-group'] : '',
                'find_series'          => isset($_GET['mc-series']) ? $_GET['mc-series'] : '',
                'find_author'          => isset($_GET['mc-author']) ? $_GET['mc-author'] : '',
                'show'                 => "__videoplayer fullscreen='true'__",
                'show'                 => "__audioplayer__",
            ]);
            //filter categories separately since there can be more than 1 category filter
            if (!empty($_GET['mc-category'])) {
                $content['show'] = self::searchArray($content['show'], $_GET['mc-category']);
            }
            if (!empty($_GET['mc-category_add1'])) {
                $content['show'] = self::searchArray($content['show'], $_GET['mc-category_add1']);
            }
            if (!empty($_GET['mc-category_add2'])) {
                $content['show'] = self::searchArray($content['show'], $_GET['mc-category_add2']);
            }
            if (!empty($_GET['mc-category_add3'])) {
                $content['show'] = self::searchArray($content['show'], $_GET['mc-category_add3']);
            }
        }

        ?>

        <div id="brz-articleLayout__filters" class="brz-articleLayout__filters">
            <form id="brz-articleLayout__form" class="brz-articleLayout__filters--form" action="<?= $baseURL ?>" data-count="<?= $filterCount ?>">
                <?php if($show_group_filter && !empty($groups['group_show'])): ?>
                    <div class="brz-articleLayout__filters--form-sorter">
                        <select name="mc-group" class="brz-articleLayout__filters--form-select">
                            <option value=""><?= $group_filter_heading ?></option>
                            <option value="">All</option>
                            <?php
                            usort($groups["group_show"], function($a, $b) {
                                return strcasecmp($a["title"], $b["title"]);
                            });
                            foreach ($groups['group_show'] as $group) {
                                echo "<option value=\"{$group['slug']}\"";
                                if (isset($_GET['mc-group']) && $_GET['mc-group'] == $group['slug']) {
                                    echo " selected";
                                }
                                echo ">{$group['title']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php
                if($show_category_filter): ?>
                    <div class="brz-articleLayout__filters--form-sorter">
                        <select name="mc-category" class="brz-articleLayout__filters--form-select">
                            <option value=""><?= $category_filter_heading ?></option>
                            <option value="">All</option>
                            <?php
                            //since this is the main category filter this will always show the
                            if ($category_filter_parent && !empty($categories['level3'])) {
                                usort($categories["level3"], function($a, $b) {
                                    return strcasecmp($a["name"], $b["name"]);
                                });
                                foreach ($categories['level3'] as $category) {
                                    if ($category['slug'] != $category_filter_parent) {
                                        continue;
                                    }
                                    echo "<option value=\"{$category['slug']}\"";
                                    if (isset($_GET['mc-category']) && $_GET['mc-category'] == $category['slug']) {
                                        echo " selected";
                                    }
                                    echo ">{$category['name']}</option>";
                                }
                            } else {
                                if ($parent_category != "") {
                                    usort($categories_parent["level1"], function($a, $b) {
                                        return strcasecmp($a["name"], $b["name"]);
                                    });
                                    foreach ($categories_parent["level1"] as $category) {
                                        echo "<option value=\"{$category['slug']}\"";
                                        if (isset($_GET['mc-category']) && $_GET['mc-category'] == $category['slug']) {
                                            echo " selected";
                                        }
                                        echo ">{$category['name']}</option>";
                                    }
                                } else {
                                    usort($categories["show"], function($a, $b) {
                                        return strcasecmp($a["name"], $b["name"]);
                                    });
                                    foreach ($categories["show"] as $category) {
                                        echo "<option value=\"{$category['slug']}\"";
                                        if (isset($_GET['mc-category']) && $_GET['mc-category'] == $category['slug']) {
                                            echo " selected";
                                        }
                                        echo ">{$category['name']}</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php
                if($show_category_filter_add1 && $category_filter_parent_add1): ?>
                    <div class="brz-articleLayout__filters--form-sorter">
                        <select name="mc-category_add1" class="brz-articleLayout__filters--form-select">
                            <option value=""><?= $category_filter_heading_add1 ?></option>
                            <option value="">All</option>
                            <?php
                            usort($categories["level3"], function($a, $b) {
                                return strcasecmp($a["name"], $b["name"]);
                            });
                            foreach ($categories["level3"] as $category) {
                                if ($category["slug"] != $category_filter_parent_add1) {
                                    continue;
                                }
                                echo "<option value=\"{$category['slug']}\"";
                                if (isset($_GET['mc-category_add1']) && $_GET['mc-category_add1'] == $category['slug']) {
                                    echo " selected";
                                }
                                echo ">{$category['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php
                if($show_category_filter_add2 && $category_filter_parent_add2 != ""): ?>
                    <div class="brz-articleLayout__filters--form-sorter">
                        <select name="mc-category_add2" class="brz-articleLayout__filters--form-select">
                            <option value=""><?= $category_filter_heading_add2 ?></option>
                            <option value="">All</option>
                            <?php
                            usort($categories["level3"], function ($a, $b) {
                                return strcasecmp($a["name"], $b["name"]);
                            });
                            foreach ($categories["level3"] as $category) {
                                if ($category["slug"] != $category_filter_parent_add2) {
                                    continue;
                                }
                                echo "<option value=\"{$category['slug']}\"";
                                if (isset($_GET['mc-category_add2']) && $_GET['mc-category_add2'] == $category['slug']) {
                                    echo " selected";
                                }
                                echo ">{$category['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php
                if($show_category_filter_add3 && $category_filter_parent_add3 != ""): ?>
                    <div class="brz-articleLayout__filters--form-sorter">
                        <select name="mc-category_add3" class="brz-articleLayout__filters--form-select">
                            <option value=""><?= $category_filter_heading_add3 ?></option>
                            <option value="">All</option>
                            <?php
                            usort($categories["level3"], function ($a, $b) {
                                return strcasecmp($a["name"], $b["name"]);
                            });
                            foreach ($categories["level3"] as $category) {
                                if ($category["slug"] != $category_filter_parent_add3) {
                                    continue;
                                }
                                echo "<option value=\"{$category['slug']}\"";
                                if (isset($_GET['mc-category_add3']) && $_GET['mc-category_add3'] == $category['slug']) {
                                    echo " selected";
                                }
                                echo ">{$category['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if($show_series_filter && count($series['group_show']) > 0): ?>
                    <div class="brz-articleLayout__filters--form-sorter">
                        <select name="mc-series" class="brz-articleLayout__filters--form-select">
                            <option value=""><?= $series_filter_heading ?></option>
                            <option value="">All</option>
                            <?php
                            usort($series['group_show'], function ($a, $b) {
                                return strcasecmp($a["title"], $b["title"]);
                            });
                            foreach ($series['group_show'] as $serie) {
                                echo "<option value=\"{$serie['slug']}\"";
                                if (isset($_GET['mc-series']) && $_GET['mc-series'] == $serie['slug']) {
                                    echo " selected";
                                }
                                echo ">{$serie['title']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if($show_author_filter && count($authors['group_show']) > 0): ?>
                    <div class="brz-articleLayout__filters--form-sorter">
                        <select name="mc-author" class="brz-articleLayout__filters--form-select">
                            <option value=""><?= $author_filter_heading ?></option>
                            <option value="">All</option>
                            <?php
                            usort($authors["group_show"], function($a, $b) {
                                return strcasecmp($a["title"], $b["title"]);
                            });
                            foreach ($authors['group_show'] as $val) {
                                echo "<option value=\"{$val['slug']}\"";
                                if (isset($_GET['mc-author']) && $_GET['mc-author'] == $val['slug']) {
                                    echo " selected";
                                }
                                echo ">{$val['title']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>
            </form>

            <?php if ($show_search): ?>
                <form method="get" id="brz-articleLayout__filters--form-search" class="brz-articleLayout__filters--form-search" name="mc-search" action="<?= $baseURL ?>" data-count="<?= $filterCount ?>">
                    <fieldset>
                        <input type="text" id="brz-articleLayout__filters--form-search_term" class="brz-articleLayout__filters--form-search_term" name="mc-search_term" value="" placeholder="<?= $search_placeholder ?>"/>
                        <button type="submit" name="submit" id="brz-articleLayout__filters--form-search_submit" class="brz-articleLayout__filters--form-search_submit" value="">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="brz-icon-svg align-[initial]" data-type="fa" data-name="search"><path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path></svg>
                        </button>
                    </fieldset>
                </form>
            <?php endif; ?>
        </div>

        <?php
        if (isset($_GET['mc-search_term'])) {
            echo "<h4 class=\"brz-articleLayout__results_heading\"><a href=\"{$baseURL}\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 352 512\" class=\"brz-icon-svg align-[initial]\" data-type=\"fa\" data-name=\"times\"><path d=\"M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z\"></path></svg></a> Search results for \"{$_GET['mc-search_term']}\"</h4>";
        }
        ?>
        <div id="brz-articleLayout__container" class="brz-articleLayout__container">

            <?php
            $pagination = new CustomPagination($content["show"] ?: [] , $page, $howmany);
            $pagination->setShowFirstAndLast(true);
            $resultsPagination = $pagination->getResults();

            if (count($resultsPagination) > 0) { ?>
                <div class="brz-articleLayout__content">
                    <?php
                    foreach ($resultsPagination as $key => $item) {
                        echo "<article>";
                        echo "<div class=\"brz-articleLayout__info\">";

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
                            echo "<div class=\"brz-articleLayout__media_audio\">";
                            echo "<audio src=\"{$item['audiourl']}\" controls preload=\"none\"></audio>";
                            echo "</div>";
                        }

                        if ($show_title) {
                            echo "<h4 class=\"brz-articleLayout__heading\">";
                            if ($detail_url) {
                                echo "<a href=\"{$detail_url}?mc-slug={$item['slug']}\">";
                            }
                            echo "{$item['title']}";
                            if ($detail_url) {
                                echo "</a>";
                            }
                            echo "</h4>";
                        }

                        if ($show_date && $item['date']) {
                            echo "<h6 class=\"brz-ministryBrands__item brz-ministryBrands__item--meta-date\">";
                            if ($show_meta_headings) {
                                if ($show_meta_icons) {
                                    echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M96 32V64H48C21.5 64 0 85.5 0 112v48H448V112c0-26.5-21.5-48-48-48H352V32c0-17.7-14.3-32-32-32s-32 14.3-32 32V64H160V32c0-17.7-14.3-32-32-32S96 14.3 96 32zM448 192H0V464c0 26.5 21.5 48 48 48H400c26.5 0 48-21.5 48-48V192z\"></path></svg></span>";
                                } else {
                                    echo "Date: ";
                                }
                            }
                            echo "{$item['date']}";
                            echo "</h6>";
                        }
                        if ($show_category && $item['category']) {
                            echo "<h6 class=\"brz-ministryBrands__item brz-ministryBrands__item--meta-category\">";
                            if ($show_meta_headings) {
                                if ($show_meta_icons) {
                                    echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M0 48V487.7C0 501.1 10.9 512 24.3 512c5 0 9.9-1.5 14-4.4L192 400 345.7 507.6c4.1 2.9 9 4.4 14 4.4c13.4 0 24.3-10.9 24.3-24.3V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48z\"></path></svg></span>";
                                } else {
                                    echo "Category: ";
                                }
                            }
                            echo "{$item['category']}";
                            echo "</h6>";
                        }
                        if ($show_group && $item['group']) {
                            echo "<h6 class=\"brz-ministryBrands__item brz-ministryBrands__item--meta-group\">";
                            if ($show_meta_headings) {
                                if ($show_meta_icons) {
                                    echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 640 512\"><path fill=\"currentColor\" d=\"M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z\"></path></svg></span>";
                                } else {
                                    echo "Group: ";
                                }
                            }
                            echo "{$item['group']}";
                            echo "</h6>";
                        }
                        if ($show_series && $item['series']) {
                            echo "<h6 class=\"brz-ministryBrands__item brz-ministryBrands__item--meta-series\">";
                            if ($show_meta_headings) {
                                if ($show_meta_icons) {
                                    echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path d=\"M40 48C26.7 48 16 58.7 16 72v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V72c0-13.3-10.7-24-24-24H40zM192 64c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zM16 232v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V232c0-13.3-10.7-24-24-24H40c-13.3 0-24 10.7-24 24zM40 368c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24H88c13.3 0 24-10.7 24-24V392c0-13.3-10.7-24-24-24H40z\"/></svg></span>";
                                } else {
                                    echo "Series: ";
                                }
                            }
                            echo "{$item['series']}";
                            echo "</h6>";
                        }
                        if ($show_author && $item['author']) {
                            echo "<h6 class=\"brz-ministryBrands__item brz-ministryBrands__item--meta-author\">";
                            if ($show_meta_headings) {
                                if ($show_meta_icons) {
                                    echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path d=\"M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z\"/></svg></span>";
                                } else {
                                    echo "Author: ";
                                }
                            }
                            echo "{$item['author']}";
                            echo "</h6>";
                        }
                        if ($show_media_links) {
                            echo "<ul class=\"brz-articleLayout__media\">";
                            if ($item['videoplayer']) {
                                $item['videoplayer'] = preg_replace(
                                    '/<a(.+?)>.+?<\/a>/i',
                                    "<a$1><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\" class=\"brz-icon-svg\" data-type=\"fa\" data-name=\"desktop\"><path d=\"M528 0H48C21.5 0 0 21.5 0 48v320c0 26.5 21.5 48 48 48h192l-16 48h-72c-13.3 0-24 10.7-24 24s10.7 24 24 24h272c13.3 0 24-10.7 24-24s-10.7-24-24-24h-72l-16-48h192c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zm-16 352H64V64h448v288z\"></path></svg></a>",
                                    $item['videoplayer']
                                );
                                echo "<li class=\"brz-articleLayout__media_videoplayer\">{$item['videoplayer']}</li>";
                            }
                            if ($item['audioplayer']) {
                                $item['audioplayer'] = preg_replace(
                                    '/<a(.+?)>.+?<\/a>/i',
                                    "<a$1><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\" class=\"brz-icon-svg\" data-type=\"fa\" data-name=\"volume-up\"><path d=\"M215.03 71.05L126.06 160H24c-13.26 0-24 10.74-24 24v144c0 13.25 10.74 24 24 24h102.06l88.97 88.95c15.03 15.03 40.97 4.47 40.97-16.97V88.02c0-21.46-25.96-31.98-40.97-16.97zm233.32-51.08c-11.17-7.33-26.18-4.24-33.51 6.95-7.34 11.17-4.22 26.18 6.95 33.51 66.27 43.49 105.82 116.6 105.82 195.58 0 78.98-39.55 152.09-105.82 195.58-11.17 7.32-14.29 22.34-6.95 33.5 7.04 10.71 21.93 14.56 33.51 6.95C528.27 439.58 576 351.33 576 256S528.27 72.43 448.35 19.97zM480 256c0-63.53-32.06-121.94-85.77-156.24-11.19-7.14-26.03-3.82-33.12 7.46s-3.78 26.21 7.41 33.36C408.27 165.97 432 209.11 432 256s-23.73 90.03-63.48 115.42c-11.19 7.14-14.5 22.07-7.41 33.36 6.51 10.36 21.12 15.14 33.12 7.46C447.94 377.94 480 319.54 480 256zm-141.77-76.87c-11.58-6.33-26.19-2.16-32.61 9.45-6.39 11.61-2.16 26.2 9.45 32.61C327.98 228.28 336 241.63 336 256c0 14.38-8.02 27.72-20.92 34.81-11.61 6.41-15.84 21-9.45 32.61 6.43 11.66 21.05 15.8 32.61 9.45 28.23-15.55 45.77-45 45.77-76.88s-17.54-61.32-45.78-76.86z\"></path></svg></a>",
                                    $item['audioplayer']
                                );
                                echo "<li class=\"brz-articleLayout__media_audioplayer\">{$item['audioplayer']}</li>";
                            }
                            if ($item['notes']) {
                                echo "<li class=\"brz-articleLayout__media_notes\"><a href=\"{$item['notes']}\" target=\"_blank\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\" class=\"brz-icon-svg\" data-type=\"fa\" data-name=\"file-alt\"><path d=\"M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm64 236c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-64c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-72v8c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12zm96-114.1v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z\"></path></svg></a></li>";
                            }
                            echo "</ul>";
                        }
                        if ($show_preview && $item['preview']) {
                            echo '<p class="brz-articleLayout__preview">';
                            echo $this->excerpt($item['preview']);
                            echo '</p>';
                        }

                        if ($detail_url && $detail_page_button_text) {
                            echo "<p class=\"brz-articleLayout__detail_button\"><a href=\"{$detail_url}?mc-slug={$item['slug']}\" class=\"brz-articleLayout__button-link\"><span class=\"brz-articleLayout__button-text\">{$detail_page_button_text}</span></a></p>";
                        }

                        echo "</div>";
                        echo "</article>";
                    }
                    ?>
                </div>
                <?php
                if ($show_pagination) {
                    $paginationOutput = '<p id="brz-articleLayout__pagination" class="brz-articleLayout__pagination">'.$pagination->getLinks($_GET, 'mc-page').'</p>';

                    if (!empty($_GET['mc-search'])) {
                        $paginationOutput = str_replace('?', "?mc-search={$_GET['mc-search']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-search_term'])) {
                        $paginationOutput = str_replace('?', "?mc-search_term={$_GET['mc-search_term']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-group'])) {
                        $paginationOutput = str_replace('?', "?mc-group={$_GET['mc-group']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-category'])) {
                        $paginationOutput = str_replace('?', "?mc-category={$_GET['mc-category']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-category_add1'])) {
                        $paginationOutput = str_replace('?', "?mc-category_add1={$_GET['mc-category_add1']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-category_add2'])) {
                        $paginationOutput = str_replace('?', "?mc-category_add2={$_GET['mc-category_add2']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-category_add3'])) {
                        $paginationOutput = str_replace('?', "?mc-category_add3={$_GET['mc-category_add3']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-series'])) {
                        $paginationOutput = str_replace('?', "?mc-series={$_GET['mc-series']}&", $paginationOutput);
                    }

                    if (!empty($_GET['mc-author'])) {
                        $paginationOutput = str_replace('?', "?mc-author={$_GET['mc-author']}&", $paginationOutput);
                    }

                    echo $paginationOutput;
                }
            } else { ?>
                <p class="brz-articleLayout-no-results">There are no items available.</p>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * only searches for the expanded category filtering options.  all other options handled within api
     * also accounts for how the api reveals categories and category slugs with parent category.
     *
     * @param $items
     * @param $category
     * @return array
     */
    public function searchArray($items = [], $category = '')
    {
        $results = [];
        foreach ($items as $item) {
            $pieces        = explode(", ", $item["category"] ?? '');
            $categoriesArr = [];
            $count         = 1;

            foreach ($pieces as $piece) {
                $catcall         = "category{$count}slug";
                $categoriesArr[] = $item[$catcall];
                $count++;
            }

            if (in_array($category, $categoriesArr)) {
                $results[] = $item;
            }
        }

        return $results;
    }
}
