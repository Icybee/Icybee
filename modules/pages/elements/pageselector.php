<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\ActiveRecord\Model;
use BrickRouge\Element;

class WdPageSelectorElement extends Element
{
	public function __toString()
	{
		global $core;

		try
		{
			$model = $core->models['pages'];
			$nodes = $model->select('nid, parentid, title')->where('siteid = ?', $core->site_id)->order('weight, created')->all(PDO::FETCH_OBJ);

			$tree = Model\Pages::nestNodes($nodes);
			Model\Pages::setNodesDepth($tree);
			$entries = Model\Pages::levelNodesById($tree);

			$options = array();

			foreach ($entries as $entry)
			{
				$options[$entry->nid] = str_repeat("\xC2\xA0", $entry->depth * 4) . $entry->title;
			}

			$this->set(self::T_OPTIONS, array(null => '') + $options);
		}
		catch (\Exception $e)
		{
			return $e->getMessage();
		}

		return parent::__toString();
	}
}