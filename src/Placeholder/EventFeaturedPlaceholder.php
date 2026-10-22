<?php

namespace BrizyEkklesia\Placeholder;

use BrizyEkklesia\HelperTrait;
use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class EventFeaturedPlaceholder extends PlaceholderAbstract
{
    use HelperTrait;

    const NAME = 'ekk_event_featured';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        $options = [
            'show_image'              => false,
            'show_title'              => false,
            'show_date'               => false,
            'show_category'           => false,
            'show_group'              => false,
            'show_meta_headings'      => false,
            'show_location'           => false,
            'show_room'               => false,
            'show_coordinator'        => false,
            'show_coordinator_email'  => false,
            'show_coordinator_phone'  => false,
            'show_cost'               => false,
            'show_website'            => false,
            'show_registration'       => false,
            'show_description'        => false,
            'show_preview'            => false,
            'show_latest_events'      => false,
            'recentEvents'            => '',
            'event_slug'              => false,
            'category'                => 'all',
            'group'                   => 'all',
            'features'                => '',
            'nonfeatures'             => '',
            'detail_page_button_text' => false,
            'detail_page'             => false,
            'show_meta_icons'         => false,
            'date_format'             => 'g:i a'
        ];

        $settings = array_merge($options, $placeholder->getAttributes());

        extract($settings);

        $cms          = $this->monkCMS;
        $recentEvents = $settings['recentEvents'] != '' ? $settings['recentEvents'] : '';
        $category     = $settings['category'] != 'all' ? $settings['category'] : '';
        $group        = $settings['group'] != 'all' ? $settings['group'] : '';
        $detail_url   = $detail_page ? $this->replacer->replacePlaceholders(urldecode($detail_page), $context) : false;
        $slugLink     = false;
        $slug         = false;

        if ($features) {
            $nonfeatures = '';
        } elseif ($nonfeatures) {
            $features = '';
        }

        //make content
        if ($show_latest_events) {
            $content1 = $cms->get([
                'module'        => 'event',
                'display'       => 'list',
                'order'         => 'recent',
                'howmany'       => 1,
                'find_category' => $category,
                'find_group'    => $group,
                'features'      => $features,
                'nonfeatures'   => $nonfeatures,
                'emailencode'   => 'no',
            ]);

            $content  = empty($content1['show'][0]) ? [] : $content1['show'][0];
            $slugLink = empty($content['slug']) ? '' : $content['slug'];

        } else {
            if ($event_slug) {
                $slug = $event_slug;
                $slugLink = $slug;
            } elseif ($recentEvents != '') {
                $slug = $recentEvents;
                $slugLink = $slug;
            }
        }

        if ($slug) {
            $content = $cms->get([
                'module'      => 'event',
                'display'     => 'detail',
                'find'        => $slug,
                'emailencode' => 'no',
            ])['show'];
        }
?>


        <?php //output
        if (isset($content) && count($content) > 0) {
            $item = $content;
        ?>
            <?php
            echo "<div class=\"brz-eventFeatured__item\">";
            if ($show_image && $item['imageurl']) {
                if ($detail_url) echo "<a href=\"{$detail_url}?mc-slug={$slugLink}\">";
                echo "<div class=\"brz-ministryBrands__item--media\"><img src=\"{$item['imageurl']}\" alt=\"\" /></div>";
                if ($detail_url) echo "</a>";
            }
            if ($show_title) {
                echo "<h2 class=\"brz-eventFeatured__item--meta--title brz-ministryBrands__item--meta-title\">";
                if ($detail_url) echo "<a href=\"{$detail_url}?mc-slug={$slugLink}\">";
                echo "{$item['title']}";
                if ($detail_url) echo "</a>";
                echo "</h4>";
            }
            if ($show_date) {
                $starttime = date($date_format, strtotime($item['eventstart'] ?? ''));
                $endtime = date($date_format, strtotime($item['eventend'] ?? ''));
                $frequency = $item['eventtimesremarks'];

                echo "<h5 class=\"brz-eventFeatured__item--meta--date brz-ministryBrands__item--meta-date\">{$frequency}, {$starttime} - {$endtime}</h5>";
            }
            if ($show_category && $item['category']) {
                echo "<h6 class=\"brz-eventFeatured__item--meta brz-ministryBrands__item--meta-category\">";
                if($show_meta_headings) {
                    if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M0 48V487.7C0 501.1 10.9 512 24.3 512c5 0 9.9-1.5 14-4.4L192 400 345.7 507.6c4.1 2.9 9 4.4 14 4.4c13.4 0 24.3-10.9 24.3-24.3V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48z\"></path></svg>
</span>";
                    else echo "<span>Category: </span>";
                }
                echo "<span>{$item['category']}</span>";
                echo "</h6>";
            }
            if ($show_group && $item['group']) {
                echo "<h6 class=\"brz-eventFeatured__item--meta brz-ministryBrands__item--meta-group\">";
                if($show_meta_headings) {
                    if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 640 512\"><path fill=\"currentColor\" d=\"M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z\"></path></svg>
</span>";
                    else echo "<span>Group: </span>";
                }
                echo "<span>{$item['group']}</span>";
                echo "</h6>";
            }

            if ($show_location && $item['location']) {
                echo "<h6 class=\"brz-eventFeatured__item--meta brz-ministryBrands__item--meta-location\">";
                if($show_meta_headings) {
                    if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\"><path fill=\"currentColor\" d=\"M384 476.1L192 421.2V35.9L384 90.8V476.1zm32-1.2V88.4L543.1 37.5c15.8-6.3 32.9 5.3 32.9 22.3V394.6c0 9.8-6 18.6-15.1 22.3L416 474.8zM15.1 95.1L160 37.2V423.6L32.9 474.5C17.1 480.8 0 469.2 0 452.2V117.4c0-9.8 6-18.6 15.1-22.3z\"></path></svg>
</span>";
                    else echo "<span>Location: </span>";
                }
                echo "<span>{$item['location']}</span>";
                echo "</h6>";

                if ($item['fulladdress']) {
                    echo "<h6 class=\"brz-eventFeatured__item--meta--link brz-ministryBrands__item--meta-address\">";
                    if($show_meta_headings) {
                        if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z\"></path></svg>
</span>";
                        else  echo "<span class='brz-eventFeatured__item--meta'>Address: </span>";
                    }
                    echo "<a href=\"http://maps.google.com/maps?q={$item["fulladdress"]}\" target=\"_blank\">{$item['fulladdress']}</a>";
                    echo "</h6>";
                }
            }
            if ($show_room && $item['room']) {
                echo "<h6 class=\"brz-eventFeatured__item--meta brz-ministryBrands__item--meta-room\">";
                if($show_meta_headings) {
                    if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M227.7 11.7c15.6-15.6 40.9-15.6 56.6 0l216 216c15.6 15.6 15.6 40.9 0 56.6l-216 216c-15.6 15.6-40.9 15.6-56.6 0l-216-216c-15.6-15.6-15.6-40.9 0-56.6l216-216zm87.6 137c-4.6-4.6-11.5-5.9-17.4-3.5s-9.9 8.3-9.9 14.8v56H224c-35.3 0-64 28.7-64 64v48c0 13.3 10.7 24 24 24s24-10.7 24-24V280c0-8.8 7.2-16 16-16h64v56c0 6.5 3.9 12.3 9.9 14.8s12.9 1.1 17.4-3.5l80-80c6.2-6.2 6.2-16.4 0-22.6l-80-80z\"></path></svg>
</span>";
                    else echo "<span>Room: </span>";
                }
                echo "<span>{$item['room']}</span>";
                echo "</h6>";
            }
            if ($show_coordinator && $item['coordname']) {
                echo "<h6 class=\"brz-eventFeatured__item--meta brz-ministryBrands__item--meta-coordinator\">";
                if($show_meta_headings) {
                    if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z\"></path></svg>
</span>";
                    else echo "<span>Coordinator: </span>";
                }
                echo "{$item['coordname']}";
                echo "</h6>";
                if ($show_coordinator_email && $item['coordemail']) {
                    echo "<h6 class=\"brz-eventFeatured__item--meta--link brz-ministryBrands__item--meta-coordinatorEmail\">";
                    if($show_meta_headings) {
                        if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z\"></path></svg>
</span>";
                        else echo "<span class='brz-eventFeatured__item--meta'>Coordinator Email: </span>";
                    }
                    echo "<a href=\"mailto:{$item['coordemail']}\">{$item['coordemail']}</a>";
                    echo "</h6>";
                }
                if ($show_coordinator_phone && $item['coordphone']) {
                    echo "<h6 class=\"brz-eventFeatured__item--meta brz-ministryBrands__item--meta-coordinatorPhone\">";
                    if($show_meta_headings) {
                        if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M16 64C16 28.7 44.7 0 80 0H304c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H80c-35.3 0-64-28.7-64-64V64zM224 448a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM304 64H80V384H304V64z\"></path></svg>
</span>";
                        else echo "<span>Coordinator Phone: </span>";
                    }
                    echo "{$item['coordphone']}";
                    echo "</h6>";
                }
            }
            if ($show_cost && $item['cost']) {
                echo "<h6 class=\"brz-eventFeatured__item--meta brz-ministryBrands__item--meta-cost\">";
                if($show_meta_headings) {
                    if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\"><path fill=\"currentColor\" d=\"M64 64C28.7 64 0 92.7 0 128v64c0 8.8 7.4 15.7 15.7 18.6C34.5 217.1 48 235 48 256s-13.5 38.9-32.3 45.4C7.4 304.3 0 311.2 0 320v64c0 35.3 28.7 64 64 64H512c35.3 0 64-28.7 64-64V320c0-8.8-7.4-15.7-15.7-18.6C541.5 294.9 528 277 528 256s13.5-38.9 32.3-45.4c8.3-2.9 15.7-9.8 15.7-18.6V128c0-35.3-28.7-64-64-64H64zm64 112l0 160c0 8.8 7.2 16 16 16H432c8.8 0 16-7.2 16-16V176c0-8.8-7.2-16-16-16H144c-8.8 0-16 7.2-16 16zM96 160c0-17.7 14.3-32 32-32H448c17.7 0 32 14.3 32 32V352c0 17.7-14.3 32-32 32H128c-17.7 0-32-14.3-32-32V160z\"></path></svg>
</span>";
                    else echo "<span>Cost: </span>";
                }
                echo "<span>{$item['cost']}</span>";
                echo "</h6>";
            }
            if ($show_website && $item['website']) {
                echo "<h6 class=\"brz-eventFeatured__item--meta--link brz-ministryBrands__item--meta-website\">";
                if($show_meta_headings) {
                    if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 20.9 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z\"></path></svg>
</span>";
                    else echo "<span class='brz-eventFeatured__item--meta'>Website: </span>";
                }
                echo "<a href=\"{$item['website']}\">{$item['website']}</a>";
                echo "</h6>";
            }
            if ($show_registration && $item['registrationurl']) {
                echo "<div class=\"brz-ministryBrands__item--meta--register-button\"><a href=\"{$item['registrationurl']}\" target=\"_blank\">Register</a></div>";
            }
            if ($show_registration && $item['externalregistrationurl']) {
                echo "<div class=\"brz-ministryBrands__item--meta--register-button\"><a href=\"{$item['externalregistrationurl']}\" target=\"_blank\">Register</a></div>";
            }
            if ($show_preview && $item['preview']) {
                echo '<p class="brz-eventFeatured__item--meta--preview"><span>';
                echo $this->excerpt($item['preview']);
                echo '</span></p>';
            }
            if ($show_description && $item['text']) {
                echo "<div class=\"brz-eventFeatured__item--meta--preview\">{$item['text']}</div>";
            }

            if ($detail_url && $detail_page_button_text) {
                echo "<p class=\"brz-ministryBrands__item--meta--button\"><a href=\"{$detail_url}?mc-slug={$slugLink}\" class=\"brz-button-link brz-button brz-size-sm\"><span class=\"brz-button-text\">{$detail_page_button_text}</span></a></p>";
            }

            echo "</div>";
            ?>
        <?php
        } //no output
        else {
        ?>
            <p>There is no event available.</p>

        <?php
        }
        ?>
<?php
    }
}
