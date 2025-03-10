<?php declare(strict_types=1);

/**
 * AbstractCustomPageImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Imports;

use Bugo\Compat\{Db, ErrorHandler, Sapi};
use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractCustomPageImport implements ImportInterface, CustomImportInterface
{
	use Helper;

	abstract protected function getItems(array $pages): array;

	protected function run(): void
	{
		if ($this->request()->isEmpty('pages') && $this->request()->hasNot('import_all'))
			return;

		Sapi::setTimeLimit();

		$pages = $this->request('pages') && $this->request()->hasNot('import_all')
			? $this->request('pages')
			: [];

		$results = $titles = $params = $comments = [];
		$items = $this->getItems($pages);

		$this->hook('importPages', [&$items, &$titles, &$params, &$comments]);

		if ($items) {
			$items = array_chunk($items, 100);
			$count = sizeof($items);

			for ($i = 0; $i < $count; $i++) {
				$results = Db::$db->insert('replace',
					'{db_prefix}lp_pages',
					[
						'page_id'      => 'int',
						'author_id'    => 'int',
						'alias'        => 'string-255',
						'description'  => 'string-255',
						'content'      => 'string',
						'type'         => 'string',
						'permissions'  => 'int',
						'status'       => 'int',
						'num_views'    => 'int',
						'num_comments' => 'int',
						'created_at'   => 'int',
						'updated_at'   => 'int',
					],
					$items[$i],
					['page_id'],
					2
				);
			}
		}

		if (empty($results))
			ErrorHandler::fatalLang('lp_import_failed');

		if ($titles) {
			$titles = array_chunk($titles, 100);
			$count  = sizeof($titles);

			for ($i = 0; $i < $count; $i++) {
				Db::$db->insert('replace',
					'{db_prefix}lp_titles',
					[
						'item_id' => 'int',
						'type'    => 'string',
						'lang'    => 'string',
						'title'   => 'string',
					],
					$titles[$i],
					['item_id', 'type', 'lang'],
					2
				);
			}
		}

		if ($params) {
			$params = array_chunk($params, 100);
			$count  = sizeof($params);

			for ($i = 0; $i < $count; $i++) {
				Db::$db->insert('replace',
					'{db_prefix}lp_params',
					[
						'item_id' => 'int',
						'type'    => 'string',
						'name'    => 'string',
						'value'   => 'string',
					],
					$params[$i],
					['item_id', 'type', 'name'],
					2
				);
			}
		}

		if ($comments) {
			$tempCommentArray = [];

			foreach ($comments as $comment) {
				foreach ($comment as $com) {
					$tempCommentArray[] = $com;
				}
			}

			$comments = array_chunk($tempCommentArray, 100);
			$count    = sizeof($comments);

			for ($i = 0; $i < $count; $i++) {
				Db::$db->insert('replace',
					'{db_prefix}lp_comments',
					[
						'id'         => 'int',
						'parent_id'  => 'int',
						'page_id'    => 'int',
						'author_id'  => 'int',
						'message'    => 'string',
						'created_at' => 'int',
					],
					$comments[$i],
					['id', 'page_id'],
					2
				);
			}
		}

		$this->cache()->flush();
	}
}