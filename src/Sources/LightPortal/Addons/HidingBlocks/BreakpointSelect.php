<?php declare(strict_types=1);

/**
 * BreakpointSelect.php
 *
 * @package HidingBlocks (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Addons\HidingBlocks;

use Bugo\Compat\{Lang, Utils};
use Bugo\LightPortal\Areas\Partials\AbstractPartial;

final class BreakpointSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$currentBreakpoints = Utils::$context['lp_block']['options']['hidden_breakpoints'] ?? [];
		$currentBreakpoints = is_array($currentBreakpoints)
			? $currentBreakpoints
			: explode(',', $currentBreakpoints);

		$breakpoints = array_combine(
			['xs', 'sm', 'md', 'lg', 'xl'], Lang::$txt['lp_hiding_blocks']['hidden_breakpoints_set']
		);

		$data = $items = [];

		foreach ($breakpoints as $bp => $name) {
			$data[] = '{label: "' . $name . '", value: "' . $bp . '"}';

			if (in_array($bp, $currentBreakpoints)) {
				$items[] = Utils::escapeJavaScript($bp);
			}
		}

		return /** @lang text */ '
		<div id="hidden_breakpoints" name="hidden_breakpoints"></div>
		<script>
			VirtualSelect.init({
				ele: "#hidden_breakpoints",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				showValueAsTags: true,
				placeholder: "' . Lang::$txt['lp_hiding_blocks']['hidden_breakpoints_subtext'] . '",
				clearButtonText: "' . Lang::$txt['remove'] . '",
				selectAllText: "' . Lang::$txt['check_all'] . '",
				multiple: true,
				search: false,
				options: [' . implode(',', $data) . '],
				selectedValue: [' . implode(',', $items) . ']
			});
		</script>';
	}
}
