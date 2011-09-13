<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Taxonomy;

class Support extends \Icybee\Module
{
	/**
	 * The getManageColumns method can be used by modules whishing to display
	 * vocabularies columns in their management table.
	 *
	 * @param $scope
	 * The scope for which to retreive columns.
	 *
	 * @return
	 * An array of columns for the Icybee\Manager class
	 */

	/*
	public function getManageColumns($scope)
	{
		$vocabularies = $this->vocabulary->model('scope')->loadAll
		(
			'where scope = ? and is_multiple = 0 order by weight, vocabulary', array
			(
				$scope
			)
		);

		$columns = array();

		foreach ($vocabularies as $vocabulary)
		{
			$columns[$vocabulary->vocabularyslug] = array
			(
				'label' => $vocabulary->vocabulary
			);
		}

		return $columns;
	}

	public function getSelectIdentifiers($scope)
	{
		$vocabularies = $this->vocabulary->model('scope')->loadAll
		(
			'where scope = ? order by weight, vocabulary', array
			(
				$scope
			)
		);

		$i = 0;
		$identifiers = array();

		foreach ($vocabularies as $vocabulary)
		{
			$i++;

			$vid = $vocabulary->vid;
			$identifier = $vocabulary->vocabularyslug;

			#
			# update identifiers
			#

			$definition = '(select ';

			if ($vocabulary->is_multiple)
			{
				$definition .= 'GROUP_CONCAT(term)';
			}
			else
			{
				$definition .= 'term';
			}

			$definition .= ' from {prefix}terms__nodes as s' . $i . 't1 inner join `{prefix}terms` as s' . $i . 't2 on (s' . $i . 't1.vtid = s' . $i . 't2.vtid and s' . $i .'t2.vid = ' . $vid . ')
			where s' . $i . 't1.nid = node.nid)';

			$identifiers[$identifier] = $definition;
		}

		return $identifiers;
	}
	*/

	/**
	 * Complete a WdDatabaseView schema in order to incorporate the vocabulary
	 * of a given scope.
	 *
	 * @param $schema
	 * The schema that will be used by WdDatabaseView to create a view.
	 *
	 * @return array
	 * The modified schema
	 */

	/*
	public function completeViewSchema(array $schema, $scope)
	{
		$vocabularies = $this->vocabulary->model('scope')->loadAll
		(
			'where scope = ? order by weight, vocabulary', array
			(
				$scope
			)
		);

		$identifiers = &$schema['identifiers'];
		$fields = &$schema['fields'];

		$i = 0;

		foreach ($vocabularies as $vocabulary)
		{
			$i++;

			$vid = $vocabulary->vid;
			$identifier = $vocabulary->slug;

			#
			# update fields
			#

			$fields[$identifier] = array('type' => 'varchar');

			#
			# update identifiers
			#

			$definition = '(select ';

			if ($vocabulary->is_multiple)
			{
				$definition .= 'GROUP_CONCAT(term)';
			}
			else
			{
				$definition .= 'term';
			}

			$definition .= ' from {prefix}terms__nodes as s' . $i . 't1 inner join `{prefix}terms` as s' . $i . 't2 on (s' . $i . 't1.vtid = s' . $i . 't2.vtid and s' . $i .'t2.vid = ' . $vid . ')
			where s' . $i . 't1.nid = t1.nid) as `' . $identifier . '`';

			$identifiers[$identifier] = $definition;
		}

		return $schema;
	}
	*/
}