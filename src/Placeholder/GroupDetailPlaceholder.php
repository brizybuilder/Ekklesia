<?php

namespace BrizyEkklesia\Placeholder;

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class GroupDetailPlaceholder extends PlaceholderAbstract
{
	const NAME = 'ekk_group_detail';

	public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
	{
		$options = [
			'show_image'         => false,
			'show_title'         => false,
			'show_category'      => false,
			'show_group'         => false,
			'show_meta_headings' => false,
			'show_day'           => false,
			'show_times'         => false,
			'show_status'        => false,
			'show_childcare'     => false,
			'show_resourcelink'  => false,
			'show_content'       => false,
			'groups_recent'      => false,
			'previous_page'      => false,
            'show_meta_icons'    => false,
			'date_format' 		 => 'g:i a'
		];

		$settings = array_merge($options, $placeholder->getAttributes());
		$cms      = $this->monkCMS;

		extract($settings);

		if (isset($_GET['mc-slug'])) {
			$slug = $_GET['mc-slug'];
		} elseif ($groups_recent) {
			$slug = $groups_recent;
		} else {
			$recent = $cms->get([
				'module'      => 'smallgroup',
				'display'     => 'list',
				'order'       => 'recent',
				'howmany'     => 1,
				'emailencode' => 'no',
			]);

			$slug = isset($recent['show'][0]['slug']) ? $recent['show'][0]['slug'] : '';
		}

		$content = $cms->get([
			'module'      => 'smallgroup',
			'display'     => 'detail',
			'find'        => $slug,
			'show'        => "__endtime format='g:ia'__",
			'emailencode' => 'no',
		]);

		if (empty($content['show'])) {
			echo '<p>There is no group available.</p>';
			return;
		}

		$item = $content['show'];

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

		echo "<div class='brz-groupDetail__item'>";

		if ($show_title) {
			echo "<h2 class=\"brz-groupDetail__item--meta--title brz-ministryBrands__item--meta-title\">{$item['name']}</h2>";
		}

		if ($show_day && $item['dayoftheweek']) {
			echo "<h5 class=\"brz-groupDetail__item--meta--date brz-ministryBrands__item--meta-day\">";
			if($show_meta_headings) {
				if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\"><path fill=\"currentColor\" d=\"M96 32V64H48C21.5 64 0 85.5 0 112v48H448V112c0-26.5-21.5-48-48-48H352V32c0-17.7-14.3-32-32-32s-32 14.3-32 32V64H160V32c0-17.7-14.3-32-32-32S96 14.3 96 32zM448 192H0V464c0 26.5 21.5 48 48 48H400c26.5 0 48-21.5 48-48V192z\"></path></svg>
</span>";
				else echo "<span>Meeting Day: </span>";
			}
			echo "<span>{$item['dayoftheweek']}</span>";
			echo "</h5>";
		}
		if ($show_times && ($item['starttime'] || $item['endtime'])) {
			echo "<h5 class=\"brz-groupDetail__item--meta--date brz-ministryBrands__item--meta-times\">";
			if($show_meta_headings) {
				if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z\"></path></svg>
</span>";
				else echo "<span>Meeting Time: </span>";
			}
			if ($item['starttime']) {
				echo date($date_format, strtotime($item['starttime']));
			}
            if ($item['endtime']) {
				echo " - " . date($date_format, strtotime($item['endtime']));
			}
			echo "</h5>";
		}

		if ($show_image && $item['imageurl']) {
			echo "<div class=\"brz-ministryBrands__item--media\"><img src=\"{$item['imageurl']}\" alt=\"\" /></div>";
		}

		if ($show_category && $item['category']) {
			echo "<h6 class=\"brz-groupDetail__item--meta brz-ministryBrands__item--meta-category\">";
			if($show_meta_headings) {
				if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M0 48V487.7C0 501.1 10.9 512 24.3 512c5 0 9.9-1.5 14-4.4L192 400 345.7 507.6c4.1 2.9 9 4.4 14 4.4c13.4 0 24.3-10.9 24.3-24.3V48c0-26.5-21.5-48-48-48H48C21.5 0 0 21.5 0 48z\"></path></svg>
</span>";
				else echo "<span>Category: </span>";
			}
			echo "<span>{$item['category']}</span>";
			echo "</h6>";
		}
		if ($show_group && $item['group']) {
			echo "<h6 class=\"brz-groupDetail__item--meta brz-ministryBrands__item--meta-group\">";
			if($show_meta_headings) {
				if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 640 512\"><path fill=\"currentColor\" d=\"M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z\"></path></svg>
</span>";
				else echo "<span>Group: </span>";
			}
			echo "<span>{$item['group']}</span>";
			echo "</h6>";
		}
		if ($show_status && $item['groupstatus']) {
			echo "<h6 class=\"brz-groupDetail__item--meta brz-ministryBrands__item--meta-status\">";
			if($show_meta_headings) {
				if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M96 0c17.7 0 32 14.3 32 32V64l352 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-352 0V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V128H32C14.3 128 0 113.7 0 96S14.3 64 32 64H64V32C64 14.3 78.3 0 96 0zm96 160H448c17.7 0 32 14.3 32 32V352c0 17.7-14.3 32-32 32H192c-17.7 0-32-14.3-32-32V192c0-17.7 14.3-32 32-32z\"></path></svg>
</span>";
				else echo "<span>Status: </span>";
			}
			echo "<span>{$item['groupstatus']}</span>";
			echo "</h6>";
		}
		if ($show_childcare) {
			$childcare = "No";
			if ($item['childcareprovided']) {
				$childcare = "Yes";
			}
			echo "<h6 class=\"brz-groupDetail__item--meta brz-ministryBrands__item--meta-childcare\">";
			if($show_meta_headings) {
				if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"currentColor\" d=\"M256 192H.1C2.7 117.9 41.3 52.9 99 14.1c13.3-8.9 30.8-4.3 39.9 8.8L256 192zm128-32c0-35.3 28.7-64 64-64h32c17.7 0 32 14.3 32 32s-14.3 32-32 32l-32 0v64c0 25.2-5.8 50.2-17 73.5s-27.8 44.5-48.6 62.3s-45.5 32-72.7 41.6S253.4 416 224 416s-58.5-5-85.7-14.6s-51.9-23.8-72.7-41.6s-37.3-39-48.6-62.3S0 249.2 0 224l224 0 160 0V160zM80 416a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm240 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0z\"></path></svg>
</span>";
				else echo "<span>Childcare Provided: </span>";
			}
			echo "<span>{$childcare}</span>";
			echo "</h6>";
		}
		if ($show_resourcelink && $item['resourcelink']) {
			$resource_target = "";
			if ($item['iflinknewwindow']) {
				$resource_target = " target=\"_blank\"";
			}
			echo "<h6 class=\"brz-groupDetail__item--meta brz-ministryBrands__item--meta-resourceLink\">";
			if($show_meta_headings) {
				if($show_meta_icons) echo "<span class=\"brz-ministryBrands__meta--icons\"><svg class=\"brz-icon-svg align-[initial]\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><path fill=\"currentColor\" d=\"M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM216 232V334.1l31-31c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-72 72c-9.4 9.4-24.6 9.4-33.9 0l-72-72c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l31 31V232c0-13.3 10.7-24 24-24s24 10.7 24 24z\"></path></svg>
</span>";
				else echo "<span>Resource: </span>";
			}
			echo "<a href=\"{$item['resourcelink']}\" {$resource_target}>{$item['resourcelink']}</a>";
			echo "</h6>";
		}
		if ($show_content && $item['description']) {
			echo "<div class=\"brz-groupDetail__item--meta--preview\">{$item['description']}</div>";
		}

		if ($previous_page) {
			echo '<div class="brz-ministryBrands__item--meta--links brz-ministryBrands__item--meta--links--previous">
	                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512" class="brz-icon-svg align-[initial]"><path d="M31.7 239l136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L127.9 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9L201.7 409c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z"></path></svg>
	                    Previous Page</div>';
		}

		echo "</div>";
	}
}
