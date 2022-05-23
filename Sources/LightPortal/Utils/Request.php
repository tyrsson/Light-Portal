<?php declare(strict_types=1);

/**
 * Request.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class Request extends AbstractStorage
{
	public function __construct()
	{
		$this->storage = &$_REQUEST;
	}

	/**
	 * @param string|array ...$patterns
	 * @return bool
	 */
	public function is(...$patterns): bool
    {
		if ($this->has('action') === false) {
			return false;
		}

		if (is_array($patterns[0])) {
			$patterns = $patterns[0];
		}

		foreach ($patterns as $pattern) {
			if ($this->storage['action'] === $pattern) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string|array ...$patterns
	 * @return bool
	 */
	public function isNot(...$patterns): bool
	{
		return empty($this->is($patterns));
	}

	/**
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function json(?string $key = null, $default = null)
	{
		$data = json_decode(file_get_contents('php://input'), true) ?? [];

		if (isset($data[$key])) {
			return $data[$key] ?: $default;
		}

		return $data;
	}

	public function url(): string
	{
		return $_SERVER['REQUEST_URL'] ?? '';
	}
}
