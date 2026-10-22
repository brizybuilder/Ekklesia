<?php

namespace BrizyEkklesia\Placeholder;

use BrizyEkklesia\HelperTrait;
use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class GroupLayoutPlaceholder extends PlaceholderAbstract
{
    use HelperTrait;

    const NAME = 'ekk_group_layout';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        $options = [
            'show_category'                   => true,
            'parent_category'                 => '',
            'show_category_filter'            => true,
            'category_filter_parent'          => true,
            'category_filter_heading'         => 'Category',
            'show_category_filter_add1'       => false,
            'category_filter_parent_add1'     => '',
            'category_filter_heading_add1'    => 'Category',
            'show_category_filter_add2'       => false,
            'category_filter_parent_add2'     => '',
            'category_filter_heading_add2'    => 'Category',
            'show_category_filter_add3'       => true,
            'category_filter_parent_add3'     => '',
            'category_filter_heading_add3'    => 'Category',
            'show_group_filter'               => true,
            'group_filter_heading'            => 'Group',
            'show_search'                     => true,
            'search_placeholder'              => 'Search',
            'show_pagination'                 => true,
            'howmany'                         => 9,
            'column_count'                    => 3,
            'column_count_tablet'             => 2,
            'column_count_mobile'             => 1,
            'show_images'                     => true,
            'show_day'                        => true,
            'show_times'                      => true,
            'show_group'                      => true,
            'show_status'                     => true,
            'show_childcare'                  => true,
            'show_resourcelink'               => true,
            'show_preview'                    => true,
            'detail_page_button_text'         => false,
            'sticky_space'                    => 0,
            'detail_page'                     => false,
            'show_meta_icons'                 => false,
            'date_format'                     => 'g:i a',
            'group_slug'                      => ''
        ];

        $settings = array_merge($options, $placeholder->getAttributes());

        extract($settings);

        $cms            = $this->monkCMS;
        $detail_url     = $settings['detail_page'] ? $this->replacer->replacePlaceholders(urldecode($settings['detail_page']), $context) : false;
        $page           = isset($_GET['mc-page']) ? $_GET['mc-page'] : 1;
        $baseURL        = strtok($_SERVER["REQUEST_URI"], '?') !== FALSE ? strtok($_SERVER["REQUEST_URI"], '?') : $_SERVER["REQUEST_URI"];
        $filterCountArr = [$show_category_filter, $show_category_filter_add1, $show_category_filter_add2, $show_category_filter_add3, $show_group_filter];
        $filterCount    = count(array_filter($filterCountArr));
        $requestGroup    = $_GET['mc-group'] ?? false;
        $categories     = $cms->get([
            'module'     => 'smallgroup',
            'display'    => 'categories',
            'find_group' => $group_slug ?: $requestGroup
        ]);
        $categories_parent = $cms->get([
            'module'          => 'smallgroup',
            'display'         => 'categories',
            'parent_category' => $parent_category,
        ]);
        $groups = $cms->get([
            'module'  => 'smallgroup',
            'display' => 'list',
            'groupby' => 'group'
        ]);

        if (!isset($groups['group_show'])) {
            $groups['group_show'] = [];
        }

        if (isset($_GET['mc-search'])) {
            //search is not allowing page so no pagination
            $content = [];
            $search_arr = $cms->get([
                'module'        => 'search',
                'display'       => 'results',
                'howmany'       => '100',
                'find_category' => $parent_category,
                'keywords'      => $_GET['mc-search'],
                'find_module'   => 'smallgroup',
                'hide_module'   => 'media',
            ]);

            $_search = isset($search_arr['show']) ? $search_arr['show'] : [];
            foreach ($_search as $search) {
                $item = $cms->get([
                    'module'      => 'smallgroup',
                    'display'     => 'detail',
                    'emailencode' => 'no',
                    'show'        => "__endtime format='g:ia'__",
                    'find'        => $search['slug'],
                ]);
                $content['show'][] = $item['show'];
            }

        } else {
            $content = $cms->get([
                'module'        => 'smallgroup',
                'display'       => 'list',
                'order'         => 'recent',
                'emailencode'   => 'no',
                'howmany'       => '100',
                'find_category' => $parent_category,
                'find_group'    => $group_slug ?: $requestGroup,
                'show'          => "__endtime format='g:ia'__",
            ]);
            //filter categories separately since there can be more than 1 category filter
            if (!empty($_GET["mc-category"])) {
                $catArr = [];
                if (!empty($categories["show"]) && !empty($content["show"])) {
                    foreach ($categories["show"] as $key => $val) {
                        if ($val["slug"] == $_GET["mc-category"]) {
                            $catArr[] = $val["name"];
                        }
                    }

                    $content["show"] = self::searchArray($content["show"], $catArr);
                }
            }
            if (!empty($_GET["mc-category-1"])) {
                $cat1Arr = [];
                foreach ($categories["show"] as $key => $val) {
                    if ($val["slug"] == $_GET["mc-category-1"]) {
                        $cat1Arr[] = $val["name"];
                    }
                }
                $content["show"] = self::searchArray($content["show"], $cat1Arr);
            }
            if (!empty($_GET["mc-category-2"])) {
                $cat2Arr = [];
                foreach ($categories["show"] as $key => $val) {
                    if ($val["slug"] == $_GET["mc-category-2"]) {
                        $cat2Arr[] = $val["name"];
                    }
                }
                $content["show"] = self::searchArray($content["show"], $cat2Arr);
            }
            if (!empty($_GET["mc-category-3"])) {
                $cat3Arr = [];
                foreach ($categories["show"] as $key => $val) {
                    if ($val["slug"] == $_GET["mc-category-3"]) {
                        $cat3Arr[] = $val["name"];
                    }
                }
                $content["show"] = self::searchArray($content["show"], $cat3Arr);
            }
        }

?>
        <div id="brz-groupLayout__filters" class="brz-groupLayout__filters">
            <form id="brz-groupLayout__filters--form" name="brz-groupLayout__filters--form" class="brz-groupLayout__filters--form" action="<?= $baseURL ?>" data-count="<?= $filterCount ?>">

                <?php if ($show_group_filter && !$group_slug): ?>
                    <div class="brz-groupLayout__filters--form-selectWrapper">
                        <select name="mc-group" class='sorter'>
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

                <?php
                if ($show_category_filter): ?>
                    <div class="brz-groupLayout__filters--form-selectWrapper">
                        <select name="mc-category" class='sorter'>
                            <option value=""><?= $category_filter_heading ?></option>
                            <option value="">All</option>
                            <?php
                            //since this is the main category filter this will always show the
                            if ($category_filter_parent) {
                                if (!empty($categories["level3"])) {
                                    foreach ($categories["level3"] as $category) {
                                        if ($category["parentid"] != $category_filter_parent) {
                                            continue;
                                        }
                                        echo "<option value=\"{$category['slug']}\"";
                                        if (isset($_GET['mc-category']) && $_GET['mc-category'] == $category['slug']) {
                                            echo " selected";
                                        }
                                        echo ">{$category['name']}</option>";
                                    }
                                }
                            } else {
                                if ($parent_category != "" && !empty($categories_parent["level1"])) {
                                    foreach ($categories_parent["level1"] as $category) {
                                        echo "<option value=\"{$category['slug']}\"";
                                        if (isset($_GET['mc-category']) && $_GET['mc-category'] == $category['slug']) {
                                            echo " selected";
                                        }
                                        echo ">{$category['name']}</option>";
                                    }
                                } else {
                                    if (!empty($categories["show"])) {
                                        foreach ($categories["show"] as $category) {
                                            echo "<option value=\"{$category['slug']}\"";
                                            if (isset($_GET['mc-category']) && $_GET['mc-category'] == $category['slug']) {
                                                echo " selected";
                                            }
                                            echo ">{$category['name']}</option>";
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php
                if ($show_category_filter_add1 && $category_filter_parent_add1 != ""): ?>
                    <div class="brz-groupLayout__filters--form-selectWrapper">
                        <select name="mc-category-1" class='sorter'>
                            <option value=""><?= $category_filter_heading_add1 ?></option>
                            <option value="">All</option>
                            <?php
                            foreach ($categories["level3"] as $category) {
                                if ($category["parentid"] != $category_filter_parent_add1) {
                                    continue;
                                }
                                echo "<option value=\"{$category['slug']}\"";
                                if (isset($_GET['mc-category-1']) && $_GET['mc-category-1'] == $category['slug']) {
                                    echo " selected";
                                }
                                echo ">{$category['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php
                if ($show_category_filter_add2 && $category_filter_parent_add2 != ""): ?>
                    <div class="brz-groupLayout__filters--form-selectWrapper">
                        <select name="mc-category-2" class='sorter'>
                            <option value=""><?= $category_filter_heading_add2 ?></option>
                            <option value="">All</option>
                            <?php
                            foreach ($categories["level3"] as $category) {
                                if ($category["parentid"] != $category_filter_parent_add2) {
                                    continue;
                                }
                                echo "<option value=\"{$category['slug']}\"";
                                if (isset($_GET['mc-category-2']) && $_GET['mc-category-2'] == $category['slug']) {
                                    echo " selected";
                                }
                                echo ">{$category['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php
                if ($show_category_filter_add3 && $category_filter_parent_add3 != ""): ?>
                    <div class="brz-groupLayout__filters--form-selectWrapper">
                        <select name="mc-category-3" class='sorter'>
                            <option value=""><?= $category_filter_heading_add3 ?></option>
                            <option value="">All</option>
                            <?php
                            foreach ($categories["level3"] as $category) {
                                if ($category["parentid"] != $category_filter_parent_add3) {
                                    continue;
                                }
                                echo "<option value=\"{$category['slug']}\"";
                                if (isset($_GET['mc-category-3']) && $_GET['mc-category-3'] == $category['slug']) {
                                    echo " selected";
                                }
                                echo ">{$category['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>
            </form>

            <?php if ($show_search): ?>
                <form method="get" id="brz-groupLayout__filters--form-search" name="search" class="brz-groupLayout__filters--form-search" action="<?= $baseURL ?>" data-count="<?= $filterCount ?>">
                    <fieldset>
                        <input type="text" id="brz-groupLayout__filters--form-search_term" name="mc-search" value="" placeholder="<?= $search_placeholder ?>"/>
                        <button type="submit" id="brz-groupLayout__filters--form-search_submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="brz-icon-svg align-[initial]" data-type="fa" data-name="search"><path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path></svg></button>
                    </fieldset>
                </form>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['mc-search'])) {
            echo "<h5 class=\"brz-groupLayout-results-heading\"><a href=\"{$baseURL}\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 352 512\" class=\"brz-icon-svg align-[initial]\" data-type=\"fa\" data-name=\"times\"><path d=\"M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z\"></path></svg></a> Search results for \"{$_GET['mc-search']}\"</h5>";
        }
        ?>

        <div id="brz-groupLayout__container" class="brz-groupLayout__container">

            <?php
            //setup pagination
            $_content  = isset($content["show"]) ? $content["show"] : [];
            $pagination = new CustomPagination($_content , (isset($page) ? $page : 1), $howmany);
            $pagination->setShowFirstAndLast(true);
            $resultsPagination = $pagination->getResults();
            //output
            if (count($resultsPagination) > 0) {
            ?>

<div class="brz-groupLayout__content" data-columncount="<?php echo $column_count; ?>"
                     data-columncount-tablet="<?php echo $column_count_tablet; ?>"
                     data-columncount-mobile="<?php echo $column_count_mobile; ?>">
                    <?php
                    foreach ($resultsPagination as $key => $item) {
                        //remove 12am
                        if (strtolower($item['starttime'] ?? '') == "12 am" || strtolower($item['starttime'] ?? '') == "12:00am") {
                            $item['starttime'] = false;
                        }
                        if (strtolower($item['endtime'] ?? '') == "12 am" || strtolower($item['endtime'] ?? '') == "12:00am") {
                            $item['endtime'] = false;
                        }
                        //remove site group
                        $item['group'] = str_replace("Site Group, ", "", $item['group'] ?? '');
                        $item['group'] = str_replace("Site Group", "", $item['group'] ?? '');

                        echo "<div class=\"brz-groupLayout--item\">";
                        echo "<div class=\"brz-groupLayout--item__content\">";
                        if ($show_images && $item['imageurl']) {
                            if ($detail_url) echo "<a href=\"{$detail_url}?mc-slug={$item['slug']}\">";
                            echo "<div class=\"brz-ministryBrands__item--media\"><img src=\"{$item['imageurl']}\" alt=\"\" /></div>";
                            if ($detail_url) echo "</a>";
                        }

                        echo "<h4 class=\"brz-groupLayout--item__content-heading brz-ministryBrands__item--meta-title\">";
                        if ($detail_url) echo "<a href=\"{$detail_url}?mc-slug={$item['slug']}\">";
                        echo "{$item['name']}";
                        if ($detail_url) echo "</a>";
                        echo "</h4>";

                        if ($show_day && $item['dayoftheweek']) {
                            echo "<h6 class=\"brz-groupLayout--item__content-meta brz-ministryBrands__item--meta-day\">";
                            if ($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M96 32V64H48C21.5 64 0 85.5 0 112v48H448V112c0-26.5-21.5-48-48-48H352V32c0-17.7-14.3-32-32-32s-32 14.3-32 32V64H160V32c0-17.7-14.3-32-32-32S96 14.3 96 32zM448 192H0V464c0 26.5 21.5 48 48 48H400c26.5 0 48-21.5 48-48V192z\"></path></svg>
</span>";
                            else echo "<span>Meeting Day: </span>";
                            echo "<span>{$item['dayoftheweek']}</span>";
                            echo "</h6>";
                        }
                        if ($show_times && ($item['starttime'] || $item['endtime'])) {
                            echo "<h6 class=\"brz-groupLayout--item__content-meta brz-ministryBrands__item--meta-times\">";
                            if ($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z\"></path></svg>
</span>";
                            else echo "<span>Meeting Time: </span>";
                            if ($item['starttime']) echo date($date_format, strtotime($item['starttime']));
                            if ($item['endtime']) echo " - " . date($date_format, strtotime($item['endtime']));
                            echo "</h6>";
                        }
                        if ($show_category && $item['category']) {
                            echo "<h6 class=\"brz-groupLayout--item__content-meta brz-ministryBrands__item--meta-category\">";
                            if ($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M0 48V487.7C0 501.1 10.9 512 24.3 512c5 0 9.9-1.5 14-4.4L192 400 345.7 507.6c4.1 2.9 9 4.4 14 4.4c13.4 0 24.3-10.9 24.3-24.3V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48z\"></path></svg>
</span>";
                            else echo "<span>Category: </span>";
                            echo "<span>{$item['category']}</span>";
                            echo "</h6>";
                           }
                        if ($show_group && $item['group']) {
                            echo "<h6 class=\"brz-groupLayout--item__content-meta brz-ministryBrands__item--meta-group\">";
                            if ($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 640 512\"><path fill=\"currentColor\" d=\"M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z\"></path></svg>
</span>";
                            else echo "<span>Group: </span>";
                            echo "<span>{$item['group']}</span>";
                            echo "</h6>";
                            }
                        if ($show_status && $item['groupstatus']) {
                            echo "<h6 class=\"brz-groupLayout--item__content-meta brz-ministryBrands__item--meta-status\">";
                            if ($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M96 0c17.7 0 32 14.3 32 32V64l352 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-352 0V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V128H32C14.3 128 0 113.7 0 96S14.3 64 32 64H64V32C64 14.3 78.3 0 96 0zm96 160H448c17.7 0 32 14.3 32 32V352c0 17.7-14.3 32-32 32H192c-17.7 0-32-14.3-32-32V192c0-17.7 14.3-32 32-32z\"></path></svg>
</span>";
                            else echo "<span>Status: </span>";
                            echo "<span>{$item['groupstatus']}</span>";
                            echo "</h6>";
                        }
                        if ($show_childcare) {
                            $childcare = "No";
                            if ($item['childcareprovided']) {
                                $childcare = "Yes";
                            }
                            echo "<h6 class=\"brz-groupLayout--item__content-meta brz-ministryBrands__item--meta-childcare\">";
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M256 192H.1C2.7 117.9 41.3 52.9 99 14.1c13.3-8.9 30.8-4.3 39.9 8.8L256 192zm128-32c0-35.3 28.7-64 64-64h32c17.7 0 32 14.3 32 32s-14.3 32-32 32l-32 0v64c0 25.2-5.8 50.2-17 73.5s-27.8 44.5-48.6 62.3s-45.5 32-72.7 41.6S253.4 416 224 416s-58.5-5-85.7-14.6s-51.9-23.8-72.7-41.6s-37.3-39-48.6-62.3S0 249.2 0 224l224 0 160 0V160zM80 416a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm240 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0z\"></path></svg>
</span>";
                            else echo "<span>Childcare Provided: </span>";
                            echo "<span>{$childcare}</span>";
                            echo "</h6>";
                        }
                        if ($show_resourcelink && $item['resourcelink']) {
                            $resource_target = "";
                            if ($item['iflinknewwindow']) {
                                $resource_target = " target=\"_blank\"";
                            }
                            echo "<h6 class=\"brz-groupLayout--item__content-meta brz-ministryBrands__item--meta-resourceLink\">";
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM216 232V334.1l31-31c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-72 72c-9.4 9.4-24.6 9.4-33.9 0l-72-72c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l31 31V232c0-13.3 10.7-24 24-24s24 10.7 24 24z\"></path></svg>
</span>";
                            else echo "<span class='brz-groupList__item--meta'>Resource: </span>";
                            echo "<a href=\"{$item['resourcelink']}\" {$resource_target}>{$item['resourcelink']}</a>";
                            echo "</h6>";
                        }

                        if ($show_preview && $item['description']) {
                            echo '<p class="brz-groupLayout--item__content-preview">';
                            echo $this->excerpt($item['description'], '<span class=\"brz-groupLayout--item__content-preview--more\">...</span>');
                            echo '</p>';
                        }

                        if ($detail_url && $detail_page_button_text) {
                            echo "<p class=\"brz-groupLayout--item__content-detailButton\"><a href=\"{$detail_url}?mc-slug={$item['slug']}\" class=\"brz-button-link brz-button brz-size-sm\"><span class=\"brz-button-text\">{$detail_page_button_text}</span></a></p>";
                        }
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
                <?php
                if ($show_pagination) {

                    $paginationOutput = '<p id="brz-groupLayout__pagination" class="brz-groupLayout__pagination">' . $pagination->getLinks($_GET, 'mc-page') . '</p>';

                    //if complexity grows consider http_build_query
                    if (isset($_GET['mc-search'])) {
                        $paginationOutput = str_replace('?', "?mc-search={$_GET['mc-search']}&", $paginationOutput);
                    }

                    //add group
                    if (isset($_GET['mc-group'])) {
                        $paginationOutput = str_replace('?', "?mc-group={$_GET['mc-group']}&", $paginationOutput);
                    }
                    //add category
                    if (isset($_GET['mc-category'])) {
                        $paginationOutput = str_replace('?', "?mc-category={$_GET['mc-category']}&", $paginationOutput);
                    }
                    //add1 category
                    if (isset($_GET['mc-category-1'])) {
                        $paginationOutput = str_replace('?', "?mc-category-1={$_GET['mc-category-1']}&", $paginationOutput);
                    }
                    //add2 category
                    if (isset($_GET['mc-category-2'])) {
                        $paginationOutput = str_replace('?', "?mc-category-2={$_GET['mc-category-2']}&", $paginationOutput);
                    }
                    //add3 category
                    if (isset($_GET['mc-category-3'])) {
                        $paginationOutput = str_replace('?', "?mc-category-3={$_GET['mc-category-3']}&", $paginationOutput);
                    }
                    echo $paginationOutput;
                }
            } //no output
            else {
                ?>

                <p class="brz-groupLayout-no-results">There are no groups available.</p>

            <?php
            }
            ?>
        </div>
<?php
    }

    /**
     * only searches for the expanded category filtering options.  all other options handled within api
     *
     * @param $groups
     * @param $categories
     * @return array
     */
    private function searchArray($groups = [], $categories = [])
    {
        $results = [];
        foreach ($groups as $group) {
            $pieces  = explode(", ", $group["category"] ?? '');
            $matches = array_intersect($pieces, $categories);

            if (count($matches) > 0) {
                $results[] = $group;
            }
        }

        return $results;
    }
}