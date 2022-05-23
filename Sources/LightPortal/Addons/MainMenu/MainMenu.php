<?php

/**
 * MainMenu.php
 *
 * @package MainMenu (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 12.05.22
 */

namespace Bugo\LightPortal\Addons\MainMenu;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class MainMenu extends Plugin
{
	public string $type = 'other';

	public function init()
	{
		add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons#', false, __FILE__);
		add_integration_function('integrate_current_action', __CLASS__ . '::currentAction#', false, __FILE__);
	}

	public function menuButtons(array &$buttons)
	{
		$this->prepareVariables();

		if (! empty($this->context['lp_main_menu_addon_portal_langs'][$this->user_info['language']]))
			$buttons[LP_ACTION]['title'] = $this->context['lp_main_menu_addon_portal_langs'][$this->user_info['language']];

		if (! empty($this->context['lp_main_menu_addon_forum_langs'][$this->user_info['language']]))
			$buttons[empty($this->modSettings['lp_standalone_mode']) ? 'home' : 'forum']['title'] = $this->context['lp_main_menu_addon_forum_langs'][$this->user_info['language']];

		if (empty($this->context['lp_main_menu_addon_items']))
			return;

		$pages = [];

		foreach ($this->context['lp_main_menu_addon_items'] as $item) {
			$alias = strtr(parse_url($item['url'], PHP_URL_QUERY), ['=' => '_']);

			$pages['portal_' . $alias] = [
				'title' => $this->getTranslatedTitle($item['langs']),
				'href'  => $item['url'],
				'icon'  => empty($item['unicode']) ? null : ('" style="display: none"></span><span class="portal_menu_icons fas fa-portal_' . $alias),
				'show'  => $this->canViewItem($item['access'])
			];

			$this->addInlineCss('
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

	public function currentAction(string &$current_action)
	{
		if (empty($this->context['canonical_url']) || empty($this->context['lp_main_menu_addon_items']))
			return;

		if ($this->request()->url() === $this->context['canonical_url'] && in_array($this->context['canonical_url'], array_column($this->context['lp_main_menu_addon_items'], 'url'))) {
			$current_action = 'portal_action_' . $current_action;

			if ($this->request()->isEmpty('action') && $this->request()->isNotEmpty(LP_PAGE_PARAM)) {
				$current_action = 'portal_page_' . $this->request(LP_PAGE_PARAM);
			}
		}
	}

	public function frontCustomTemplate()
	{
		if (! empty($this->context['lp_main_menu_addon_portal_langs'][$this->user_info['language']]) && ! empty($this->context['linktree'][1]))
			$this->context['linktree'][1]['name'] = $this->context['lp_main_menu_addon_portal_langs'][$this->user_info['language']];
	}

	public function addSettings(array &$config_vars)
	{
		$config_vars['main_menu'][] = ['callback', 'items', [$this, 'showList']];
	}

	public function showList()
	{
		$this->prepareForumLanguages();

		$this->prepareVariables();

		$this->setTemplate();

		callback_main_menu_table();
	}

	public function saveSettings(array &$plugin_options)
	{
		if (! isset($plugin_options['items']))
			return;

		$portal_langs = $forum_langs = [];

		if ($this->post()->has('portal_item_langs')) {
			foreach ($this->post('portal_item_langs') as $lang => $val) {
				if (! empty($val))
					$portal_langs[$lang] = $val;
			}
		}

		if ($this->post()->has('forum_item_langs')) {
			foreach ($this->post('forum_item_langs') as $lang => $val) {
				if (! empty($val))
					$forum_langs[$lang] = $val;
			}
		}

		$plugin_options['portal_langs'] = json_encode($portal_langs, JSON_UNESCAPED_UNICODE);
		$plugin_options['forum_langs']  = json_encode($forum_langs, JSON_UNESCAPED_UNICODE);

		$items = $langs = [];

		if ($this->post()->has('url')) {
			foreach ($this->post('url') as $key => $value) {
				foreach ($this->post('langs') as $lang => $val) {
					if (! empty($val[$key]))
						$langs[$key][$lang] = $val[$key];
				}

				$items[] = [
					'url'     => $this->validate($value, 'url'),
					'unicode' => $this->validate($this->post('unicode')[$key]),
					'langs'   => $langs[$key],
					'access'  => $this->validate($this->post('access')[$key], 'int')
				];
			}
		}

		$plugin_options['items'] = json_encode($items, JSON_UNESCAPED_UNICODE);
	}

	private function prepareVariables()
	{
		$this->context['lp_main_menu_addon_portal_langs'] = $this->jsonDecode($this->context['lp_main_menu_plugin']['portal_langs'] ?? '', true);
		$this->context['lp_main_menu_addon_forum_langs']  = $this->jsonDecode($this->context['lp_main_menu_plugin']['forum_langs'] ?? '', true);
		$this->context['lp_main_menu_addon_items']        = $this->jsonDecode($this->context['lp_main_menu_plugin']['items'] ?? '', true);
	}
}
