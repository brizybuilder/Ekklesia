<?php

namespace BrizyEkklesia\Placeholder;

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;
use DateInterval;
use DatePeriod;
use DateTime;

class EventCalendarPlaceholder extends PlaceholderAbstract
{
    const NAME = 'ekk_event_calendar';

    private $time = false;
    private $timeFormat = '24';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        $options = [
            'category'                      => 'all',
            'group'                         => 'all',
            'features'                      => '',
            'nonfeatures'                   => '',
            'detail_page'                   => false,
            'howmanymonths'                 => 3,
            'time'                          => false,
            'time_format'                   => '24',
            'showSubscribeToCalendarButton' => false,
        ];

        $subscribeToCalendarButton = $placeholder->getContent();
        $settings                  = array_merge($options, $placeholder->getAttributes());

        extract($settings);

        $cms           = $this->monkCMS;
        $category      = $settings['category'] != 'all' ? $settings['category'] : '';
        $group         = $settings['group'] != 'all' ? $settings['group'] : '';
        $detail_url    = $settings['detail_page'] ? $this->replacer->replacePlaceholders(
            urldecode($settings['detail_page']),
            $context
        ) : false;
        $calendarStart = date('Y-m-d');
        $calendarEnd   = date('Y-m-d', strtotime("+{$howmanymonths} months"));
        $date1         = new DateTime($calendarStart);
        $date2         = new DateTime($calendarEnd);
        $diff          = $date1->diff($date2, true);
        $calendarDays  = $diff->format('%a');
        $this->time       = $time;
        $this->timeFormat = isset($settings['time_format']) ? $settings['time_format'] : '24';

        if ($features) {
            $nonfeatures = '';
        } elseif ($nonfeatures) {
            $features = '';
        }

        $content = $cms->get([
            'module'        => 'event',
            'display'       => 'list',
            'emailencode'   => 'no',
            'recurring'     => 'yes',
            'repeatevent'   => 'yes',
            'groupby'       => 'day',
            'howmanydays'   => $calendarDays,
            'find_category' => $category,
            'find_group'    => $group,
            'features'      => $features,
            'nonfeatures'   => $nonfeatures,
        ]);

        if (isset($_GET['mc-subscribe']) && !empty($content['show'])) {
            if (!is_numeric($_GET['mc-subscribe'])) {
                EventDetailPlaceholder::sendCalendarIcs($content['show']);
            }
        }

        ?>

        <div class="brz-eventCalendar_wrap">

