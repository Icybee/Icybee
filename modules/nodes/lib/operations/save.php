<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

use ICanBoogie\I18n\FormattedString;

use Icybee\Modules\Nodes\Node;

/**
 * Saves a node.
 *
 * Adds the "display" save mode.
 */
class SaveOperation extends \Icybee\SaveOperation
{
	const MODE_DISPLAY = 'display';

	/**
	 * Overrides the method to handle the following properties:
	 *
	 * `constructor`: In order to avoid misuse and errors, the constructor of the record is set by
	 * the method.
	 *
	 * `uid`: Only users with the PERMISSION_ADMINISTER permission can choose the user of records.
	 * If the user saving a record has no such permission, the Node::UID property is removed from
	 * the properties created by the parent method.
	 *
	 * `siteid`: If the user is creating a new record or the user has no permission to choose the
	 * record's site, the property is set to the value of the working site's id.
	 *
	 * @see Icybee\SaveOperation::get_properties()
	 */
	protected function get_properties()
	{
		global $core;

		$properties = parent::get_properties();

		$user = $core->user;

		if (!$user->has_permission(Module::PERMISSION_ADMINISTER, $this->module))
		{
			unset($properties[Node::UID]);
		}

		if (!$this->key || !$user->has_permission(Module::PERMISSION_MODIFY_BELONGING_SITE))
		{
			$properties[Node::SITEID] = $core->site_id;
		}

		if (!empty($properties[Node::SITEID]))
		{
			$properties[Node::LANGUAGE] = $core->models['sites'][$properties[Node::SITEID]]->language;
		}

		return $properties;
	}

	/**
	 * Overrides the method to provide a nicer log message, and change the operation location to
	 * the node URL if the save mode is "display".
	 *
	 * @see Icybee\SaveOperation::process()
	 */
	protected function process()
	{
		$rc = parent::process();
		$record = $this->module->model[$rc['key']];

		$this->response->message = new FormattedString
		(
			$rc['mode'] == 'update' ? '%title has been updated in :module.' : '%title has been created in %module.', array
			(
				'title' => \ICanBoogie\shorten($record->title),
				'module' => $this->module->title
			)
		);

		if ($this->mode == self::MODE_DISPLAY)
		{
			$url = $record->url;

			if ($url{0} != '#')
			{
				$this->response->location = $record->url;
			}
		}

		return $rc;
	}
}