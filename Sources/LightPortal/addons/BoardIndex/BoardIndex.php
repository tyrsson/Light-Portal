<?php

/**
 * BoardIndex
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\BoardIndex;

use Bugo\LightPortal\Addons\Plugin;

class BoardIndex extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'other';

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_mark_read_button', __CLASS__ . '::toggleRobotNoIndex#', false, __FILE__);
	}

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $txt, $scripturl, $modSettings;

		$txt['lp_board_index']['description'] = sprintf($txt['lp_board_index']['description'], $scripturl . '?action=forum');

		if (!isset($modSettings['lp_board_index_addon_allow_for_spiders']))
			updateSettings(['lp_board_index_addon_allow_for_spiders' => false]);

		$config_vars['board_index'][] = array('check', 'allow_for_spiders');
	}

	/**
	 * Toggle indexing of the main forum page
	 *
	 * Переключаем возможность индексации главной страницы форума
	 *
	 * @return void
	 */
	public function toggleRobotNoIndex()
	{
		global $modSettings, $context;

		if (!empty($modSettings['lp_frontpage_mode']))
			$context['robot_no_index'] = empty($modSettings['lp_board_index_addon_allow_for_spiders']);
	}
}
