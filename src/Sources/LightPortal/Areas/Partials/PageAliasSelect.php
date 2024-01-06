<?php declare(strict_types=1);

/**
 * PageAliasSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Areas\Partials;

final class PageAliasSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_frontpage_alias';
		$params['value'] ??= $this->modSettings['lp_frontpage_alias'] ?? '';
		$params['data'] ??= $this->getEntityList('page');

		$data = [];
		foreach ($params['data'] as $page) {
			$data[] = [
				'label' => $page['title'],
				'value' => $page['alias']
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				search: true,
				maxWidth: "100%",
				placeholder: "' . ($params['hint'] ?? $this->txt['no']) . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				noOptionsText: "' . $this->txt['lp_frontpage_pages_no_items'] . '",
				options: ' . json_encode($data) . ',
				selectedValue: "' . $params['value'] . '"
			});
		</script>';
	}
}
