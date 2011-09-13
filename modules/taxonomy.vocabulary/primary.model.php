<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Model\Taxonomy;

use ICanBoogie\ActiveRecord\Model;

class Vocabulary extends Model
{
	public function save(array $properties, $key=null, array $options=array())
	{
		if (isset($properties['vocabulary']) && empty($properties['vocabularyslug']))
		{
			$properties['vocabularyslug'] = wd_slugize($properties['vocabulary']);
		}

		if (isset($properties['vocabularyslug']))
		{
			$properties['vocabularyslug'] = wd_normalize($properties['vocabularyslug']);
		}

		$key = parent::save($properties, $key, $options);

		if (!$key)
		{
			return $key;
		}

		$scope = array();

		if (isset($properties['scope']))
		{
			$insert = $this->prepare('INSERT IGNORE INTO {self}__scopes (vid, constructor) VALUES(?, ?)');

			foreach ($properties['scope'] as $constructor => $ok)
			{
				$ok = filter_var($ok, FILTER_VALIDATE_BOOLEAN);

				if (!$ok)
				{
					continue;
				}

				$scope[] = $constructor;
				$insert->execute(array($key, $constructor));
			}
		}

		if ($scope)
		{
			$scope = array_map(array($this, 'quote'), $scope);

			$this->execute('DELETE FROM {self}__scopes WHERE vid = ? AND constructor NOT IN(' . implode(',', $scope) . ')', array($key));
		}

		return $key;
	}

	public function delete($key)
	{
		$rc = parent::delete($key);

		if ($rc)
		{
			$this->execute('DELETE FROM {self}__scopes WHERE vid = ?', array($key));
			$this->clearTerms($key);
		}

		return $rc;
	}

	protected function clearTerms($vid)
	{
		// TODO: use model delete() method instead, maybe put an event on 'taxonomy.vocabulary.delete'

		global $core;

		$model = $core->models['taxonomy.terms'];
		$model->execute('DELETE FROM {self}__nodes WHERE (SELECT vid FROM {self} WHERE {self}__nodes.vtid = {self}.vtid) = ?', array($vid));
		$model->execute('DELETE FROM {self} WHERE vid = ?', array($vid));
	}
}