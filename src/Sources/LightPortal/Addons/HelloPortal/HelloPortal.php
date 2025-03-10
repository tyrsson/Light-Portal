<?php

/**
 * HelloPortal.php
 *
 * @package HelloPortal (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 21.03.24
 */

namespace Bugo\LightPortal\Addons\HelloPortal;

use Bugo\Compat\{Lang, Theme, Utils};
use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class HelloPortal extends Plugin
{
	public string $type = 'other';

	private array $themes = [false, 'dark', 'modern', 'flattener'];

	public function init(): void
	{
		$this->applyHook('menu_buttons');
	}

	public function menuButtons(): void
	{
		if ($this->request()->isNot('admin') || empty($steps = $this->getStepData()))
			return;

		Lang::load('Post');

		if (
			! empty(Utils::$context['admin_menu_name'])
			&& ! empty(Utils::$context[Utils::$context['admin_menu_name']])
			&& ! empty(Utils::$context[Utils::$context['admin_menu_name']]['tab_data']['title'])
		) {
			$menu = Utils::$context['admin_menu_name'];
			$tabs = Utils::$context[$menu]['tab_data'];
			$button = '<button class="button floatnone lp_hello_portal_button" @click.prevent="runTour()" x-data>'
				. Lang::$txt['lp_hello_portal']['tour_button'] . '</button>';
			$tabs['title'] .= $button;
			Utils::$context[$menu]['tab_data'] = $tabs;
		}

		$params = ['external' => true];

		Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/intro.js@4/minified/introjs.min.css', $params);

		if (! empty(Utils::$context['lp_hello_portal_plugin']['theme'])) {
			$theme = Utils::$context['lp_hello_portal_plugin']['theme'] . '.css';
			Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/intro.js@4/themes/introjs-' . $theme, $params);
		}

		if (Utils::$context['right_to_left'])
			Theme::loadCSSFile('https://cdn.jsdelivr.net/npm/intro.js@4/minified/introjs-rtl.min.css', $params);

		Theme::loadJavaScriptFile('https://cdn.jsdelivr.net/npm/intro.js@4/minified/intro.min.js', $params);

		Theme::addInlineJavaScript('
		function runTour() {
			introJs().setOptions({
				tooltipClass: "lp_addon_hello_portal",
				nextLabel: ' . Utils::escapeJavaScript(Lang::$txt['admin_next']) . ',
				prevLabel: ' . Utils::escapeJavaScript(Lang::$txt['back']) . ',
				doneLabel: ' . Utils::escapeJavaScript(Lang::$txt['attach_dir_ok']) . ',
				steps: [' . $steps . '],
				showProgress: ' . (
					empty(Utils::$context['lp_hello_portal_plugin']['show_progress']) ? 'false' : 'true'
				) . ',
				showButtons: ' . (
					empty(Utils::$context['lp_hello_portal_plugin']['show_buttons']) ? 'false' : 'true'
				) . ',
				showBullets: false,
				exitOnOverlayClick: ' . (
					empty(Utils::$context['lp_hello_portal_plugin']['exit_on_overlay_click']) ? 'false' : 'true'
				) . ',
				keyboardNavigation: ' . (
					empty(Utils::$context['lp_hello_portal_plugin']['keyboard_navigation']) ? 'false' : 'true'
				) . ',
				disableInteraction: ' . (
					empty(Utils::$context['lp_hello_portal_plugin']['disable_interaction']) ? 'false' : 'true'
				) . ',
				scrollToElement: true,
				scrollTo: "tooltip"
			}).start();
		}');
	}

	public function addSettings(array &$settings): void
	{
		$settings['hello_portal'][] = [
			'select', 'theme', array_combine($this->themes, Lang::$txt['lp_hello_portal']['theme_set'])
		];
		$settings['hello_portal'][] = ['check', 'show_progress'];
		$settings['hello_portal'][] = ['check', 'show_buttons'];
		$settings['hello_portal'][] = ['check', 'exit_on_overlay_click'];
		$settings['hello_portal'][] = ['check', 'keyboard_navigation'];
		$settings['hello_portal'][] = ['check', 'disable_interaction'];
	}

	public function credits(array &$links): void
	{
		$links[] = [
			'title' => 'Intro.js',
			'link' => 'https://github.com/usablica/intro.js',
			'author' => 'Afshin Mehrabani',
			'license' => [
				'name' => 'GNU AGPLv3',
				'link' => 'https://github.com/usablica/intro.js/blob/master/license.md'
			]
		];
	}

	private function getStepData(): string
	{
		if (! is_file($path = __DIR__ . DIRECTORY_SEPARATOR . 'steps.php'))
			return '';

		$steps = require_once $path;

		if ($this->isCurrentArea('lp_settings', 'basic'))
			return $steps['basic_settings'];

		if ($this->isCurrentArea('lp_settings', 'extra', false))
			return $steps['extra_settings'];

		if ($this->isCurrentArea('lp_settings', 'panels', false))
			return $steps['panels'];

		if ($this->isCurrentArea('lp_settings', 'misc', false))
			return $steps['misc'];

		if ($this->isCurrentArea('lp_blocks'))
			return $steps['blocks'];

		if ($this->isCurrentArea('lp_pages'))
			return $steps['pages'];

		if ($this->isCurrentArea('lp_categories'))
			return $steps['categories'];

		if ($this->isCurrentArea('lp_plugins'))
			return $steps['plugins'];

		if ($this->isCurrentArea('lp_plugins', 'add', false))
			return $steps['add_plugins'];

		return '';
	}

	private function isCurrentArea(string $area, string $sa = 'main', bool $canBeEmpty = true): bool
	{
		return $this->request()->has('area') && $this->request('area') === $area &&
			(
				$canBeEmpty
				? (Utils::$context['current_subaction'] === $sa || empty(Utils::$context['current_subaction']))
				: Utils::$context['current_subaction'] === $sa
			);
	}
}
