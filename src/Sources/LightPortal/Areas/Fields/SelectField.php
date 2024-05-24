<?php declare(strict_types=1);

/**
 * SelectField.php
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

if (! defined('SMF'))
	die('No direct access...');

class SelectField extends AbstractField
{
	protected array $options = [];

	public function __construct(string $name, string $label)
	{
		parent::__construct($name, $label);

		$this
			->setType('select')
			->setAttribute('id', $name);
	}

	public function setOptions(array $options): self
	{
		$this->options = $options;

		return $this;
	}

	protected function build(): void
	{
		parent::build();

		foreach ($this->options as $key => $value) {
			Utils::$context['posting_fields'][$this->name]['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key === $this->attributes['value']
			];
		}
	}
}
