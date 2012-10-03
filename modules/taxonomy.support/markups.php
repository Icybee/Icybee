<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Exception;
use Icybee\Modules\Taxonomy\Terms\Term;

class taxonomy_support_WdMarkups extends patron_markups_WdHooks
{
	static protected function model($name='vocabulary')
	{
		return parent::model($name);
	}

	static public function popularity(array $args, Patron\Engine $patron, $template)
	{
		extract($args, EXTR_PREFIX_ALL, 'p');

		$where = array();
		$params = array();

		#
		# vocabulary
		#

		if ($p_vocabulary)
		{
			$where[] = '(v.vocabulary = ? OR v.vocabularyslug = ?)';
			$params[] = $p_vocabulary;
			$params[] = $p_vocabulary;
		}

		#
		# scope of the vocabulary
		#

		if ($p_scope)
		{
			$parts = explode(',', $p_scope);
			$parts = array_map('trim', $parts);

			if (count($parts) > 1)
			{
				$where[] = 'vs.constructor IN (' . implode(', ', array_pad(array(), count($parts), '?')) . ')';
				$params = array_merge($params, $parts);
			}
			else
			{
				$where[] = 'vs.constructor = ?';
				$params[] = $p_scope;
			}
		}

		#
		# query
		#

//		echo t('where: <code>\1</code> \2', array($where, $params));

		global $core;

		$entries = $core->db->query
		(
			'SELECT t.*,

			(SELECT COUNT(nid) FROM {prefix}taxonomy_terms__nodes tn WHERE tn.vtid = t.vtid) AS `used`

			FROM {prefix}taxonomy_vocabulary v
			INNER JOIN {prefix}taxonomy_vocabulary__scopes vs USING(vid)
			INNER JOIN {prefix}taxonomy_terms t USING(vid)

			' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '

			GROUP BY vtid ORDER BY term',

			$params
		)
		->fetchAll(PDO::FETCH_ASSOC);

		#
		# remove used entries
		#

		foreach ($entries as $i => $entry)
		{
			if ($entry['used'])
			{
				continue;
			}

			unset($entries[$i]);
		}

		#
		# scale popularities
		#

		if ($p_scale)
		{
			$min = 0xFFFFFFFF;
			$max = 0;

			foreach ($entries as $entry)
			{
				$min = min($min, $entry['used']);
				$max = max($max, $entry['used']);
			}

			$range = max($max - $min, 1);

			//echo "min: $min, max: $max, range: $range<br />";

			foreach ($entries as &$entry)
			{
				$entry['popularity'] = 1 + round(($entry['used'] - $min) / $range * ($p_scale - 1));
			}
		}

		return $patron($template, $entries);
	}

	static public function terms(array $args, Patron\Engine $patron, $template)
	{
		global $core;

		if (isset($args['scope']))
		{
			throw new Exception('The "scope" parameter is deprecated, use "construtor" instead.');

			$args['constructor'] = $args['scope'];
		}

		$conditions = array();
		$conditions_args = array();

		$inner = ' INNER JOIN {prefix}taxonomy_terms term USING(vid)';

		$constructor = $args['constructor'];

		if ($constructor)
		{
			$inner .= ' INNER JOIN {prefix}taxonomy_vocabulary__scopes USING(vid)';

			$conditions[] = 'constructor = ?';
			$conditions_args[] = $constructor;
		}

		$vocabulary = $args['vocabulary'];

		if ($vocabulary)
		{
			if (is_numeric($vocabulary))
			{
				$conditions[] = 'vid = ?';
				$conditions_args[] = $vocabulary;
			}
			else
			{
				$conditions[] = '(vocabulary = ? OR vocabularyslug = ?)';
				$conditions_args[] = $vocabulary;
				$conditions_args[] = $vocabulary;
			}
		}

		$conditions[] = '(SELECT GROUP_CONCAT(nid) FROM {prefix}taxonomy_terms__nodes tnode
			INNER JOIN {prefix}nodes node USING(nid)
			WHERE is_online = 1 AND tnode.vtid = term.vtid) IS NOT NULL';


