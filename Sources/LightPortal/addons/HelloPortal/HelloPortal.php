<?php

/**
 * HelloPortal
 *
 * @package Light Portal
 * @link https://github.com/dragomano/Light-Portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9.5
 */

namespace Bugo\LightPortal\Addons\HelloPortal;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

/**
 * Generated by PluginMaker
 */
class HelloPortal extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'other';

	/**
	 * @var array
	 */
	private $themes = [false, 'royal', 'nassim', 'nazanin', 'dark', 'modern', 'flattener'];

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
	}

	/**
	 * @return void
	 */
	public function menuButtons()
	{
		global $context, $txt, $modSettings;

		if (Helpers::request()->isNot('admin') || empty($steps = $this->getStepData()))
			return;

		loadLanguage('Post');

		if (!empty($context[$context['admin_menu_name']]) && !empty($context[$context['admin_menu_name']]['tab_data']['title']))
			$context[$context['admin_menu_name']]['tab_data']['title'] .= '<button class="button floatnone lp_hello_portal_button" @click.prevent="runTour()" x-data>' . $txt['lp_hello_portal']['tour_button'] . '</button>';

		loadCSSFile('https://cdn.jsdelivr.net/npm/intro.js@4/minified/introjs.min.css', array('external' => true));

		if (!empty($modSettings['lp_hello_portal_addon_theme']))
			loadCSSFile('https://cdn.jsdelivr.net/npm/intro.js@4/themes/introjs-' . $modSettings['lp_hello_portal_addon_theme'] . '.css', array('external' => true));

		if ($context['right_to_left'])
			loadCSSFile('https://cdn.jsdelivr.net/npm/intro.js@4/minified/introjs-rtl.min.css', array('external' => true));

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/intro.js@4/minified/intro.min.js', array('external' => true));

		addInlineJavaScript('
		function runTour() {
			introJs().setOptions({
				tooltipClass: "lp_addon_hello_portal",
				nextLabel: ' . JavaScriptEscape($txt['previous_next_forward']) . ',
				prevLabel: ' . JavaScriptEscape($txt['previous_next_back']) . ',
				doneLabel: ' . JavaScriptEscape($txt['announce_done']) . ',
				steps: [' . $steps . '],
				showProgress: ' . (!empty($modSettings['lp_hello_portal_addon_show_progress']) ? 'true' : 'false') . ',
				showButtons: ' . (!empty($modSettings['lp_hello_portal_addon_show_buttons']) ? 'true' : 'false') . ',
				showBullets: false,
				exitOnOverlayClick: ' . (!empty($modSettings['lp_hello_portal_addon_exit_on_overlay_click']) ? 'true' : 'false') . ',
				keyboardNavigation: ' . (!empty($modSettings['lp_hello_portal_addon_keyboard_navigation']) ? 'true' : 'false') . ',
				disableInteraction: ' . (!empty($modSettings['lp_hello_portal_addon_disable_interaction']) ? 'true' : 'false') . ',
				scrollToElement: true,
				scrollTo: "tooltip"
			}).start();
		}');
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(array &$config_vars)
	{
		global $modSettings, $txt;

		$addSettings = [];
		if (!isset($modSettings['lp_hello_portal_addon_show_progress']))
			$addSettings['lp_hello_portal_addon_show_progress'] = 1;
		if (!isset($modSettings['lp_hello_portal_addon_show_buttons']))
			$addSettings['lp_hello_portal_addon_show_buttons'] = 1;
		if (!isset($modSettings['lp_hello_portal_addon_keyboard_navigation']))
			$addSettings['lp_hello_portal_addon_keyboard_navigation'] = 1;
		if (!empty($addSettings))
			updateSettings($addSettings);

		$config_vars['hello_portal'][] = array('select', 'theme', array_combine($this->themes, $txt['lp_hello_portal']['theme_set']));
		$config_vars['hello_portal'][] = array('check', 'show_progress');
		$config_vars['hello_portal'][] = array('check', 'show_buttons');
		$config_vars['hello_portal'][] = array('check', 'exit_on_overlay_click');
		$config_vars['hello_portal'][] = array('check', 'keyboard_navigation');
		$config_vars['hello_portal'][] = array('check', 'disable_interaction');
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(array &$links)
	{
		$links[] = array(
			'title' => 'Intro.js',
			'link' => 'https://github.com/usablica/intro.js',
			'author' => 'Afshin Mehrabani',
			'license' => array(
				'name' => 'GNU AGPLv3',
				'link' => 'https://github.com/usablica/intro.js/blob/master/license.md'
			)
		);
	}

	/**
	 * @return string
	 */
	private function getStepData(): string
	{
		global $context;

		$steps = require_once __DIR__ . DIRECTORY_SEPARATOR . 'steps.php';

		if ($this->isCurrentArea('lp_settings', 'basic'))
			return $steps['basic_settings'];

		if ($this->isCurrentArea('lp_settings', 'extra', false))
			return $steps['extra_settings'];

		if ($this->isCurrentArea('lp_settings', 'categories', false))
			return $steps['categories'];

		if ($this->isCurrentArea('lp_settings', 'panels', false))
			return $steps['panels'];

		if ($this->isCurrentArea('lp_settings', 'misc', false))
			return $steps['misc'];

		if ($this->isCurrentArea('lp_blocks'))
			return $steps['blocks'];

		if ($this->isCurrentArea('lp_pages'))
			return $steps['pages'];

		if ($this->isCurrentArea('lp_plugins'))
			return $steps['plugins'];

		if ($this->isCurrentArea('lp_plugins', 'add', false))
			return $steps['add_plugins'];

		return '';
	}

	/**
	 * @param string $area
	 * @return bool
	 */
	private function isCurrentArea(string $area, string $sa = 'main', bool $canBeEmpty = true)
	{
		global $context;

		return Helpers::request()->has('area') && Helpers::request('area') === $area &&
			($canBeEmpty ? ($context['current_subaction'] === $sa || empty($context['current_subaction'])) : $context['current_subaction'] === $sa);
	}
}
