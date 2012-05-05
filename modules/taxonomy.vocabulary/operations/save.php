<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Taxonomy\Vocabulary;

use ICanBoogie\Modules;

class SaveOperation extends \Icybee\SaveOperation
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

		if (!$this->key || !$core->user->has_permission(Modules\Nodes\Module::PERMISSION_MODIFY_BELONGING_SITE))
		{
			$properties['siteid'] = $core->site_id;
		}

		return $properties;
	}
}