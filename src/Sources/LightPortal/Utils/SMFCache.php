<?php declare(strict_types=1);

/**
 * SMFCache.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Utils;

use function cache_get_data;
use function cache_put_data;
use function clean_cache;

if (! defined('SMF'))
	die('No direct access...');

final class SMFCache implements CacheInterface
{
	private string $prefix = 'lp_';

	private int $lifeTime = 0;

	public function __construct(private ?string $key = null)
	{
	}

	public function setLifeTime(int $lifeTime): self
	{
		$this->lifeTime = $lifeTime;

		return $this;
	}

	public function setFallback(string $className, string $methodName, ...$params): mixed
	{
		if (empty($methodName) || empty($className) || $this->lifeTime === 0)
			$this->forget($this->key);

		if (($cachedValue = $this->get($this->key, $this->lifeTime)) === null) {
			$cachedValue = $this->callMethod($className, $methodName, ...$params);
			$this->put($this->key, $cachedValue, $this->lifeTime);
		}

		return $cachedValue;
	}

	public function get(string $key, ?int $time = null): ?array
	{
		return cache_get_data($this->prefix . $key, $time ?? $this->lifeTime);
	}

	public function put(string $key, ?array $value, ?int $time = null): void
	{
		cache_put_data($this->prefix . $key, $value, $time ?? $this->lifeTime);
	}

	public function forget(string $key): void
	{
		$this->put($key, null);
	}

	public function flush(): void
	{
		clean_cache();
	}

	protected function callMethod(string $className, string $methodName, ...$params): mixed
	{
		if (method_exists($className, $methodName)) {
			return (new $className)->{$methodName}(...$params);
		}

		return null;
	}
}
