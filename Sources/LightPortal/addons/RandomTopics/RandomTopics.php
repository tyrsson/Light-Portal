<?php

/**
 * RandomTopics
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\RandomTopics;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class RandomTopics extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-random';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['random_topics']['no_content_class'] = true;

		$options['random_topics']['parameters']['num_topics'] = 10;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'random_topics')
			return;

		$parameters['num_topics'] = FILTER_VALIDATE_INT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'random_topics')
			return;

		$context['posting_fields']['num_topics']['label']['text'] = $txt['lp_random_topics']['num_topics'];
		$context['posting_fields']['num_topics']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'num_topics',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['num_topics']
			)
		);
	}

	/**
	 * Get the list of random forum topics
	 *
	 * Получаем список случайных тем форума
	 *
	 * @param int $num_topics
	 * @return array
	 */
	public function getData($num_topics)
	{
		global $modSettings, $user_info, $db_type, $smcFunc, $context, $settings, $scripturl;

		if (empty($num_topics))
			return [];

		$ignore_boards = !empty($modSettings['recycle_board']) ? [(int) $modSettings['recycle_board']] : [];

		if (!empty($modSettings['allow_ignore_boards'])) {
			$ignore_boards = array_unique(array_merge($ignore_boards, $user_info['ignoreboards']));
		}

		if ($db_type == 'postgresql') {
			$request = $smcFunc['db_query']('', '
				WITH RECURSIVE r AS (
					WITH b AS (
						SELECT min(t.id_topic), (
							SELECT t.id_topic FROM {db_prefix}topics AS t
							WHERE t.approved = {int:is_approved}' . (!empty($ignore_boards) ? '
								AND t.id_board NOT IN ({array_int:ignore_boards})' : '') . '
							ORDER BY t.id_topic DESC
							LIMIT 1 OFFSET {int:limit} - 1
						) max
						FROM {db_prefix}topics AS t
						WHERE t.approved = {int:is_approved}' . (!empty($ignore_boards) ? '
							AND t.id_board NOT IN ({array_int:ignore_boards})' : '') . '
					)
					(
						SELECT t.id_topic, min, max, array[]::integer[] || t.id_topic AS a, 0 AS n
						FROM {db_prefix}topics AS t, b
						WHERE t.id_topic >= min + ((max - min) * random())::int' . (!empty($ignore_boards) ? '
							AND t.id_board NOT IN ({array_int:ignore_boards})' : '') . '
							AND	t.approved = {int:is_approved}
						LIMIT 1
					) UNION ALL (
						SELECT t.id_topic, min, max, a || t.id_topic, r.n + 1 AS n
						FROM {db_prefix}topics AS t, r
						WHERE t.id_topic >= min + ((max - min) * random())::int
							AND t.id_topic <> all(a)
							AND r.n + 1 < {int:limit}' . (!empty($ignore_boards) ? '
							AND t.id_board NOT IN ({array_int:ignore_boards})' : '') . '
							AND t.approved = {int:is_approved}
						LIMIT 1
					)
				)
				SELECT t.id_topic
				FROM {db_prefix}topics AS t, r
				WHERE r.id_topic = t.id_topic',
				array(
					'is_approved'   => 1,
					'ignore_boards' => $ignore_boards,
					'limit'         => $num_topics
				)
			);

			$topic_ids = [];
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$topic_ids[] = $row['id_topic'];

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			if (empty($topic_ids))
				return $this->getData($num_topics - 1);

			$request = $smcFunc['db_query']('', '
				SELECT
					mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg,
					COALESCE(mem.real_name, mf.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read' : '
					COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read') . ', mf.icon
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
					INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
					LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (t.id_board = lmr.id_board AND lmr.id_member = {int:current_member})') . '
				WHERE t.id_topic IN ({array_int:topic_ids})',
				array(
					'current_member' => $user_info['id'],
					'topic_ids'      => $topic_ids
				)
			);
		} else {
			$request = $smcFunc['db_query']('', '
				SELECT
					mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg,
					COALESCE(mem.real_name, mf.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read' : '
					COALESCE(lt.id_msg, lmr.id_msg, 0) >= ml.id_msg_modified AS is_read') . ', mf.icon
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (t.id_last_msg = ml.id_msg)
					INNER JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
					LEFT JOIN {db_prefix}members AS mem ON (mf.id_member = mem.id_member)' . ($user_info['is_guest'] ? '' : '
					LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:current_member})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (t.id_board = lmr.id_board AND lmr.id_member = {int:current_member})') . '
				WHERE t.approved = {int:is_approved}
					AND t.id_board NOT IN ({array_int:ignore_boards})
				ORDER BY RAND()
				LIMIT {int:limit}',
				array(
					'current_member' => $user_info['id'],
					'is_approved'    => 1,
					'ignore_boards'  => $ignore_boards,
					'limit'          => $num_topics
				)
			);
		}

		$icon_sources = [];
		foreach ($context['stable_icons'] as $icon)
			$icon_sources[$icon] = 'images_url';

		$topics = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!empty($modSettings['messageIconChecks_enable']) && !isset($icon_sources[$row['icon']])) {
				$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';
			} elseif (!isset($icon_sources[$row['icon']])) {
				$icon_sources[$row['icon']] = 'images_url';
			}

			$topics[] = array(
				'poster' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>',
				'time'   => $row['poster_time'],
				'link'   => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new" rel="nofollow">' . $row['subject'] . '</a>',
				'is_new' => empty($row['is_read']),
				'icon'   => '<img class="centericon" src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" alt="' . $row['icon'] . '">'
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $topics;
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $txt;

		if ($type !== 'random_topics')
			return;

		$random_topics = Helpers::cache(
			'random_topics_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$cache_time,
			$parameters['num_topics']
		);

		if (!empty($random_topics)) {
			ob_start();

			echo '
			<ul class="random_topics noup">';

			foreach ($random_topics as $topic) {
				echo '
				<li class="windowbg">', ($topic['is_new'] ? '
					<span class="new_posts">' . $txt['new'] . '</span>' : ''), $topic['icon'], ' ', $topic['link'], '
					<br><span class="smalltext">', $txt['by'], ' ', $topic['poster'], '</span>
					<br><span class="smalltext">', Helpers::getFriendlyTime($topic['time']), '</span>
				</li>';
			}

			echo '
			</ul>';

			$content = ob_get_clean();
		}
	}
}
