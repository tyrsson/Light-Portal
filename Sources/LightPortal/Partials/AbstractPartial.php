<?php declare(strict_types=1);

/**
 * AbstractPartial.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.2
 */

namespace Bugo\LightPortal\Partials;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractPartial
{
	use Helper;
}
