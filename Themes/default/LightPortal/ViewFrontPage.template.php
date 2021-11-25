<?php

/**
 * Default template
 *
 * Шаблон по умолчанию
 *
 * @return void
 */
function template_empty()
{
	global $txt;

	echo '
	<div class="infobox">', $txt['lp_no_items'], '</div>';
}

/**
 * Wrong template
 *
 * Неверный шаблон
 *
 * @return void
 */
function template_wrong_template()
{
	global $txt;

	echo '
	<div class="errorbox">', $txt['lp_wrong_template'], '</div>';
}

/**
 * Default template view for frontpage articles
 *
 * Дефолтный шаблон отображения статей на главной
 *
 * @return void
 */
function template_show_articles()
{
	global $context, $modSettings, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe', $article['css_class'] ?? '', '">';

		if (!empty($article['image'])) {
			if ($article['is_new']) {
				echo '
					<div class="new_hover">
						<div class="new_icon">
							<span class="new_posts">', $txt['new'], '</span>
						</div>
					</div>';
			}

			if ($article['can_edit']) {
				echo '
					<div class="info_hover">
						<div class="edit_icon">
							<a href="', $article['edit_link'], '">
								<i class="fas fa-edit" title="', $txt['edit'], '"></i>
							</a>
						</div>
					</div>';
			}

			echo '
				<div class="card_img"></div>
				<a href="', $article['link'], '">
					<div class="card_img_hover" style="background-image: url(\'', $article['image'], '\')"></div>
				</a>';
		}

		echo '
				<div class="card_info">
					<span class="card_date smalltext">';

		if (!empty($article['section']['name'])) {
			echo '
						<a class="floatleft" href="', $article['section']['link'], '"><i class="far fa-list-alt"></i> ', $article['section']['name'], '</a>';
		}

		if ($article['is_new'] && empty($article['image'])) {
			echo '
						&nbsp;<span class="new_posts">', $txt['new'], '</span>';
		}


		if (!empty($article['datetime'])) {
			echo '
						<time class="floatright" datetime="', $article['datetime'], '"><i class="fas fa-clock"></i> ', $article['date'], '</time>';
		}

		echo '
					</span>
					<h3>
						<a href="', $article['msg_link'], '">', $article['title'], '</a>
					</h3>';

		if (!empty($article['teaser'])) {
			echo '
					<p>', $article['teaser'], '</p>';
		}

		echo '
					<div>';

		if (!empty($article['category'])) {
			echo '
						<span class="card_author"><i class="fas fa-list-alt"></i> ', $article['category'], '</span>';
		}

		if (!empty($modSettings['lp_show_author']) && !empty($article['author'])) {
			if (!empty($article['author']['id']) && !empty($article['author']['name'])) {
				echo '
						<a href="', $article['author']['link'], '" class="card_author"><i class="fas fa-user"></i> ', $article['author']['name'], '</a>';
			} else {
				echo '
						<span class="card_author">', $txt['guest_title'], '</span>';
			}
		}

		if (!empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatright">';

			if (!empty($article['views'])) {
				echo '
							<i class="fas fa-eye" title="', $article['views']['title'], '"></i> ', $article['views']['num'];
			}

			if (!empty($article['views']['after']))
				echo $article['views']['after'];

			if (!empty($article['is_redirect'])) {
				echo '
							<i class="fas fa-directions"></i>';
			} elseif (!empty($article['replies']['num'])) {
				echo '
							<i class="fas fa-comment" title="', $article['replies']['title'], '"></i> ', $article['replies']['num'];
			}

			if (!empty($article['replies']['after']))
				echo $article['replies']['after'];

			echo '
						</span>';
		}

		echo '
					</div>
				</div>
			</article>
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 *
 * @return void
 */
function template_show_articles_alt()
{
	global $context, $txt, $modSettings;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_alt_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">
			<article class="roundframe">
				<header>
					<div class="title_bar">
						<h3>
							<a href="', $article['msg_link'], '">', $article['title'], '</a>', $article['is_new'] ? (' <span class="new_posts">' . $txt['new'] . '</span>') : '', '
						</h3>
					</div>
					<div>';

		if (!empty($modSettings['lp_show_num_views_and_comments'])) {
			echo '
						<span class="floatleft">';

			if (!empty($article['views'])) {
				echo '
							<i class="fas fa-eye" title="', $article['views']['title'], '"></i> ', $article['views']['num'];
			}

			if (!empty($article['views']['after']))
				echo $article['views']['after'];

			if (!empty($article['replies']['num'])) {
				echo '
							<i class="fas fa-comment" title="', $article['replies']['title'], '"></i> ', $article['replies']['num'];
			}

			if (!empty($article['replies']['after']))
				echo $article['replies']['after'];

			echo '
						</span>';
		}

		if (!empty($article['section']['name'])) {
			echo '
						<a class="floatright" href="', $article['section']['link'], '"><i class="far fa-list-alt"></i> ', $article['section']['name'], '</a>';
		}

		echo '
					</div>';

		if (!empty($article['image'])) {
			echo '
					<img src="', $article['image'], '" alt="', $article['title'], '">';
		}

		echo '
				</header>
				<div class="article_body">';

		if (!empty($article['teaser'])) {
			echo '
					<p>', $article['teaser'], '</p>';
		}

		echo '
				</div>
				<div class="article_footer">
					<div class="centertext">
						<a class="bbc_link" href="', $article['link'], '">', $txt['lp_read_more'], '</a>
					</div>
					<div class="centertext">';

		if (!empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '"><i class="fas fa-clock"></i> ', $article['date'], '</time>';
		}

		if (!empty($modSettings['lp_show_author']) && !empty($article['author'])) {
			if (!empty($article['author']['id']) && !empty($article['author']['name'])) {
				echo '
						| <i class="fas fa-user"></i> <a href="', $article['author']['link'], '" class="card_author">', $article['author']['name'], '</a>';
			} else {
				echo '
						| <span class="card_author">', $txt['guest_title'], '</span>';
			}
		}

		echo '
					</div>
				</div>
			</article>
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 *
 * @return void
 */
function template_show_articles_alt2()
{
	global $context, $modSettings, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="article_alt2_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<article class="descbox">';

		if (!empty($article['image'])) {
			echo '
			<a class="article_image_link" href="', $article['link'], '">
				<div style="background-image: url(\'' . $article['image'] . '\')"></div>
			</a>';
		}

		echo '
			<div class="article_body">
				<div>
					<header>';

		if (!empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '"><i class="fas fa-clock"></i> ', $article['date'], '</time>';
		}

		echo '
						<h3><a href="', $article['msg_link'], '">', $article['title'], '</a></h3>
					</header>';

		if (!empty($article['teaser'])) {
			echo '
					<section>
						<p>', $article['teaser'], '</p>
					</section>';
		}

		echo '
				</div>';

		if (!empty($modSettings['lp_show_author']) && !empty($article['author'])) {
			echo '
				<footer>';

			if (!empty($article['author']['avatar'])) {
				echo '
					<img src="', $article['author']['avatar'], '" loading="lazy" alt="', $txt['author'], '">';
			}

			echo '
					<span>';

			if (!empty($article['author']['id']) && !empty($article['author']['name'])) {
				echo '
						<a href="', $article['author']['link'], '">', $article['author']['name'], '</a>';
			} else {
				echo '
						<span>', $txt['guest_title'], '</span>';
			}

			echo '
					</span>
				</footer>';
		}

		echo '
			</div>
		</article>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 *
 * @return void
 */
function template_show_articles_alt3()
{
	global $context, $modSettings, $txt, $scripturl;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_alt3_view">';

	show_pagination();

	$i = 0;
	foreach ($context['lp_frontpage_articles'] as $article) {
		$i++;

		echo '
		<div class="card', $i % 2 === 0 ? ' alt': '', ' col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">
			<div class="meta">';

		if (!empty($article['image'])) {
			echo '
				<div class="photo" style="background-image: url(\'', $article['image'], '\')"></div>';
		}

		echo '
				<ul class="details">';

		if (!empty($modSettings['lp_show_author']) && !empty($article['author'])) {
			echo '
					<li class="author">
						<i class="fas fa-user"></i>';

			if (!empty($article['author']['id']) && !empty($article['author']['name'])) {
				echo '
						<a href="', $article['author']['link'], '">', $article['author']['name'], '</a>';
			} else {
				echo '
						<span class="card_author">', $txt['guest_title'], '</span>';
			}

			echo '
					</li>';
		}

		if (!empty($article['datetime'])) {
			echo '
					<li class="date"><i class="fas fa-calendar"></i><time datetime="', $article['datetime'], '">', $article['date'], '</time></li>';
		}

		if (!empty($article['tags'])) {
			echo '
					<li class="tags">
						<i class="fas fa-tag"></i>
						<ul style="display: inline">';

			foreach ($article['tags'] as $key) {
				echo '
							<li><a href="', $key['href'], '">', $key['name'], '</a></li>';
			}

			echo '
						</ul>
					</li>';
		}

		echo '
				</ul>
			</div>
			<div class="description">
				<h1><a href="', $article['link'], '">', $article['title'], '</a></h1>';

		if (!empty($article['section']['name'])) {
			echo '
				<h2><a href="', $article['section']['link'], '"><i class="far fa-list-alt"></i> ', $article['section']['name'], '</a></h2>';
		}

		if (!empty($article['teaser'])) {
			echo '
				<p>', $article['teaser'], '</p>';
		}

		echo '
				<div class="read_more">
					<a class="bbc_link" href="', $article['msg_link'], '">', $txt['lp_read_more'], '</a> <i class="fas fa-arrow-right"></i>
				</div>
			</div>
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 *
 * @return void
 */
function template_show_articles_simple()
{
	global $context, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_simple_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12 col-sm-6 col-md-', $context['lp_frontpage_num_columns'], '">';

		if (!empty($article['image'])) {
			echo '
			<div class="article_image" style="background-image: url(\'' . $article['image'] . '\')"></div>';
		}

		echo '
			<div class="mt-6 body">
				<a class="article_title" href="', $article['link'], '">', $article['title'], '</a>';

		if (!empty($article['teaser'])) {
			echo '
				<p class="article_teaser">', $article['teaser'], '</p>';
		}

		echo '
			</div>
			<div class="mt-6">
				<a class="bbc_link" href="', $article['link'], '">', $txt['lp_read_more'], '</a>
			</div>
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 *
 * @return void
 */
function template_show_articles_simple2()
{
	global $context, $txt;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
	<div class="lp_frontpage_articles article_simple2_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
		<div class="col-xs-12">
			<div class="card">
				<div class="card-header">
					<div class="card-image" style="background-image: url(\'' . $article['image'] . '\')"></div>
					<div class="card-title">
						<h3>', $article['title'], '</h3>';

		if (!empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '">', $article['date'], '</time>';
		}

		echo '
					</div>
					<svg viewBox="0 0 100 100" preserveAspectRatio="none">
						<polygon points="50,0 100,0 50,100 0,100" />
					</svg>
				</div>

				<div class="card-body">
					<div class="card-body-inner">';

		if (!empty($article['datetime'])) {
			echo '
						<time datetime="', $article['datetime'], '">', $article['date'], '</time>';
		}

		echo '
						<h3>', $article['title'], '</h3>';

		if (!empty($article['teaser'])) {
			echo '
						<p class="article_teaser">', $article['teaser'], '</p>';
		}

		echo '
						<a class="read_more" href="', $article['link'], '">
							<span>', $txt['lp_read_more'], '</span>
							<span class="arrow">&#x279c;</span>
						</a>
					</div>
				</div>

			</div>
		</div>';
	}

	show_pagination('bottom');

	echo '
	</div>';

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

/**
 * Example of custom view for frontpage articles
 *
 * Пример альтернативного отображения статей
 *
 * @return void
 */
function template_show_articles_simple3()
{
	global $context, $scripturl;

	if (empty($context['lp_active_blocks']))
		echo '
	<div class="col-xs">';

	echo '
		<div class="article_simple3_view">';

	show_pagination();

	foreach ($context['lp_frontpage_articles'] as $article) {
		echo '
			<div>';

		if (!empty($article['image'])) {
			echo '
				<img src="', $article['image'], '" alt="', $article['title'], '">';
		}

		echo '
				<div class="title">
					<div><a class="bbc_link" href="', $article['link'], '">', $article['title'], '</a></div>';

		if (!empty($article['teaser'])) {
			echo '
					<p>', $article['teaser'], '</p>';
		}

		echo '
				</div>';

		if (!empty($article['tags'])) {
			echo '
				<div class="tags">';

			foreach ($article['tags'] as $key) {
				echo '
					<a class="new_posts" href="', $key['href'], '">#', $key['name'], '</a>';
			}

			echo '
				</div>';
		}

		echo '
			</div>';
	}

	echo '
		</div>';

	show_pagination('bottom');

	if (empty($context['lp_active_blocks']))
		echo '
	</div>';
}

/**
 * Шаблон списка сортировки для страниц рубрик и тегов
 *
 * Template of sort list for category pages and tags
 *
 * @return void
 */
function template_sorting_above()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], '</h3>
	</div>';

	if (empty($context['lp_frontpage_articles'])) {
		echo '
	<div class="information">', $txt['lp_no_items'], '</div>';
	} else {
		echo '
	<div class="information">';

		if (!empty($context['description'])) {
			echo '
		<div class="floatleft">', $context['description'], '</div>';
		}

		echo '
		<div class="floatright">
			<form action="', $context['canonical_url'], '" method="post">
				<label for="sort">', $txt['lp_sorting_label'], '</label>
				<select id="sort" name="sort" onchange="this.form.submit()">
					<option value="title;desc"', $context['current_sorting'] == 'title;desc' ? ' selected' : '', '>', $txt['lp_sort_by_title_desc'], '</option>
					<option value="title"', $context['current_sorting'] == 'title' ? ' selected' : '', '>', $txt['lp_sort_by_title'], '</option>
					<option value="created;desc"', $context['current_sorting'] == 'created;desc' ? ' selected' : '', '>', $txt['lp_sort_by_created_desc'], '</option>
					<option value="created"', $context['current_sorting'] == 'created' ? ' selected' : '', '>', $txt['lp_sort_by_created'], '</option>
					<option value="updated;desc"', $context['current_sorting'] == 'updated;desc' ? ' selected' : '', '>', $txt['lp_sort_by_updated_desc'], '</option>
					<option value="updated"', $context['current_sorting'] == 'updated' ? ' selected' : '', '>', $txt['lp_sort_by_updated'], '</option>
					<option value="author_name;desc"', $context['current_sorting'] == 'author_name;desc' ? ' selected' : '', '>', $txt['lp_sort_by_author_desc'], '</option>
					<option value="author_name"', $context['current_sorting'] == 'author_name' ? ' selected' : '', '>', $txt['lp_sort_by_author'], '</option>
					<option value="num_views;desc"', $context['current_sorting'] == 'num_views;desc' ? ' selected' : '', '>', $txt['lp_sort_by_num_views_desc'], '</option>
					<option value="num_views"', $context['current_sorting'] == 'num_views' ? ' selected' : '', '>', $txt['lp_sort_by_num_views'], '</option>
				</select>
			</form>
		</div>
	</div>';
	}
}

function template_sorting_below()
{
}

/**
 * @param string $position
 * @return void
 */
function show_pagination(string $position = 'top')
{
	global $context, $modSettings;

	$show_on_top = $position == 'top' && !empty($modSettings['lp_show_pagination']);

	$show_on_bottom = $position == 'bottom' && (empty($modSettings['lp_show_pagination']) || ($modSettings['lp_show_pagination'] == 1));

	if (!empty($context['page_index']) && ($show_on_top || $show_on_bottom))
		echo '
		<div class="col-xs-12 centertext">
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>';
}
