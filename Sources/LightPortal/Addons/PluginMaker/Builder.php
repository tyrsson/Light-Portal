<?php

/**
 * Builder.php
 *
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 11.05.22
 */

namespace Bugo\LightPortal\Addons\PluginMaker;

use Bugo\LightPortal\Helper;

if (! defined('LP_NAME'))
	die('No direct access...');

class Builder
{
	use Helper;

	protected string $name;
	protected string $path;

	public function __construct(string $name)
	{
		$this->name = $name;
		$this->path = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $name;
	}

	public function create(string $content): Builder
	{
		$dir_ready = mkdir($this->path);

		if (! $dir_ready) {
			$this->fatalError($this->txt['lp_plugin_maker']['addon_dir_not_created']);
		}

		copy(LP_ADDON_DIR . DIRECTORY_SEPARATOR . 'index.php', $this->path . DIRECTORY_SEPARATOR . 'index.php');

		$this->addSecurityCheck($content);

		file_put_contents($this->path . DIRECTORY_SEPARATOR . $this->name . '.php', $content, LOCK_EX);

		return $this;
	}

	public function createLangs(array $languages = []): Builder
	{
		$dir_ready = mkdir($this->path . DIRECTORY_SEPARATOR . 'langs');

		if (! $dir_ready) {
			$this->fatalError($this->txt['lp_plugin_maker']['lang_dir_not_created']);
		}

		copy($this->path . DIRECTORY_SEPARATOR . 'index.php', $this->path . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR . 'index.php');

		foreach ($languages as $lang => $data) {
			foreach ($data as $content) {
				file_put_contents($this->path . DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR . $lang . '.php', $content, FILE_APPEND | LOCK_EX);
			}
		}

		return $this;
	}

	private function addSecurityCheck(string &$content)
	{
		$message = <<<XXX
	if (! defined('LP_NAME'))
		die('No direct access...');

	/**
	 * Generated by PluginMaker
	 */
	XXX;

		$content = str_replace('/**
 * Generated by PluginMaker
 */', $message, $content);
	}
}
