<?php

namespace Bugo\LightPortal\Front;

use Bugo\LightPortal\Helpers;
use Bugo\LightPortal\Page;
use Bugo\LightPortal\Subs;

/**
 * PageArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class PageArticle extends AbstractArticle
{
	/**
	 * Initialize class properties
	 *
	 * Инициализируем свойства класса
	 *
	 * @return void
	 */
	public function init()
	{
		global $modSettings;

		$this->selected_categories = !empty($modSettings['lp_frontpage_categories']) ? explode(',', $modSettings['lp_frontpage_categories']) : [];

		$this->params = [
			'status'              => Page::STATUS_ACTIVE,
			'current_time'        => time(),
			'permissions'         => Helpers::getPermissions(),
			'selected_categories' => $this->selected_categories
		];

		$this->orders = [
			'CASE WHEN (SELECT lp_com.created_at FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id LIMIT 1) > 0 THEN 0 ELSE 1 END, comment_date DESC',
			'p.created_at DESC',
			'p.created_at',
			'date DESC'
		];

		Subs::runAddons('frontPages', array(&$this->columns, &$this->tables, &$this->wheres, &$this->params, &$this->orders));
	}

	/**
	 * Get active pages of the portal
	 *
	 * Получаем активные страницы портала
	 *
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getData(int $start, int $limit)
	{
		global $user_info, $smcFunc, $modSettings, $scripturl;

		if (empty($this->selected_categories))
			return [];

		if (($pages = Helpers::cache()->get('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, LP_CACHE_TIME)) === null) {
			$titles = Helpers::getAllTitles();

			$this->params += array(
				'start' => $start,
				'limit' => $limit
			);

			$request = $smcFunc['db_query']('', '
				SELECT
					p.page_id, p.category_id, p.author_id, p.alias, p.content, p.description, p.type, p.status, p.num_views, p.num_comments, p.created_at,
					GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name,
					(SELECT lp_com.created_at FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_date, (SELECT lp_com.author_id FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_author_id, (SELECT real_name FROM {db_prefix}lp_comments AS lp_com LEFT JOIN {db_prefix}members ON (lp_com.author_id = id_member) WHERE lp_com.page_id = p.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_author_name, (SELECT lp_com.message FROM {db_prefix}lp_comments AS lp_com WHERE p.page_id = lp_com.page_id ORDER BY lp_com.created_at DESC LIMIT 1) AS comment_message' . (!empty($this->columns) ? ', ' . implode(', ', $this->columns) : '') . '
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)' . (!empty($this->tables) ? '
					' . implode("\n\t\t\t\t\t", $this->tables) : '') . '
				WHERE p.status = {int:status}
					AND p.created_at <= {int:current_time}
					AND p.permissions IN ({array_int:permissions})
					AND p.category_id IN ({array_int:selected_categories})' . (!empty($this->wheres) ? '
					' . implode("\n\t\t\t\t\t", $this->wheres) : '') . '
				ORDER BY ' . (!empty($modSettings['lp_frontpage_order_by_num_replies']) ? 'num_comments DESC, ' : '') . $this->orders[$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT {int:start}, {int:limit}',
				$this->params
			);

			$pages = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				Helpers::parseContent($row['content'], $row['type']);

				$image = null;
				if (!empty($modSettings['lp_show_images_in_articles'])) {
					$first_post_image = preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $row['content'], $value);
					$image = $first_post_image ? array_pop($value) : null;
				}

				if (!isset($pages[$row['page_id']])) {
					$pages[$row['page_id']] = array(
						'id'            => $row['page_id'],
						'category_name' => !empty($row['category_id']) ? Helpers::getAllCategories()[$row['category_id']]['name'] : '',
						'category_link' => !empty($row['category_id']) ? $scripturl . '?action=portal;sa=categories;id=' . $row['category_id'] : '',
						'author_id'     => $author_id = empty($modSettings['lp_frontpage_article_sorting']) && !empty($row['num_comments']) ? $row['comment_author_id'] : $row['author_id'],
						'author_link'   => $scripturl . '?action=profile;u=' . $author_id,
						'author_name'   => empty($modSettings['lp_frontpage_article_sorting']) && !empty($row['num_comments']) ? $row['comment_author_name'] : $row['author_name'],
						'type'          => $row['type'],
						'num_views'     => $row['num_views'],
						'num_comments'  => $row['num_comments'],
						'date'          => empty($modSettings['lp_frontpage_article_sorting']) && !empty($row['comment_date']) ? $row['comment_date'] : $row['created_at'],
						'is_new'        => $user_info['last_login'] < $row['date'] && $row['author_id'] != $user_info['id'],
						'link'          => $scripturl . '?page=' . $row['alias'],
						'image'         => $image,
						'can_edit'      => $user_info['is_admin'] || (allowedTo('light_portal_manage_own_pages') && $row['author_id'] == $user_info['id'])
					);

					$pages[$row['page_id']]['teaser'] = Helpers::getTeaser(empty($modSettings['lp_frontpage_article_sorting']) && !empty($row['num_comments']) ? strip_tags(parse_bbc($row['comment_message'])) : ($row['description'] ?: strip_tags($row['content'])));

					if (!empty($modSettings['lp_frontpage_article_sorting']) && $modSettings['lp_frontpage_article_sorting'] == 3)
						$pages[$row['page_id']]['date'] = $row['date'];
				}

				$pages[$row['page_id']]['title'] = $titles[$row['page_id']];

				Subs::runAddons('frontPagesOutput', array(&$pages, $row));
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_' . $start . '_' . $limit, $pages, LP_CACHE_TIME);
		}

		return $pages;
	}

	/**
	 * Get count of active pages
	 *
	 * Получаем количество активных страниц
	 *
	 * @return int
	 */
	public function getTotalCount()
	{
		global $user_info, $smcFunc;

		if (empty($this->selected_categories))
			return 0;

		if (($num_pages = Helpers::cache()->get('articles_u' . $user_info['id'] . '_total', LP_CACHE_TIME)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(p.page_id)
				FROM {db_prefix}lp_pages AS p' . (!empty($this->tables) ? '
					' . implode("\n\t\t\t\t\t", $this->tables) : '') . '
				WHERE p.status = {int:status}
					AND p.created_at <= {int:current_time}
					AND p.permissions IN ({array_int:permissions})
					AND p.category_id IN ({array_int:selected_categories})' . (!empty($this->wheres) ? '
					' . implode("\n\t\t\t\t\t", $this->wheres) : ''),
				$this->params
			);

			[$num_pages] = $smcFunc['db_fetch_row']($request);

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('articles_u' . $user_info['id'] . '_total', $num_pages, LP_CACHE_TIME);
		}

		return (int) $num_pages;
	}
}
