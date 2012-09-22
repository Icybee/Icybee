<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Articles;

use ICanBoogie\Event;

// TODO-20101116: move this code to the "contents" module.

class Hooks
{
	/**
	 * The callback is called when the `resources.files.path.change` is triggered, allowing us to
	 * update contents to the changed path of resources.
	 *
	 * @param Event $event
	 */
	public static function resources_files_path_change(Event $event) // TODO-20120922: move this to contents module
	{
		global $core;

		try
		{
			$model = $core->models['articles'];
		}
		catch (\Exception $e)
		{
			return;
		}

		$model->execute
		(
			'UPDATE {self} SET body = REPLACE(body, ?, ?)', $event->path
		);
	}

	public static function markup_articles(array $args, \WdPatron $patron, $template)
	{
		global $core;

		extract($args, EXTR_PREFIX_ALL, 'attr');

		#
		#
		#

		// TODO-20090121: ajouter l'atribut group="username" grouporder="asc"
		// on pourra peut être se débarasser de month, categories, user...

		$options = $args;

		#
		# build query
		#

		$where = array();
		$params = array();

		if ($attr_author)
		{
			$where[] = '(SELECT username FROM {prefix}user_users WHERE uid = node.uid) = ?';
			$params[] = $attr_author;
		}

		if ($attr_date)
		{
			$names = array('YEAR', 'MONTH', 'DAY');

			if (preg_match('#(\d{4})?-(\d{2})?#', $attr_date, $match))
			{
//				echo l('date: \1, match: \2', $attr_date, $match);

				array_shift($match);

				foreach ($match as $key => $value)
				{
					$where[] = $names[$key] . '(`date`) = ?';
					$params[] = $value;
				}
			}
		}

		$where[] = 'is_online = 1';

		#
		# build query
		#

		$arq = $core->models['articles']->where(implode(' AND ', $where), $params);

		$count = $arq->count;

		$options['count'] = $count;
		$options['pages'] = $attr_limit ? ceil($count / $attr_limit) : 1;

		/*
		 * FIXME-20100702: this is disabled because the markup might be used multiple time on
		 * the same page. (e.g. list, recent...)
		 *
		if ($attr_limit && $attr_page === null && isset($_GET['page']))
		{
			$attr_page = $_GET['page'];
		}
		*/

		#
		# load entries
		#

		if ($attr_order == 'random')
		{
			$arq->order('rand()');
		}
		else if ($attr_by)
		{
			$arq->order("$attr_by $attr_order");
		}

		if ($attr_limit)
		{
			$arq->limit($attr_page * $attr_limit, $attr_limit);
		}

		$entries = $arq->all;

		Event::fire
		(
			'nodes_load', array
			(
				'nodes' => $entries
			),

			$patron
		);

		#
		# save options, they'll be used to handle pages
		#

		//$patron->set('self.range', $options);
		$patron->context['self']['range'] = array
		(
			'count' => $count,
			'limit' => $attr_limit,
			'page' => $attr_page
		);

		return $patron($template, $entries);
	}

	public static function markup_articles_read(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$limit = $args['limit'];
		$constructor = 'articles';

		$hits = $core->models['feedback.hits']->query
		(
			'SELECT hit.*, (hits / (TO_DAYS(CURRENT_DATE) - TO_DAYS(first))) AS perday
			FROM {self} as hit
			INNER JOIN {prefix}nodes USING(nid)
			WHERE is_online = 1 AND constructor = ?
			ORDER BY hits DESC LIMIT ' . $limit, array
			(
				$constructor
			)
		)
		->fetchAll(\PDO::FETCH_OBJ);

		$nids = array();

		foreach ($hits as $hit)
		{
			$nids[$hit->nid] = $hit;
		}

		$entries = $core->models[$constructor]->find(array_keys($nids));

		foreach ($entries as $entry)
		{
			$nids[$entry->nid]->node = $entry;
		}

		return $patron($template, array_values($nids));
	}

	// TODO-20110627: should move this to the "contents" model.

	public static function markup_by_date(array $args, \WdPatron $patron, $template)
	{
		global $core;

		extract($args, EXTR_PREFIX_ALL, 'p');

		$query = 'node.*, article.* FROM {prefix}nodes node
		INNER JOIN {prefix}contents article USING(nid) WHERE is_online = 1';
		$params = array();

		if ($p_group)
		{
			$query = 'DATE_FORMAT(`date`, ?), ' . $query;
			$params[] = $p_group;
		}

		$query .= ' ORDER BY `date` ' . $p_order;

		if ($p_limit)
		{
			$query .= " LIMIT $p_start, $p_limit";
		}
		else if ($p_start)
		{
			$query .= " LIMIT $p_start";
		}

		$model = $core->models['articles'];

		$entries = $model->query('SELECT ' . $query, $params)
		->fetchAll($p_group ? \PDO::FETCH_GROUP | \PDO::FETCH_CLASS : \PDO::FETCH_CLASS, __NAMESPACE__ . '\Article', array($model));

		return $patron($template, $entries);
	}

	// TODO-20110627: should move this to the "contents" model.

	public static function markup_by_author(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$model = $core->models['articles'];

		$entries = $model->query
		(
			'SELECT username, node.*, article.*
			FROM {prefix}nodes node
			INNER JOIN {self} article USING(nid)
			INNER JOIN {prefix}users USING(uid)
			WHERE is_online = 1 ORDER BY `username`, `date` DESC'
		)
		->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_CLASS, __NAMESPACE__ . '\Article', array($model));

		return $patron($template, $entries);
	}
}