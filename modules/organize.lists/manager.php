<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Organize\Lists;

use ICanBoogie\ActiveRecord;

class Manager extends \ICanBoogie\Modules\Nodes\Manager
{
	protected function get_cell_title(ActiveRecord\Node $record, $property)
	{
		global $core;

		$titles = $core->models['nodes']->select('title')
		->joins('INNER JOIN {prefix}organize_lists_nodes AS jn ON nodeid = nid')->where('listid = ?', $record->nid)
		->order('jn.weight')
		->all(PDO::FETCH_COLUMN);

		if ($titles)
		{
			$last = array_pop($titles);

			$includes = $titles
				? t('Comprenant&nbsp;: !list et !last', array('!list' => wd_shorten(implode(', ', $titles), 80, 1), '!last' => $last))
				: t('Comprenant&nbsp;: !entry', array('!entry' => $last));
		}
		else
		{
			$includes = '<em>La liste est vide</em>';
		}

		$title  = parent::get_cell_title($record, $property);
		$title .= '<br />';
		$title .= '<span class="small">';
		$title .= $includes;
		$title .= '</span>';

		return $title;
	}
}