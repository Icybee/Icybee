<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

use Brickrouge\Element;

class PopPage extends Element // TODO-20120922: rewrite this element
{
	public function __toString()
	{
		global $core;

		try
		{
			$model = $core->models['pages']; // TODO-20120922: use BluePrint.
			$nodes = $model->select('nid, parentid, title')
			->filter_by_siteid($core->site_id)
			->order('weight, created')
			->all(\PDO::FETCH_OBJ);

			$tree = Model::nestNodes($nodes);
			Model::setNodesDepth($tree);
			$entries = Model::levelNodesById($tree);

			$options = array();

			foreach ($entries as $entry)
			{
				$options[$entry->nid] = str_repeat("\xC2\xA0", $entry->depth * 4) . $entry->title;
			}

			$this[self::OPTIONS] = array(null => '') + $options;
		}
		catch (\Exception $e)
		{
			return $e->getMessage();
		}

		return parent::__toString();
	}
}