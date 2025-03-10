<?php declare(strict_types=1);

/**
 * UrlField.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Fields;

if (! defined('SMF'))
	die('No direct access...');

class UrlField extends TextField
{
	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);

		$this->setType('url');
	}
}
