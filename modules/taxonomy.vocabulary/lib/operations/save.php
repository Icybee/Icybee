<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Taxonomy\Vocabulary;

use ICanBoogie\Modules;

class SaveOperation extends \ICanBoogie\SaveOperation
{
	protected function lazy_get_properties()
	{
		global $core;

		$request = $this->request;
		$properties = parent::lazy_get_properties();

		if ($request['scope'])
		{
			$properties['scope'] = $request['scope'];
		}

		if (!$this->key || !$core->user->has_permission(\Icybee\Modules\Nodes\Module::PERMISSION_MODIFY_BELONGING_SITE))
		{
			$properties['siteid'] = $core->site_id;
		}

		return $properties;
	}
}