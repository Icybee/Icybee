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

class taxonomy_support_WdMarkups
{
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
}