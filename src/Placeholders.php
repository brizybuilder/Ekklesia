<?php

namespace BrizyEkklesia;

use BrizyEkklesia\Placeholder\ArticleDetailPlaceholder;
use BrizyEkklesia\Placeholder\ArticleLayoutPlaceholder;
use BrizyEkklesia\Placeholder\ArticleListPlaceholder;
use BrizyEkklesia\Placeholder\EventCalendarPlaceholder;
use BrizyEkklesia\Placeholder\EventDetailPlaceholder;
use BrizyEkklesia\Placeholder\EventFeaturedPlaceholder;
use BrizyEkklesia\Placeholder\EventLayoutPlaceholder;
use BrizyEkklesia\Placeholder\EventListPlaceholder;
use BrizyEkklesia\Placeholder\FormPlaceholder;
use BrizyEkklesia\Placeholder\GroupDetailPlaceholder;
use BrizyEkklesia\Placeholder\GroupFeaturedPlaceholder;
use BrizyEkklesia\Placeholder\GroupLayoutPlaceholder;
use BrizyEkklesia\Placeholder\GroupListPlaceholder;
use BrizyEkklesia\Placeholder\GroupSliderPlaceholder;
use BrizyEkklesia\Placeholder\SermonDetailPlaceholder;
use BrizyEkklesia\Placeholder\SermonFeaturedPlaceholder;
use BrizyEkklesia\Placeholder\SermonLayoutPlaceholder;
use BrizyEkklesia\Placeholder\SermonListPlaceholder;
use BrizyEkklesia\Placeholder\ArticleFeaturedPlaceholder;
use BrizyEkklesia\Placeholder\StaffDetailPlaceholder;
use BrizyEkklesia\Placeholder\StaffLayoutPlaceholder;
use BrizyEkklesia\Placeholder\StaffListPlaceholder;
use BrizyEkklesia\Placeholder\PrayerPlaceholder;
use BrizyEkklesia\Placeholder\PrayerButtonPlaceholder;
use BrizyEkklesia\Placeholder\PrayerFormPlaceholder;
use BrizyEkklesia\Placeholder\PrayerWallPlaceholder;
use BrizyEkklesia\Placeholder\StaffFeaturedPlaceholder;
use BrizyPlaceholders\Replacer;
use Twig_Environment;

class Placeholders
{
    /**
     * @var MonkCms
     */
    private $monkCms;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var PrayerCloudApi|null
     */
    private $prayerCloudApi;

    public function __construct(MonkCms $monkCms, Twig_Environment $twig, ?PrayerCloudApi $prayerCloudApi = null)
    {
        $this->monkCms        = $monkCms;
        $this->twig           = $twig;
        $this->prayerCloudApi = $prayerCloudApi;
    }

    public function getPlaceholders(Replacer $replacer)
    {
        return [
            SermonListPlaceholder::NAME => function () use ($replacer) {
                return new SermonListPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            SermonLayoutPlaceholder::NAME => function () use ($replacer) {
                return new SermonLayoutPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            SermonDetailPlaceholder::NAME => function () use ($replacer) {
                return new SermonDetailPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            SermonFeaturedPlaceholder::NAME => function () use ($replacer) {
                return new SermonFeaturedPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            EventListPlaceholder::NAME => function () use ($replacer) {
                return new EventListPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            EventCalendarPlaceholder::NAME => function () use ($replacer) {
                return new EventCalendarPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            EventLayoutPlaceholder::NAME => function () use ($replacer) {
                return new EventLayoutPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            EventDetailPlaceholder::NAME => function () use ($replacer) {
                return new EventDetailPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            EventFeaturedPlaceholder::NAME => function () use ($replacer) {
                return new EventFeaturedPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            GroupListPlaceholder::NAME => function () use ($replacer) {
                return new GroupListPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            GroupSliderPlaceholder::NAME => function () use ($replacer) {
                return new GroupSliderPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            GroupLayoutPlaceholder::NAME => function () use ($replacer) {
                return new GroupLayoutPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            GroupDetailPlaceholder::NAME => function () use ($replacer) {
                return new GroupDetailPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            GroupFeaturedPlaceholder::NAME => function () use ($replacer) {
                return new GroupFeaturedPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            FormPlaceholder::NAME => function () use ($replacer) {
                return new FormPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            PrayerPlaceholder::NAME => function () use ($replacer) {
                return new PrayerPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            PrayerButtonPlaceholder::NAME => function () use ($replacer) {
                return new PrayerButtonPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            PrayerFormPlaceholder::NAME => function () use ($replacer) {
                return new PrayerFormPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            PrayerWallPlaceholder::NAME => function () use ($replacer) {
                return new PrayerWallPlaceholder($this->monkCms, $this->twig, $replacer, $this->prayerCloudApi);
            },
            ArticleDetailPlaceholder::NAME => function () use ($replacer) {
                return new ArticleDetailPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            ArticleFeaturedPlaceholder::NAME => function () use ($replacer) {
                return new ArticleFeaturedPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            ArticleListPlaceholder::NAME => function () use ($replacer) {
                return new ArticleListPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            ArticleLayoutPlaceholder::NAME => function () use ($replacer) {
                return new ArticleLayoutPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            StaffListPlaceholder::NAME => function () use ($replacer) {
                return new StaffListPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            StaffLayoutPlaceholder::NAME => function () use ($replacer) {
                return new StaffLayoutPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            StaffDetailPlaceholder::NAME => function () use ($replacer) {
                return new StaffDetailPlaceholder($this->monkCms, $this->twig, $replacer);
            },
            StaffFeaturedPlaceholder::NAME => function () use ($replacer) {
                return new StaffFeaturedPlaceholder($this->monkCms, $this->twig, $replacer);
            },
        ];
    }
}