		$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : null;



		#
		#
		#

		$model = $core->models['taxonomy.terms'];

		$entries = $model->query
		(
			'SELECT voc.*, term.*,

			(SELECT GROUP_CONCAT(nid) FROM {prefix}taxonomy_terms__nodes tnode
			INNER JOIN {prefix}nodes node USING(nid)
			WHERE is_online = 1 AND tnode.vtid = term.vtid
			ORDER BY tnode.weight) AS nodes_ids

			FROM {prefix}vocabulary voc' . $inner . $where . ' ORDER BY term.weight, term',

			$conditions_args
		)
		->fetchAll(PDO::FETCH_CLASS, 'Icybee\Modules\Taxonomy\Terms\Term', array($model));

		if ($constructor)
		{
			foreach ($entries as $entry)
			{
				$entry->nodes_constructor = $constructor;
			}
		}

		return $patron($template, $entries);
	}

	/*

	Charge des noeuds 'complets' selon un _vocabulaire_ et/ou une _portée_.

	Parce qu'un même vocabulaire peut-être utilisé sur plusieurs modules, si 'scope' est
	définit le constructeur du noeud doit être connu et égal à 'scope'. Pour cela il nous faut
	joindre la table du module `system.nodes`.

	Si scope est défini c'est plus simple, parce que toutes les entrées sont chargées depuis un
	même module.

	Si scope est défini, il faudrait peut-être modifier 'self' pour qu'il contienne les données du
	terme. Ou alors utiliser un autre marqueur pour l'occasion... hmm ce serait peut-être le mieux.
	<wdp:taxonomy:term select="" vacabulary="" scope="" />

	Les options de 'range' ne doivent pas être appliquée aux termes mais au noeud chargés dans un
	second temps. Notamment les options d'ordre.

	*/

	static public function nodes(array $args, Patron\Engine $patron, $template)
	{
		global $core;

		$term = $patron->context['this'];
		$order = $args['order'];

		if ($term instanceof Term)
		{
			$constructor = $term->nodes_constructor;
			$order = $args['order'] ? strtr($args['order'], ':', ' ') : 'FIELD (nid, ' . $term->nodes_ids . ')';

			$entries = $core->models[$constructor]->where('is_online = 1 AND nid IN(' . $term->nodes_ids . ')')->order($order)->all;

			$taxonomy_property = $term->vocabularyslug;
			$taxonomy_property_slug = $taxonomy_property . 'slug';

			foreach ($entries as $entry)
			{
				$entry->$taxonomy_property = $term;
				$entry->$taxonomy_property_slug = $term->termslug;
			}
		}
		else
		{
			$term = $args['term'];
			$vocabulary = $args['vocabulary'];
			$constructor = $args['constructor'];

			$vocabulary = $core->models['taxonomy.vocabulary']
			->joins('INNER JOIN {self}__scopes USING(vid)')
			->joins('INNER JOIN {prefix}taxonomy_terms USING(vid)')
			->where('vocabularyslug = ? AND constructor = ? AND termslug = ?', $vocabulary, $constructor, $term)
			->limit(1)
			->one();

			$patron->context['self']['vocabulary'] = $vocabulary;

			$ids = $core->db->query
			(
				'SELECT nid
				FROM {prefix}taxonomy_vocabulary voc
				INNER JOIN {prefix}taxonomy_vocabulary__scopes scopes USING(vid)
				INNER JOIN {prefix}taxonomy_terms term USING(vid)
				INNER JOIN {prefix}taxonomy_terms__nodes tnode USING(vtid)
				WHERE constructor = ? AND term.termslug = ?
				', array
				(
					$constructor, $term
				)
			)
			->fetchAll(PDO::FETCH_COLUMN);

			if (!$ids)
			{
				return;
			}

			$limit = $args['limit'];
			$offset = (isset($args['page']) ? $args['page'] : 0) * $limit;

			$arr = $core->models[$constructor]
			->where(array('is_online' => true, 'nid' => $ids))
			->order($order);

			$count = $arr->count;
			$entries = $arr->limit($offset, $limit)->all;

			$patron->context['self']['range'] = array
			(
				'count' => $count,
				'limit' => $limit,
				'page' => isset($args['page']) ? $args['page'] : 0
			);
		}

		return $patron($template, $entries);

		/*
		$where = array();
		$params = array();

		$inner = ' INNER JOIN {prefix}terms USING(vid)';

		#
		#
		#

		$scope = $args['scope'];

		if ($scope)
		{
			$where[] = 'scope = ?';
			$params[] = $scope;

			$inner .= ' INNER JOIN {prefix}vocabulary__scopes USING(vid)';
		}

		$vocabulary = $args['vocabulary'];

		if ($vocabulary)
		{
			if (is_numeric($vocabulary))
			{
				$where[] = 'vid = ?';
				$params[] = $vocabulary;
			}
			else
			{
				$where[] = '(vocabulary = ? OR vocabularyslug = ?)';
				$params[] = $vocabulary;
				$params[] = $vocabulary;
			}
		}

		$term = $args['term'];

		if ($term)
		{
			if (is_numeric($term))
			{
				$where[] = 'vtid = ?';
				$params[] = $term;
			}
			else
			{
				$where[] = '(term = ? OR termslug = ?)';
				$params[] = $term;
				$params[] = $term;
			}
		}

		//$where = $where ? 'WHERE ' . implode(' AND ', $where) : null;

		#
		#
		#

		$terms = self::model()->compat_select('*', $inner . ($where ? ' WHERE ' . implode(' AND ', $where) : ''), $params)->fetchAll();

		if ($term)
		{
			$patron->context['self']['terms'] = $terms;
		}

		$patron->context['self']['vocabulary'] = array_shift($terms);

		$inner .= ' INNER JOIN {prefix}terms__nodes USING(vtid)';
		$inner .= ' INNER JOIN {prefix}nodes USING(nid)';

		$where[] = 'is_online = 1';

		if ($scope)
		{
			$where[] = 'constructor = ?';
			$params[] =  $scope;
		}

		$ids = self::model()->compat_select('nid', $inner . ($where ? ' WHERE ' . implode(' AND ', $where) : ''), $params)->fetchAll(PDO::FETCH_COLUMN);

		if (empty($ids))
		{
			return;
		}

		#
		#
		#

		if ($scope)
		{
			$query = 'WHERE nid IN(' . implode(',', $ids) . ')';
			$order = null;

			if ($args['by'])
			{
				$order = ' ORDER BY ' . $args['by'] . ' ' . $args['order'];
			}

			$limit = $args['limit'];

			if ($limit)
			{
				$count = self::model($scope)->count(null, null, $query);
				$page = isset($args['page']) ? $args['page'] : 0;

				$entries = self::model($scope)->loadRange
				(
					$page * $limit, $limit, $query . $order
				)
				->fetchAll();
			}
			else
			{
				$entries = self::model($scope)->loadAll($query . $order)->fetchAll();

				$count = count($entries);
				$limit = null;
				$page = null;
			}

			$patron->context['self']['range'] = array
			(
				'count' => $count,
				'limit' => $limit,
				'page' => $page
			);

			return $patron($template, $entries);
		}
		else
		{
			throw new Exception('Multiple scopes is not ready yet');

			$constructors = $core->db()->query
			(
				'SELECT constructor, nid FROM {prefix}system_nodes WHERE nid IN (' . implode(', ', array_keys($ids)) . ')'
			)
			->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

			foreach ($constructors as $constructor => $n_ids)
			{
				$nodes = $core->models[$constructor]->loadAll
				(
					'WHERE is_online = 1 AND nid IN(' . implode(', ', $n_ids) . ')'
				)
				->fetchAll();

				foreach ($nodes as $node)
				{
	//				$ids[$node->nid]->node = $node;
					$ids[$node->nid] = $node;
				}
			}

			return $patron($template, array_values($ids));
		}
		*/
	}
}