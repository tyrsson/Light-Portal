<?php

/**
 * MainMenu
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9.4
 */

namespace Bugo\LightPortal\Addons\MainMenu;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

/**
 * Generated by PluginMaker
 */
class MainMenu extends Plugin
{
	/** @var string */
	public $type = 'other';

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
		add_integration_function('integrate_current_action', __CLASS__ . '::currentAction#', false, __FILE__);
	}

	/**
	 * @param array $buttons
	 * @return void
	 */
	public function menuButtons(array &$buttons)
	{
		global $context, $modSettings;

		$context['lp_main_menu_addon_items'] = !empty($modSettings['lp_main_menu_addon_items']) ? json_decode($modSettings['lp_main_menu_addon_items'], true) : [];

		if (empty($context['lp_main_menu_addon_items']))
			return;

		$pages = [];

		foreach ($context['lp_main_menu_addon_items'] as $item) {
			$alias = strtr(parse_url($item['url'], PHP_URL_QUERY), ['=' => '_']);

			$pages['portal_' . $alias] = array(
				'title' => Helpers::getTranslatedTitle($item['langs']),
				'href'  => $item['url'],
				'icon'  => empty($item['unicode']) ? null : ('" style="display: none"></span><span class="portal_menu_icons fas fa-portal_' . $alias),
				'show'  => Helpers::canViewItem($item['access'])
			);

			addInlineCss('
			.fa-portal_' . $alias . '::before {
				content: "\\' . $item['unicode'] . '";
			}');
		}

		$counter = -1;
		foreach (array_keys($buttons) as $area) {
			$counter++;

			if ($area === 'admin')
				break;
		}

		$buttons = array_merge(
			array_slice($buttons, 0, $counter, true),
			$pages,
			array_slice($buttons, $counter, null, true)
		);
	}

	/**
	 * @param string $current_action
	 * @return void
	 */
	public function currentAction(string &$current_action)
	{
		global $context;

		if (empty($context['canonical_url']) || empty($context['lp_main_menu_addon_items']))
			return;

		if (Helpers::request()->url() === $context['canonical_url'] && in_array($context['canonical_url'], array_column($context['lp_main_menu_addon_items'], 'url'))) {
			$current_action = 'portal_action_' . $current_action;

			if (Helpers::request()->isEmpty('action') && Helpers::request()->notEmpty(LP_PAGE_PARAM)) {
				$current_action = 'portal_page_' . Helpers::request(LP_PAGE_PARAM);
			}
		}
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(array &$config_vars)
	{
		$config_vars['main_menu'][] = array('callback', 'items', array($this, 'showList'));
	}

	/**
	 * @return void
	 */
	public function showList()
	{
		$this->loadTemplate();

		template_callback_main_menu_table();
	}

	/**
	 * @param array $plugin_options
	 * @return void
	 */
	public function saveSettings(&$plugin_options)
	{
		if (!isset($plugin_options['lp_main_menu_addon_items']))
			return;

		$items = $langs = [];

		if (Helpers::post()->has('url')) {
			foreach (Helpers::post('url') as $key => $value) {
				foreach (Helpers::post('langs') as $lang => $val) {
					if (!empty($val[$key]))
						$langs[$key][$lang] = $val[$key];
				}

				$items[] = array(
					'url'     => Helpers::validate($value, 'url'),
					'unicode' => Helpers::validate(Helpers::post('unicode')[$key]),
					'langs'   => $langs[$key],
					'access'  => Helpers::validate(Helpers::post('access')[$key], 'int')
				);
			}
		}

		$plugin_options['lp_main_menu_addon_items'] = json_encode($items, JSON_UNESCAPED_UNICODE);
	}
}
