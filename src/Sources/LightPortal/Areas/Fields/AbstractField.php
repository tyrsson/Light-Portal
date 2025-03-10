<?php declare(strict_types=1);

/**
 * AbstractField.php
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

use Bugo\Compat\Utils;
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractField
{
	use Helper;

	protected string $tab = 'tuning';

	protected string $name;

	protected string $label;

	protected string $type;

	protected string $after = '';

	protected array $attributes = [];

	public function __destruct()
	{
		$this->build();
	}

	public static function make(string $name, string $label): static
	{
		return new static($name, $label);
	}

	public function setTab(string $tab): self
	{
		$this->tab = $tab;

		return $this;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function setLabel(string $label): self
	{
		$this->label = $label;

		return $this;
	}

	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	public function setAfter(string $after): self
	{
		$this->after = $after;

		return $this;
	}

	public function setValue(mixed $value, ...$params): self
	{
		if (is_callable($value) && is_object($value)) {
			$this->setAttribute('value', $value()(...$params));
		} else {
			$this->setAttribute('value', $value);
		}

		return $this;
	}

	public function setAttribute(string $name, mixed $value): self
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	public function required(): self
	{
		return $this->setAttribute('required', true);
	}

	public function placeholder(string $text): self
	{
		return $this->setAttribute('placeholder', $text);
	}

	protected function build(): void
	{
		Utils::$context['posting_fields'][$this->name]['label']['text'] = $this->label;
		Utils::$context['posting_fields'][$this->name]['input'] = [
			'type'       => $this->type,
			'after'      => $this->after,
			'tab'        => $this->tab,
			'attributes' => $this->attributes,
		];
	}
}
