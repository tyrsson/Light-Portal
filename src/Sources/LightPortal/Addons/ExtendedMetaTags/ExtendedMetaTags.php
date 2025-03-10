<?php

/**
 * ExtendedMetaTags.php
 *
 * @package ExtendedMetaTags (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 23.04.24
 */

namespace Bugo\LightPortal\Addons\ExtendedMetaTags;

use Bugo\Compat\{Lang, Utils};
use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Areas\Fields\VirtualSelectField;
use Bugo\LightPortal\Areas\PageArea;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class ExtendedMetaTags extends Plugin
{
	public string $type = 'page_options seo';

	private array $meta_robots = ['', 'index, follow', 'index, nofollow', 'noindex, follow', 'noindex, nofollow'];

	private array $meta_rating = ['', '14 years', 'adult', 'general', 'mature', 'restricted', 'save for kids'];

	public function init(): void
	{
		$this->applyHook('theme_context');
	}

	public function themeContext(): void
	{
		if ($this->request()->hasNot('page') || empty(Utils::$context['lp_page']['options']))
			return;

		if (! empty(Utils::$context['lp_page']['options']['meta_robots'])) {
			Utils::$context['meta_tags'][] = [
				'name' => 'robots', 'content' => Utils::$context['lp_page']['options']['meta_robots']
			];
		}

		if (! empty(Utils::$context['lp_page']['options']['meta_rating'])) {
			Utils::$context['meta_tags'][] = [
				'name' => 'rating', 'content' => Utils::$context['lp_page']['options']['meta_rating']
			];
		}
	}

	public function preparePageParams(array &$params): void
	{
		$params['meta_robots'] = '';
		$params['meta_rating'] = '';
	}

	public function validatePageParams(array &$params): void
	{
		$params['meta_robots'] = FILTER_DEFAULT;
		$params['meta_rating'] = FILTER_DEFAULT;
	}

	public function preparePageFields(): void
	{
		VirtualSelectField::make('meta_robots', Lang::$txt['lp_extended_meta_tags']['meta_robots'])
			->setTab(PageArea::TAB_SEO)
			->setOptions(array_combine($this->meta_robots, Lang::$txt['lp_extended_meta_tags']['meta_robots_set']))
			->setValue(Utils::$context['lp_page']['options']['meta_robots']);

		VirtualSelectField::make('meta_rating', Lang::$txt['lp_extended_meta_tags']['meta_rating'])
			->setTab(PageArea::TAB_SEO)
			->setOptions(array_combine($this->meta_rating, Lang::$txt['lp_extended_meta_tags']['meta_rating_set']))
			->setValue(Utils::$context['lp_page']['options']['meta_rating']);
	}
}
