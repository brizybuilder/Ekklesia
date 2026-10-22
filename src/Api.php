<?php

namespace BrizyEkklesia;

use Exception;

class Api
{
	/**
	 * @var MonkCms
	 */
	private $monkCms;

	/**
	 * @var PrayerCloudApi|null
	 */
	private $prayerCloudApi;

	public function __construct(MonkCms $monkCms, ?PrayerCloudApi $prayerCloudApi = null)
	{
		$this->monkCms = $monkCms;
		$this->prayerCloudApi = $prayerCloudApi;
	}

	/**
	 * @throws Exception
	 */
	public function getCats($module, $query)
	{
		$cats = $this->monkCms->get([
			'module' => $module, 
			'display' => 'categories',
			'groupby' => $query['groupby'] ?? null,
			'order' => $query['order'] ?? null
		]);

		$options = [];
		
		$_show = $query['show'] ?? "show";
		
		foreach ($cats[$_show] as $category) {
			$options[$category['slug']] = $category['name'];
		}

		return $options;
	}



	public function getList($module, $query)
	{
		$args= $this->monkCms->get([
			'module' => $module,
			'display' => 'list',
			'groupby' => $query['groupby'] ?? null,
			'order' => $query['order'] ?? null
		]);

		$options = [];
		
		$_show = $query['show'] ?? "show";
		
		
		foreach ($args[$_show] as $category) {
			$options[$category['slug']] = $category['title'];
		}

		return $options;
	}

	public function getSermon($query)
	{
		$display = $query['display'] ?? 'categories';
		switch ($display) {
			case "list":
				return $this->getList('sermon', $query);
			case "categories":
				return $this->getCats('sermon', $query);
		}
		return [];
	}

	public function getSmallgroup($query)
	{
		$display = $query['display'] ?? 'categories';
		switch ($display) {
			case "list":
				return $this->getList('smallgroup', $query);
			case "categories":
				return $this->getCats('smallgroup', $query);
		}
		return [];
	}

	public function getGroup($query)
	{
		$display = $query['display'] ?? 'list';
		switch ($display) {
			case "list":
				return $this->getList('group', $query);
			case "categories":
				return $this->getCats('group', $query);
		}
		return [];
	}

	/**
	 * @throws Exception
	 */
	public function getGroups()
	{
		$groups  = $this->monkCms->get(['module' => 'group', 'display' => 'list']);
		$options = [];

		foreach ($groups['show'] as $group) {
			$options[$group['slug']] = $group['title'];
		}

		return $options;
	}

	/**
	 * @throws Exception
	 */
	public function getSeries($module)
	{
		$series  = $this->monkCms->get(['module' => $module, 'display' => 'list', 'groupby' => 'series']);
		$options = [];

		foreach ($series['group_show'] as $serie) {
			$options[$serie['slug']] = $serie['title'];
		}

		return $options;
	}

	/**
	 * @param string $module - sermon|event
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getRecent($module)
	{
		$recents = $this->monkCms->get([
			'module'      => $module,
			'display'     => 'list',
			'order'       => 'recent',
			'howmany'     => 20,
			'emailencode' => 'no',
		]);

		$options = [];

		foreach ($recents['show'] as $recent) {
			$options[$recent['slug']] = isset($recent['title']) ? $recent['title'] : $recent['name'];
		}

		return $options;
	}

	public function getStaff(array $query = [])
	{
        $args = [
            'module'      => 'member',
            'display'     => 'list',
            'order'       => 'recent',
            'howmany'     => 20,
            'emailencode' => 'no',
            'restrict'    => 'no',
        ];

        if (!empty($query['find_group'])) {
            $args['find_group'] = $query['find_group'];
        }

        $recents = $this->monkCms->get($args);
		$options = [];

        if (empty($recents['show'])) {
            return [];
        }

		foreach ($recents['show'] as $recent) {
			$options[$recent['id']] = $recent['fullname'];
		}

        asort($options);

		return $options;
	}

	/**
	 * @throws Exception
	 */
	public function getCatsLevels($module)
	{
		$cats    = $this->monkCms->get(['module' => $module, 'display' => 'categories',]);
		$parents = [];

        if (!empty($cats['level1'])) {
            foreach ($cats['level1'] as $cat) {
                $parents[$cat['slug']] = $cat['name'];
            }
            asort($parents);
        }

		$childs = [];
        if (!empty($cats['level2'])) {
            foreach ($cats['level2'] as $cat) {
                $childs[$cat['bid']] = $cat['name'];
            }
            asort($childs);
        }

		return [
			'parents' => $parents,
			'childs'  => $childs,
		];
	}

	/**
	 * @throws Exception
	 */
	public function getForms()
	{
		$forms   = $this->monkCms->get(['module' => 'fmsform', 'display' => 'list', 'groupby' => '__embedhtml__',]);
		$options = [];

		foreach ($forms['show'] as $form) {
			$options[$form['id']] = $form['name'];
		}

		return $options;
	}

    /**
     * @throws Exception
     */
    public function getGroupSeries()
    {
        $groups  = $this->monkCms->get(['module'  => 'series', 'display' => 'list', 'type' => 'group']);
        $options = [];

        foreach ($groups['show'] as $serie) {
            $options[$serie['slug']] = $serie['title'];
        }

        return $options;
    }

    /**
     * @throws Exception
     */
    public function getArticleSeries()
    {
        $series  = $this->monkCms->get(['module' => 'series', 'display' => 'list', 'type' => 'article']);
        $options = [];

        foreach ($series['show'] as $serie) {
            $options[$serie['slug']] = $serie['title'];
        }

        return $options;
    }

	public function getPrayer($query)
	{
		if (!$this->prayerCloudApi) {
			return [];
		}

		$tags = $this->prayerCloudApi->getTags('category');

		if (!$tags || !isset($tags->data->category)) {
			return [];
		}

		$options = [];
		foreach ($tags->data->category as $tag) {
			if (isset($tag->data->slug, $tag->data->name)) {
				$options[$tag->data->slug] = $tag->data->name;
			}
		}

		return $options;
	}

	/**
	 * @throws Exception
	 */
	public function getModule($module, $query)
	{
		switch ($module) {
			case 'sermon':
			case 'smallgroup':
			case 'group':
            case 'staff':
            case 'forms':
            case 'groups':
            case 'groupSeries':
			case 'prayer':
				$data = $this->{'get' . ucfirst($module)}($query);
				break;
			case 'event':
				$data = $this->getCats($module, $query);
				break;
			case 'eventsLvl':
				$data = $this->getCatsLevels('event');
				break;
			case 'smallgroupsLvl':
				$data = $this->getCatsLevels('smallgroup');
				break;
			case 'sermonsLvl':
				$data = $this->getCatsLevels('sermon');
				break;
            case 'recentSermons':
				$data = $this->getRecent('sermon');
				break;
			case 'events':
				$data = $this->getRecent('event');
				break;
			case 'smallgroups':
				$data = $this->getRecent('smallgroup');
				break;
			case 'articleRecent':
				$data = $this->getRecent('article');
				break;
			case 'articleCategories':
				$data = $this->getCats('article', $query);
				break;
			case 'series':
				$data = $this->getSeries('sermon');
				break;
			case 'articleSeries':
				$data = $this->getArticleSeries();
				break;
			case 'articlesLvl':
				$data = $this->getCatsLevels('article');
				break;
			default:
				$data = [];
		}

		return $data;
	}
}
