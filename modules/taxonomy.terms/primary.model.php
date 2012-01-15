<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Taxonomy\Terms;

use ICanBoogie\ActiveRecord\Taxonomy\Term;

class Model extends \ICanBoogie\ActiveRecord\Model
{
	public function save(array $properties, $key=null, array $options=array())
	{
		if (isset($properties[Term::TERM]) && empty($properties[Term::TERMSLUG]))
		{
			$properties[Term::TERMSLUG] = wd_slugize($properties[Term::TERM]);
		}
		else if (isset($properties[Term::TERMSLUG]))
		{
			$properties[Term::TERMSLUG] = wd_normalize($properties[Term::TERMSLUG]);
		}

		return parent::save($properties, $key, $options);
	}

	/* DEPRECATED-20110709 ??
	public function load_terms($vocabulary, $scope=null, $having_nodes=true)
	{
		global $core;

		$has_descriptions = isset($core->modules['terms.descriptions']);

		$query = 'SELECT term.*';

		if ($has_descriptions)
		{
			$query .= ', description.*';
		}

		$query .= ' FROM {self} term';

		$conditions = array();
		$conditions_args = array();

		if (is_numeric($vocabulary))
		{
			$conditions[] = 'vid = ?';
			$conditions_args[]= $vocabulary;
		}
		else
		{
			$query .= ' INNER JOIN {prefix}vocabulary USING(vid)
			INNER JOIN {prefix}vocabulary__scopes USING(vid)';

			$conditions[] = '(vocabularyslug = ? OR vocabulary = ?)';
			$conditions_args[] = $vocabulary;
			$conditions_args[] = $vocabulary;

			if ($scope)
			{
				$conditions[] = 'scope = ?';
				$conditions_args[] = $scope;
			}
		}

		if ($having_nodes)
		{
			$conditions[] = '(SELECT nid FROM {self}__nodes INNER JOIN {prefix}nodes USING(nid) WHERE vtid = term.vtid AND is_online = 1 LIMIT 1) IS NOT NULL';
		}

		global $core;

		if ($has_descriptions)
		{
			$query .= ' LEFT JOIN {prefix}terms_descriptions description USING(vtid)';
		}

		return $this->query
		(
			$query . ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY weight, term',
			$conditions_args
		)
		->fetchAll(PDO::FETCH_CLASS, 'ICanBoogie\ActiveRecord\Taxonomy\Term');
	}
	*/
}