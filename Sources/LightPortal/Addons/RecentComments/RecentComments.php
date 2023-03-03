<?php

/**
 * RecentComments.php
 *
 * @package RecentComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 3.03.23
 */

namespace Bugo\LightPortal\Addons\RecentComments;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class RecentComments extends Plugin
{
	public string $icon = 'fas fa-comments';

	public function blockOptions(array &$options)
	{
		$options['recent_comments']['no_content_class'] = true;

		$options['recent_comments']['parameters'] = [
			'num_comments' => 10,
			'show_rating'  => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'recent_comments')
			return;

		$parameters = array(
			'num_comments' => FILTER_VALIDATE_INT,
			'show_rating'  => FILTER_VALIDATE_BOOLEAN,
		);
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'recent_comments')
			return;

		$this->context['posting_fields']['num_comments']['label']['text'] = $this->txt['lp_recent_comments']['num_comments'];
		$this->context['posting_fields']['num_comments']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_comments',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_comments']
			]
		];

		$this->context['posting_fields']['show_rating']['label']['text'] = $this->txt['lp_recent_comments']['show_rating'];
		$this->context['posting_fields']['show_rating']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_rating',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_rating']
			]
		];
	}

	public function getData(int $num_comments): array
	{
		if (empty($num_comments))
			return [];

		$request = $this->smcFunc['db_query']('', '
			SELECT DISTINCT com.id, com.page_id, com.message, com.created_at, p.alias, COALESCE(mem.real_name, {string:guest}) AS author_name,
			(SELECT SUM(r.value) FROM {db_prefix}lp_ratings AS r WHERE com.id = r.content_id) AS rating
			FROM {db_prefix}lp_comments AS com
				INNER JOIN (
					SELECT lt.page_id AS page_id, MAX(lt.created_at) AS created_at
					FROM {db_prefix}lp_comments AS lt
					GROUP BY lt.page_id
				) AS latest_comments ON (com.page_id = latest_comments.page_id AND com.created_at = latest_comments.created_at)
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = com.page_id)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = com.author_id)
				LEFT JOIN {db_prefix}lp_params AS par ON (par.item_id = com.page_id AND par.type = {literal:page} AND par.name = {literal:allow_comments})
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND par.value > 0
			ORDER BY com.created_at DESC
			LIMIT {int:limit}',
			array(
				'guest'        => $this->txt['guest_title'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'limit'        => $num_comments
			)
		);

		$comments = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$this->censorText($row['message']);

			$comments[$row['id']] = array(
				'link'        => LP_PAGE_URL . $row['alias'],
				'message'     => $this->getShortenText($this->parseBbc($row['message']), 20),
				'created_at'  => (int) $row['created_at'],
				'author_name' => $row['author_name'],
				'rating'      => (int) $row['rating'],
			);
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $comments;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'recent_comments')
			return;

		$comments = $this->cache('recent_comments_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', (int) $parameters['num_comments']);

		if (empty($comments))
			return;

		echo '
		<ul class="recent_comments noup">';

		foreach ($comments as $comment) {
			echo '
			<li class="windowbg">
				<a href="', $comment['link'], '">', $comment['message'], '</a>', empty($parameters['show_rating']) ? '' : (empty($comment['rating']) ? '' : ' <span class="amt floatright">' . $comment['rating'] . '</span>'), '
				<br><span class="smalltext">', $this->txt['by'], ' ', $comment['author_name'], '</span>
				<br><span class="smalltext">', $this->getFriendlyTime($comment['created_at']), '</span>
			</li>';
		}

		echo '
		</ul>';
	}
}
