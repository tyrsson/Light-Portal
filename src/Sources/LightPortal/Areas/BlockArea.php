<?php declare(strict_types=1);

/**
 * BlockArea.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas;

use Bugo\Compat\{Config, ErrorHandler, Lang, Security, Theme, Utils};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, CustomField, TextareaField, TextField, UrlField};
use Bugo\LightPortal\Areas\Partials\{AreaSelect, ContentClassSelect, IconSelect};
use Bugo\LightPortal\Areas\Partials\{PermissionSelect, PlacementSelect, TitleClassSelect};
use Bugo\LightPortal\Areas\Validators\BlockValidator;
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Models\BlockModel;
use Bugo\LightPortal\Repositories\BlockRepository;
use Bugo\LightPortal\Utils\Content;

if (! defined('SMF'))
	die('No direct access...');

final class BlockArea
{
	use Area;
	use Helper;

	public const TAB_CONTENT = 'content';

	public const TAB_ACCESS = 'access_placement';
	
	public const TAB_APPEARANCE = 'appearance';

	public const TAB_TUNING = 'tuning';

	private BlockRepository $repository;

	public function __construct()
	{
		$this->repository = new BlockRepository;
	}

	public function main(): void
	{
		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'manage_blocks';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_manage'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_manage_description'],
		];

		$this->doActions();

		Utils::$context['lp_current_blocks'] = $this->repository->getAll();
	}

	public function add(): void
	{
		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'block_add';

		Utils::$context['page_title']  = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_add_title'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=add';

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_add_description'],
		];

		Lang::$txt['lp_blocks_add_instruction'] = sprintf(
			Lang::$txt['lp_blocks_add_instruction'], Config::$scripturl . '?action=admin;area=lp_plugins'
		);

		Utils::$context['current_block']['placement'] = $this->request('placement', 'top');

		$this->prepareBlockList();

		$json = $this->request()->json();
		$type = $json['add_block'] ?? $this->request('add_block', '') ?? '';

		if (empty($type) && empty($json['search']))
			return;

		Utils::$context['current_block']['type'] = $type;

		$this->prepareForumLanguages();
		$this->validateData();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData();

		Utils::$context['sub_template'] = 'block_post';
	}

	public function edit(): void
	{
		$item = (int) ($this->request('block_id') ?: $this->request('id'));

		Theme::loadTemplate('LightPortal/ManageBlocks');

		Utils::$context['sub_template'] = 'block_post';

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt['lp_blocks_edit_title'];

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt['lp_blocks_edit_description']
		];

		$this->prepareForumLanguages();

		Utils::$context['current_block'] = $this->repository->getData($item);

		if (empty(Utils::$context['current_block'])) {
			ErrorHandler::fatalLang('lp_block_not_found', status: 404);
		}

		if ($this->request()->has('remove')) {
			$this->repository->remove([$item]);

			Utils::redirectexit('action=admin;area=lp_blocks;sa=main');
		}

		$this->validateData();

		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . Utils::$context['lp_block']['id'];

		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();

		$this->repository->setData(Utils::$context['lp_block']['id']);
	}

	private function doActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		$data = $this->request()->json();

		if (isset($data['del_item']))
			$this->repository->remove([(int) $data['del_item']]);

		if (isset($data['clone_block']))
			$this->makeCopy((int) $data['clone_block']);

		if (isset($data['toggle_item']))
			$this->repository->toggleStatus([(int) $data['toggle_item']]);

		$this->repository->updatePriority();

		$this->cache()->flush();

		exit;
	}

	private function makeCopy(int $item): void
	{
		if ($item === 0)
			return;

		$this->request()->put('clone', true);

		$result = [
			'success' => false,
		];

		Utils::$context['lp_block']       = $this->repository->getData($item);
		Utils::$context['lp_block']['id'] = $this->repository->setData();

		if (Utils::$context['lp_block']['id']) {
			$result = [
				'id'      => Utils::$context['lp_block']['id'],
				'success' => true,
			];
		}

		$this->cache()->forget('active_blocks');

		exit(json_encode($result));
	}

	private function getParams(): array
	{
		$baseParams = [
			'hide_header'      => false,
			'no_content_class' => false,
		];

		if (in_array(Utils::$context['current_block']['type'], array_keys(Utils::$context['lp_content_types']))) {
			$baseParams['content'] = true;
		}

		$params = [];

		$this->hook('prepareBlockParams', [&$params]);

		return array_merge($baseParams, $params);
	}

	private function validateData(): void
	{
		[$postData, $parameters] = (new BlockValidator())->validate();

		$options = $this->getParams();

		$blockOptions = Utils::$context['current_block']['options'] ?? $options;

		$type = Utils::$context['current_block']['type'];
		Utils::$context['current_block']['icon'] ??= Utils::$context['lp_loaded_addons'][$type]['icon'] ?? '';

		$block = new BlockModel($postData, Utils::$context['current_block']);
		$block->icon = $block->icon === 'undefined' ? '' : $block->icon;
		$block->permissions = empty(Utils::$context['user']['is_admin']) ? 4 : $block->permissions;
		$block->contentClass = empty($block->options['no_content_class']) ? $block->contentClass : '';
		$block->titles = Utils::$context['current_block']['titles'] ?? [];
		$block->options = $options;

		foreach (Utils::$context['lp_languages'] as $lang) {
			$block->titles[$lang['filename']] = $postData['title_' . $lang['filename']]
				?? $block->titles[$lang['filename']]
				?? '';
		}

		$this->cleanBbcode($block->titles);

		foreach ($block->options as $option => $value) {
			if (
				isset($parameters[$option])
				&& isset($postData['parameters'])
				&& ! isset($postData['parameters'][$option])
			) {
				$postData['parameters'][$option] = 0;

				if ($option === 'no_content_class')
					$postData['parameters'][$option] = $value;

				if ($parameters[$option] === FILTER_DEFAULT)
					$postData['parameters'][$option] = '';

				if (is_array($parameters[$option]) && $parameters[$option]['flags'] === FILTER_REQUIRE_ARRAY)
					$postData['parameters'][$option] = [];
			}

			$block->options[$option] = $postData['parameters'][$option] ?? $blockOptions[$option] ?? $value;
		}

		Utils::$context['lp_block'] = $block->toArray();
	}

	private function prepareFormFields(): void
	{
		$this->prepareTitleFields('block', false);

		TextField::make('note', Lang::$txt['lp_block_note'])
			->setTab(self::TAB_CONTENT)
			->setAttribute('maxlength', 255)
			->setValue(Utils::$context['lp_block']['note']);

		if (isset(Utils::$context['lp_block']['options']['content'])) {
			if (Utils::$context['lp_block']['type'] !== 'bbc') {
				TextareaField::make('content', Lang::$txt['lp_content'])
					->setTab(self::TAB_CONTENT)
					->setValue($this->prepareContent(Utils::$context['lp_block']));
			} else {
				$this->createBbcEditor(Utils::$context['lp_block']['content']);
			}
		}

		CustomField::make('placement', Lang::$txt['lp_block_placement'])
			->setTab(self::TAB_ACCESS)
			->setValue(static fn() => new PlacementSelect());

		CustomField::make('permissions', Lang::$txt['edit_permissions'])
			->setTab(self::TAB_ACCESS)
			->setValue(static fn() => new PermissionSelect(), [
				'type' => 'block'
			]);

		CustomField::make('areas', Lang::$txt['lp_block_areas'])
			->setTab(self::TAB_ACCESS)
			->setAfter($this->getAreasInfo())
			->setValue(static fn() => new AreaSelect());

		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(self::TAB_APPEARANCE)
			->setValue(static fn() => new IconSelect());

		CustomField::make('title_class', Lang::$txt['lp_block_title_class'])
			->setTab(self::TAB_APPEARANCE)
			->setValue(static fn() => new TitleClassSelect());

		if (empty(Utils::$context['lp_block']['options']['no_content_class'])) {
			CustomField::make('content_class', Lang::$txt['lp_block_content_class'])
				->setTab(self::TAB_APPEARANCE)
				->setValue(static fn() => new ContentClassSelect());
		}

		CheckboxField::make('hide_header', Lang::$txt['lp_block_hide_header'])
			->setValue(Utils::$context['lp_block']['options']['hide_header']);

		if (isset(Utils::$context['lp_block']['options']['link_in_title'])) {
			UrlField::make('link_in_title', Lang::$txt['lp_block_link_in_title'])
				->setValue(Utils::$context['lp_block']['options']['link_in_title']);
		}

		Utils::$context['lp_block_tab_appearance'] = true;

		$this->hook('prepareBlockFields');

		$this->preparePostFields();
	}

	private function getAreasInfo(): string
	{
		$example_areas = [
			'custom_action',
			'!custom_action',
			LP_PAGE_PARAM . '=alias',
			'board=id',
			'board=1-3',
			'board=3|7',
			'topic=id',
			'topic=1-3',
			'topic=3|7',
		];

		Lang::$txt['lp_block_areas_values'][0] = sprintf(Lang::$txt['lp_block_areas_values'][0], 'pm,agreement,search');

		Utils::$context['lp_possible_areas'] = array_combine($example_areas, Lang::$txt['lp_block_areas_values']);

		ob_start();

		template_show_areas_info();

		return ob_get_clean();
	}

	private function prepareEditor(): void
	{
		$this->hook('prepareEditor', [Utils::$context['lp_block']]);
	}

	private function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		$this->cache()->flush();

		Security::checkSubmitOnce('free');

		Utils::$context['preview_title']   = Utils::$context['lp_block']['titles'][Utils::$context['user']['language']] ?? '';
		Utils::$context['preview_content'] = Utils::htmlspecialchars(Utils::$context['lp_block']['content'], ENT_QUOTES);

		$this->cleanBbcode(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_content']);

		Utils::$context['preview_content'] = empty(Utils::$context['preview_content'])
			? Content::prepare(Utils::$context['lp_block']['type'], Utils::$context['lp_block']['id'], 0, Utils::$context['lp_block']['options'] ?? [])
			: Content::parse(Utils::$context['preview_content'], Utils::$context['lp_block']['type']);

		Utils::$context['page_title']    = Lang::$txt['preview'] . (Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : '');
		Utils::$context['preview_title'] = $this->getPreviewTitle($this->getIcon(Utils::$context['lp_block']['icon']));

		if (! empty(Utils::$context['lp_block']['options']['hide_header'])) {
			Utils::$context['preview_title'] = Utils::$context['lp_block']['title_class'] = '';
		}
	}

	private function prepareBlockList(): void
	{
		$plugins = array_merge(Utils::$context['lp_enabled_plugins'], array_keys($this->getContentTypes()));

		Utils::$context['lp_loaded_addons'] = array_merge(Utils::$context['lp_loaded_addons'] ?? [], $this->getDefaultTypes());

		Utils::$context['lp_all_blocks'] = [];
		foreach ($plugins as $addon) {
			$addon = $this->getSnakeName($addon);

			// We need blocks only
			if (! isset(Lang::$txt['lp_' . $addon]['title']) || isset(Utils::$context['lp_all_blocks'][$addon]))
				continue;

			Utils::$context['lp_all_blocks'][$addon] = [
				'type'  => $addon,
				'icon'  => Utils::$context['lp_loaded_addons'][$addon]['icon'],
				'title' => Lang::$txt['lp_' . $addon]['title'],
				'desc'  => Lang::$txt['lp_' . $addon]['block_desc'] ?? Lang::$txt['lp_' . $addon]['description']
			];
		}

		$titles = array_column(Utils::$context['lp_all_blocks'], 'title');
		array_multisort($titles, SORT_ASC, Utils::$context['lp_all_blocks']);
	}
}
