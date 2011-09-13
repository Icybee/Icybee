<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Manager;

use ICanBoogie\ActiveRecord\Site;

class Sites extends \WdManager
{
	public function __construct($module, array $tags=array())
	{
		parent::__construct
		(
			$module, $tags + array
			(
				self::T_KEY => 'siteid',
				self::T_ORDER_BY => 'title'
			)
		);
	}

	protected function columns()
	{
		return array
		(
			'title' => array
			(

			),

			'url' => array
			(
				'class' => 'url'
			),

			'status' => array
			(
				'label' => 'Status'
			)
		);
	}

	protected function extend_column_url(array $column, $id, array $fields)
	{
		return array
		(
			'orderable' => false
		)

		+ parent::extend_column($column, $id, $fields);
	}

	protected function render_cell_title($record, $property)
	{
		return $this->render_edit_cell($record, $property);
	}

	protected function render_cell_url(Site $record, $property)
	{
		$parts = explode('.', $_SERVER['HTTP_HOST']);
		$parts = array_reverse($parts);

		if ($record->tld)
		{
			$parts[0] = '<strong>' . $record->tld . '</strong>';
		}

		if ($record->domain)
		{
			$parts[1] = '<strong>' . $record->domain . '</strong>';
		}

		if ($record->subdomain)
		{
			$parts[2] = '<strong>' . $record->subdomain . '</strong>';
		}
		else if (empty($parts[2]))
		{
			unset($parts[2]);
		}

		$label = 'http://' . implode('.', array_reverse($parts)) . ($record->path ? '<strong>' . $record->path . '</strong>' : '');

		return '<a href="' . $record->url . '">' . $label . '</a>';
	}

	protected function render_cell_language($record, $property)
	{
		global $core;

		return ucfirst($core->locale->conventions['localeDisplayNames']['languages'][$record->$property]);
	}

	protected function render_cell_status(Site $record, $property)
	{
		static $labels = array
		(
			'<span class="warn">Offline</span>',
			'Online',
			'Under maintenance',
			'Deneid access'
		);

		return $labels[$record->status];
	}
}