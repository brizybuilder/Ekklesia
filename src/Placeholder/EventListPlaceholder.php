<?php

namespace BrizyEkklesia\Placeholder;

use BrizyEkklesia\HelperTrait;
use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class EventListPlaceholder extends PlaceholderAbstract
{
    use HelperTrait;

    const NAME = 'ekk_event_list';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        $options = [
            'show_images'             => false,
            'show_title'              => false,
            'show_date'               => false,
            'show_category'           => false,
            'show_group'              => false,
            'show_meta_headings'      => false,
            'category'                => 'all',
            'group'                   => 'all',
            'features'                => '',
            'nonfeatures'             => '',
            'show_preview'            => false,
            'detail_page_button_text' => '',
            'detail_page'             => false,
            'howmany'                 => 9,
            'column_count'            => 3,
            'column_count_tablet'     => 2,
            'column_count_mobile'     => 1,
            'show_pagination'         => false,
            'show_location'           => false,
            'show_registration'       => false,
            'sticky_space'            => 0,
            'show_meta_icons'         => false,
            'date_format'             => 'g:i a'
        ];

        $settings = array_merge($options, $placeholder->getAttributes());

        extract($settings);

        $cms        = $this->monkCMS;
        $category   = $settings['category'] != 'all' ? $settings['category'] : '';
        $group      = $settings['group'] != 'all' ? $settings['group'] : '';
        $detail_url = $settings['detail_page'] ? $this->replacer->replacePlaceholders(urldecode($settings['detail_page']), $context) : false;
        $page       = isset($_GET['mc-page']) ? $_GET['mc-page'] : 1;

        if ($features) {
            $nonfeatures = '';
        } elseif ($nonfeatures) {
            $features = '';
        }

        $content = $cms->get([
            'module'        => 'event',
            'display'       => 'list',
            'order'         => 'recent',
            'emailencode'   => 'no',
            'howmany'       => $howmany,
            'page'          => $page,
            'find_category' => $category,
            'find_group'    => $group,
            'features'      => $features,
            'nonfeatures'   => $nonfeatures,
            'after_show'    => '__pagination__'
        ]);
?>


        <?php //output
        if (isset($content['show']) && count($content['show']) > 0) {
        ?>

            <div class="brz-eventList__container" data-columncount="<?php echo $column_count; ?>" data-columncount-tablet="<?php echo $column_count_tablet; ?>" data-columncount-mobile="<?php echo $column_count_mobile; ?>">
                <?php
                foreach ($content['show'] as $key => $item) {
                    //__id__-__eventstart format='Y-m-d'__-__slug__
                    $slugDate = date("Y-m-d", strtotime($item["eventstart"] ?? ''));
                    $slug = "{$item['id']}-$slugDate-{$item['slug']}";

                    echo "<div class=\"brz-eventList__item\">";
                    if ($show_images && $item['imageurl']) {
                        if ($detail_url) echo "<a href=\"{$detail_url}?mc-slug={$slug}\">";
                        echo "<div class=\"brz-ministryBrands__item--media\"><img src=\"{$item['imageurl']}\" alt=\"\" /></div>";
                        if ($detail_url) echo "</a>";
                    }

                    if ($show_title) {
                        echo "<h4 class=\"brz-eventList__item--meta--title brz-ministryBrands__item--meta-title\">";
                        if ($detail_url) echo "<a href=\"{$detail_url}?mc-slug={$slug}\">";
                        echo "{$item['title']}";
                        if ($detail_url) echo "</a>";
                        echo "</h4>";
                    }

                    if ($show_date) {
                        echo "<h5 class=\"brz-eventList__item--meta--date brz-ministryBrands__item--meta-date\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M96 32V64H48C21.5 64 0 85.5 0 112v48H448V112c0-26.5-21.5-48-48-48H352V32c0-17.7-14.3-32-32-32s-32 14.3-32 32V64H160V32c0-17.7-14.3-32-32-32S96 14.3 96 32zM448 192H0V464c0 26.5 21.5 48 48 48H400c26.5 0 48-21.5 48-48V192z\"></path></svg>
</span>";
                            else echo "<span>Date: </span>";
                        }
                        
                        $starttime = date($date_format, strtotime($item['eventstart'] ?? ''));
                        $endtime = date($date_format, strtotime($item['eventend'] ?? ''));
                        $frequency = $item['eventtimesremarks'];

                        echo "<span>{$frequency}, {$starttime} - {$endtime}</span>";
                        echo "</h5>";
                    }

                    if ($show_category && $item['category']) {
                        echo "<h6 class=\"brz-eventList__item--meta brz-ministryBrands__item--meta-category\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M0 48V487.7C0 501.1 10.9 512 24.3 512c5 0 9.9-1.5 14-4.4L192 400 345.7 507.6c4.1 2.9 9 4.4 14 4.4c13.4 0 24.3-10.9 24.3-24.3V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48z\"></path></svg>
</span>";
                            else echo "<span>Category: </span>";
                        }
                        echo "<span>{$item['category']}</span>";
                        echo "</h6>";
                    }
                    if ($show_group && $item['group']) {
                        echo "<h6 class=\"brz-eventList__item--meta brz-ministryBrands__item--meta-group\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 640 512\"><path fill=\"currentColor\" d=\"M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z\"></path></svg>
</span>";
                            else echo "<span>Group: </span>";
                        }
                        echo "<span>{$item['group']}</span>";
                        echo "</h6>";
                    }
                    if ($show_location && $item['location']) {
                        echo "<h6 class=\"brz-eventList__item--meta brz-ministryBrands__item--meta-location\">";
                        if($show_meta_headings) {
                            if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\"><path fill=\"currentColor\" d=\"M384 476.1L192 421.2V35.9L384 90.8V476.1zm32-1.2V88.4L543.1 37.5c15.8-6.3 32.9 5.3 32.9 22.3V394.6c0 9.8-6 18.6-15.1 22.3L416 474.8zM15.1 95.1L160 37.2V423.6L32.9 474.5C17.1 480.8 0 469.2 0 452.2V117.4c0-9.8 6-18.6 15.1-22.3z\"></path></svg>
</span>";
                            else echo "<span>Location: </span>";
                        }
                        echo "<span>{$item['location']}</span>";
                        echo "</h6>";


                        if ($item['fulladdress']) {
                            echo "<h6 class=\"brz-eventList__item--meta--link brz-ministryBrands__item--meta-address\">";
                            if($show_meta_headings) {
                                if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z\"></path></svg>
</span>";
                                else echo "<span class='brz-eventList__item--meta'>Address: </span>";
                            }
                            echo "<a href=\"http://maps.google.com/maps?q={$item["fulladdress"]}\" target=\"_blank\">{$item['fulladdress']}</a>";
                            echo "</h6>";
                        }
                    }
                    if ($show_registration && ($item['registrationurl'] || $item['externalregistrationurl'])) {
                        if ($item['registrationurl']) {
                            echo "<div class=\"brz-ministryBrands__item--meta--register-button\"><a href=\"{$item['registrationurl']}\" target=\"_blank\">Register</a></div>";
                        }
                        if ($item['externalregistrationurl']) {
                            echo "<div class=\"brz-ministryBrands__item--meta--register-button\"><a href=\"{$item['externalregistrationurl']}\" target=\"_blank\">Register</a></div>";
                        }
                    }
                    if ($show_preview && $item['preview']) {
                        echo '<div class="brz-eventList__item--meta--preview">';
                        echo $this->excerpt($item['preview']);
                        echo '</div>';
                    }
                    if ($detail_url && $detail_page_button_text) {
                        echo "<div class=\"brz-ministryBrands__item--meta--button\"><a href=\"{$detail_url}?mc-slug={$slug}\">{$detail_page_button_text}</a></div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
            <?php
            if ($show_pagination && $content['after_show']['pagination']) {
                $content['after_show']['pagination'] = str_replace('id="pagination"', 'id="ekklesia360_event_list_pagination" class="brz-ministryBrands__pagination"', $content['after_show']['pagination']);
                $content['after_show']['pagination'] = str_replace('page=', 'mc-page=', $content['after_show']['pagination']);

                echo $content['after_show']['pagination'];
            }
        } //no output
        else {
            ?>

            <p>There are no events available.</p>

        <?php
        }
        ?>
<?php
    }
}
