<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Taxonomy\Vocabulary;

use ICanBoogie\Module;

class Save extends \Icybee\Operation\ActiveRecord\Save
{
	protected function __get_properties()
	{
		global $core;

		$request = $this->request;
		$properties = parent::__get_properties();

		if ($request['scope'])
		{
			$properties['scope'] = $request['scope'];
		}

		if (!$this->key || !$core->user->has_permission(Module\Nodes::PERMISSION_MODIFY_BELONGING_SITE))
		{
			$properties['siteid'] = $core->site_id;
		}

		return $properties;
	}
}