<?php

/**
 * The page creation/editing template
 *
 * Шаблон создания/редактирования страницы
 *
 * @return void
 */
function template_page_post()
{
	global $context, $txt, $settings;

	if (isset($context['preview_content']) && empty($context['post_errors'])) {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['preview_title'], '</h3>
	</div>
	<div class="roundframe noup page_', $context['lp_page']['type'], '">
		', $context['preview_content'], '
	</div>';
	} else {
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>';
	}

	if (!empty($context['post_errors'])) {
		echo '
	<div class="errorbox">
		<ul>';

		foreach ($context['post_errors'] as $error) {
			echo '
			<li>', $error, '</li>';
		}

		echo '
		</ul>
	</div>';
	}

	$fields = $context['posting_fields'];

	echo '
	<form id="lp_post" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);" x-data>
		<div class="roundframe', isset($context['preview_content']) ? '' : ' noup', '" @change="page.change($refs)">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd">', $txt['lp_tab_content'], '</label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd">', $txt['lp_tab_seo'], '</label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd">', $txt['lp_tab_tuning'], '</label>
				<section id="content-tab1" class="bg even">
					', template_post_tab($fields);

	if ($context['lp_page']['type'] == 'bbc') {
		echo '
					<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>';
	}

	echo '
				</section>
				<section id="content-tab2" class="bg even">
					', template_post_tab($fields, 'seo'), '
				</section>
				<section id="content-tab3" class="bg even">
					', template_post_tab($fields, 'tuning'), '
				</section>
			</div>
			<br class="clear">
			<div class="centertext">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">';

	if (!empty($context['lp_page']['id'])) {
		echo '
				<button type="submit" class="button active" name="remove" style="float: left">', $txt['remove'], '</button>';
	}

	echo '
				<button type="submit" class="button" name="preview" @click="page.post($el)">', $txt['preview'], '</button>
				<button type="submit" class="button" name="save" @click="page.post($el)">', $txt['save'], '</button>
			</div>
		</div>
	</form>
	<script async defer src="https://cdn.jsdelivr.net/npm/transliteration@2/dist/browser/bundle.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/selectize@0.12/dist/js/standalone/selectize.min.js"></script>
	<script src="', $settings['default_theme_url'], '/scripts/suggest.js"></script>
	<script>
		jQuery(document).ready(function ($) {
			$("#keywords").selectize({
				plugins: ["remove_button"],
				delimiter: ",",
				persist: false,
				create: function(input) {
					return {
						value: input,
						text: input
					}
				},
				render: {
					option_create: function(data, escape) {
						return `<div class="create">` + this.$input.data(\'label\') + ` <strong>` + escape(data.input) + `</strong>&hellip;</div>`;
					}
				}
			});
		});
		let oMemberSuggest = new smc_AutoSuggest({
			sSelf: "oMemberSuggest",
			sSessionId: smf_session_id,
			sSessionVar: smf_session_var,
			sControlId: "page_author",
			sSearchType: "member",
			bItemList: false
		});
	</script>';
}