            <?php
            if (!empty($content['show'])) {
                foreach ($content["show"] as $show) {
                    $grouping_month                           = date("Y-m", strtotime($show["eventstart"] ?? ''));
                    $grouping_day                             = date("Y-m-d", strtotime($show["eventstart"] ?? ''));
                    $events[$grouping_month][$grouping_day][] = $show;//set first dimension to day and then assign all events as second level to that day
                }
                ?>

                <div class="brz-eventCalendar-layout">
                    <?php
                    echo self::draw_calendar(
                        $events,
                        $detail_url,
                        $showSubscribeToCalendarButton,
                        $subscribeToCalendarButton
                    );
                    ?>
                </div>
                <?php
            } else {
                ?>
                <p>There are no events available.</p>
                <?php
            }
            ?>
        </div>
        <?php
    }

    //draw
    public function draw_calendar(
        $events = null,
        $detail_url = null,
        $showSubscribeToCalendarButton = false,
        $subscribeToCalendarButton = false
    ) {
        $results = false;
        $period  = self::get_period($events);

        $period_arr = iterator_to_array($period);

        $start_month        = reset($period_arr);
        $start_month_format = $start_month->format("Y-m");

        $end_month        = end($period_arr);
        $end_month_format = $end_month->format("Y-m");

        //iterate each month
        foreach ($period as $index => $month) {
            //set month formats
            $month_format       = $month->format("Y-m");//should match format of initial $events month
            $month_format_month = $month->format("m");//month to draw table
            $month_format_year  = $month->format("Y");//month to draw table

            $month_label_format = $month->format("F Y");

            //month pagination set
            $pag_format = $month->format("m-Y");
            $prev_month = date("Y-m", strtotime("1-{$pag_format} -1 month"));
            $next_month = date("Y-m", strtotime("1-{$pag_format} +1 month"));


            //open .month div
            $results .= '<div class="brz-eventCalendar-month brz-eventCalendar-month'.($index + 1).' {$month_format}">';

            //pagination
            $results .= "<div class=\"brz-eventCalendar-pagination\">";
            //prev
            if ($month_format === $start_month_format) {
                $results .= "<a class=\"brz-eventCalendar-pagination-previous off\"><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 256 512' class='brz-icon-svg' data-type='fa' data-name='angle-left'><path d='M31.7 239l136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L127.9 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9L201.7 409c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z'></path></svg></a>";
            } else {
                $results .= "<a href=\"{$prev_month}\" data-month=\"{$prev_month}\" class=\"brz-eventCalendar-pagination-previous\"><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 256 512' class='brz-icon-svg' data-type='fa' data-name='angle-left'><path d='M31.7 239l136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L127.9 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9L201.7 409c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z'></path></svg></a>";
            }
            //heading
            $results .= "<span class=\"brz-eventCalendar-heading\">{$month_label_format}</span>";

            //next
            if ($month_format === $end_month_format) {
                $results .= "<a class=\"brz-eventCalendar-pagination-next off\"><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 256 512' class='brz-icon-svg' data-type='fa' data-name='angle-right'><path d='M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z'></path></svg></a>";
            } else {
                $results .= "<a href=\"{$next_month}\" data-month=\"{$next_month}\" class=\"brz-eventCalendar-pagination-next\"><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 256 512' class='brz-icon-svg' data-type='fa' data-name='angle-right'><path d='M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z'></path></svg></a>";
            }

            $results .= "</div>";


            //if no output nrf
            if (empty($events[$month_format])) {
                $results .= "<h4 class=\"brz-eventCalendar-nrf\">There are no events for this month.</h4>";
            } //else results
            else {
                //get table of month and pass/format events
                $results .= self::draw_calendar_table(
                    $month_format_month,
                    $month_format_year,
                    $events[$month_format],
                    $detail_url
                );

            }//end if

            //close .month div
            $results .= "</div>";

        }//end foreach month

        if ($showSubscribeToCalendarButton) {
            $results .= "<div class='brz-eventCalendar__subscribe__container'>".$subscribeToCalendarButton."</div>";
        }

        return $results;
    }

    //draws calendar table
    private function draw_calendar_table(
        $month,
        $year,
        $events = null,
        $detail_url = null
    ) {

        /* draw table */
        $calendar = '<table cellpadding="0" cellspacing="0" class="brz-eventCalendar-table">';

        /* table headings */
        $headings = ['Sun', 'Mon', 'Tues', 'Wed', 'Thu', 'Fri', 'Sat'];

        $calendar .= '<tr class="brz-eventCalendar-row-weekdays"><th><span>'.implode(
                '</span></th><th><span>',
                $headings
            ).'</span></th></tr>';

        /* days and weeks vars now ... */
        $running_day       = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month     = date('t', mktime(0, 0, 0, $month, 1, $year));
        $days_in_this_week = 1;
        $day_counter       = 0;
        $dates_array       = [];

        /* row for week one */
        $calendar .= '<tr class="brz-eventCalendar-row">';

        /* print "blank" days until the first of the current week */
        for ($x = 0; $x < $running_day; $x++):
            $calendar .= '<td class="brz-eventCalendar-day-np"> </td>';
            $days_in_this_week++;
        endfor;

        /* keep going with days.... */
        for ($list_day = 1; $list_day <= $days_in_month; $list_day++):

            $cur_date = date('Y-m-d', mktime(0, 0, 0, $month, $list_day, $year));

            $calendar .= '<td class="brz-eventCalendar-day">';
            /* add in the day number */
            $calendar .= '<div class="brz-eventCalendar-day-number"><span>'.$list_day.'</span></div>';

            /** QUERY FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
            //$calendar.= str_repeat('<p> </p>',2);
            if (isset($events) && isset($events[$cur_date])) {
                //print_r($events[$cur_date]);
                $calendar .= "<ul class=\"brz-eventCalendar-links\">";
                foreach ($events[$cur_date] as $v) {
                    if ($this->time && !empty($v['eventstart']) && $time = strtotime($v['eventstart'])) {
                        $format = $this->timeFormat === '12' ? 'g:i A ' : 'H:i ';
                        $calendar .= '<span class="brz-eventCalendar__event-start-time">'.date($format, $time).'</span>';
                    }
                    $calendar .= "<li>";
                    $calendar .= "<div class=\"brz-eventCalendar-title\">";

                    if ($detail_url) {
                        $calendar .= "<a href=\"{$detail_url}?mc-slug={$v['slug']}\" title=\"{$v["title"]}\">";
                    }

                    $calendar .= "<span>{$v["title"]}</span>";

                    if ($detail_url) {
                        $calendar .= "</a>";
                    }
                    $calendar .= "</div>";
                    $calendar .= "</li>";
                }
                $calendar .= "</ul>";
            }

            $calendar .= '</td>';
            if ($running_day == 6):
                $calendar .= '</tr>';
                if (($day_counter + 1) != $days_in_month):
                    $calendar .= '<tr class="brz-eventCalendar-row">';
                endif;
                $running_day       = -1;
                $days_in_this_week = 0;
            endif;
            $days_in_this_week++;
            $running_day++;
            $day_counter++;
        endfor;

        /* finish the rest of the days in the week */
        if ($days_in_this_week < 8):
            for ($x = 1; $x <= (8 - $days_in_this_week); $x++):
                $calendar .= '<td class="brz-eventCalendar-day-np"> </td>';
            endfor;
        endif;

        /* final row */
        $calendar .= '</tr>';

        /* end the table */
        $calendar .= '</table>';

        /* all done, return result */

        return $calendar;
    }

    //get period between two months
    private function get_period($events)
    {
        $last     = key(end($events));
        $first    = key(reset($events));
        $start    = (new DateTime($first))->modify('first day of this month');
        $end      = (new DateTime($last))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);

        return $period;
    }
}
