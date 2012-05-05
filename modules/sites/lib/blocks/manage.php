<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Sites;

use Brickrouge\DropdownMenu;

use ICanBoogie\ActiveRecord\Site;

class ManageBlock extends \WdManager
{
	public function __construct($module, array $tags=array())
	{
		global $core;

		parent::__construct
		(
			$module, $tags + array
			(
				self::T_KEY => 'siteid',
				self::T_ORDER_BY => 'title',
				self::T_COLUMNS_ORDER => array('title', 'url', 'language', 'timezone', 'status')
			)
		);
	}

	public static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('../../public/admin.css');
		$document->js->add('../../public/admin.js');
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

			),

			'status' => array
			(
				'label' => 'Status'
			),

			'timezone' => array
			(
				'discreet' => true
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
		$parts = explode('.', $_SERVER['SERVER_NAME']);
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

		return $this->render_filter_cell($record, $property, \ICanBoogie\capitalize($core->locale->conventions['localeDisplayNames']['languages'][$record->$property]));
	}

	protected function render_cell_timezone($record, $property)
	{
		return $this->render_filter_cell($record, $property);
	}

	protected function render_cell_status(Site $record, $property)
	{
		static $labels = array
		(
			'Offline',
			'Online',
			'Under maintenance',
			'Denied access'
		);

		static $classes = array
		(
			'btn-danger',
			'btn-success',
			'btn-warning',
			'btn-danger'
		);

		$status = $record->status;
		$status_label = $labels[$status];
		$status_class = $classes[$status];
		$site_id = $record->siteid;

		$menu = new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $labels,

				'value' => $status
			)
		);

		return <<<EOT
<div class="btn-group" data-property="status" data-site-id="$site_id">
	<span class="btn $status_class dropdown-toggle" data-toggle="dropdown"><span class="text">$status_label</span> <span class="caret"></span></span>
    $menu
</div>
EOT;
	}
}