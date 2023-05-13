<?php

function template_manage_export_blocks()
{
	global $context, $txt, $language;

	echo '
	<form action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '">
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_area_title'], '</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col">#</th>
					<th scope="col" class="type">
						', $txt['lp_block_note'], ' / ', $txt['lp_title'], '
					</th>
					<th scope="col" class="type">
						', $txt['lp_block_type'], '
					</th>
					<th scope="col" class="placement">
						', $txt['lp_block_placement'], '
					</th>
					<th scope="col" class="actions">
						<input type="checkbox" onclick="invertAll(this, this.form);" checked>
					</th>
				</tr>
			</thead>
			<tbody>';

		$empty = true;
		foreach ($context['lp_current_blocks'] as $placement) {
			if (is_array($placement)) {
				$empty = false;
				break;
			}
		}

		if ($empty)
			$context['lp_current_blocks'] = [];

		if (empty($context['lp_current_blocks'])) {
			echo '
				<tr class="windowbg">
					<td colspan="5" class="centertext">', $txt['lp_no_items'], '</td>
				</tr>';
		} else {
			foreach ($context['lp_current_blocks'] as $placement => $blocks) {
				if (is_array($blocks)) {
					foreach ($blocks as $id => $data) {
						echo '
				<tr class="windowbg', $data['status'] ? ' sticky' : '', '">
					<td class="centertext">
						', $id, '
					</td>
					<td class="type centertext">
						', $data['note'] ?: ($data['title'][$context['user']['language']] ?? $data['title']['english'] ?? $data['title'][$language] ?? ''), '
					</td>
					<td class="type centertext">
						', $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']], '
					</td>
					<td class="placement centertext">
						', $context['lp_block_placements'][$placement] ?? ($txt['unknown'] . ' (' . $placement . ')'), '
					</td>
					<td class="actions centertext">
						<input type="checkbox" value="' . $id . '" name="blocks[]" checked>
					</td>
				</tr>';
					}
				}
			}
		}

		echo '
			</tbody>
		</table>
		<div class="additional_row">
			<input type="hidden">
			<input type="submit" name="export_selection" value="', $txt['lp_export_selection'], '" class="button">
			<input type="submit" name="export_all" value="', $txt['lp_export_all'], '" class="button">
		</div>
	</form>';
}

function template_manage_export_plugins()
{
	global $context, $txt;

	echo '
	<form action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '">
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_area_title'], '</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col">#</th>
					<th scope="col" class="type">
						', $txt['lp_plugin_name'], '
					</th>
					<th scope="col" class="actions">
						<input type="checkbox" onclick="invertAll(this, this.form);" checked>
					</th>
				</tr>
			</thead>
			<tbody>';

		if (empty($context['lp_plugins'])) {
			echo '
				<tr class="windowbg">
					<td colspan="3" class="centertext">', $txt['lp_no_items'], '</td>
				</tr>';
		} else {
			foreach ($context['lp_plugins'] as $id => $name) {
				echo '
				<tr class="windowbg">
					<td class="centertext">
						', $id + 1, '
					</td>
					<td class="name centertext">
						', $name, '
					</td>
					<td class="actions centertext">
						<input type="checkbox" value="' . $name . '" name="plugins[]" checked>
					</td>
				</tr>';
			}
		}

		echo '
			</tbody>
		</table>
		<div class="additional_row">
			<input type="hidden">
			<input type="submit" name="export_selection" value="', $txt['lp_export_selection'], '" class="button">
			<input type="submit" name="export_all" value="', $txt['lp_export_all'], '" class="button">
		</div>
	</form>';
}

function template_manage_import()
{
	global $context, $txt;

	if (! empty($context['import_successful']))
		echo '
	<div class="infobox">', $context['import_successful'], '</div>';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>
	<div class="information">', $context['page_area_info'], '</div>
	<div class="descbox">
		<form action="', $context['canonical_url'], '" method="post" enctype="multipart/form-data">
			<div class="centertext">
				<input type="hidden" name="MAX_FILE_SIZE" value="', $context['max_file_size'], '">
				<input name="import_file" type="file" accept="', $context['lp_file_type'], '">
				<button class="button floatnone" type="submit">', $txt['lp_import_run'], '</button>
			</div>
		</form>
	</div>';
}
