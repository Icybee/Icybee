<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Search;

class ConfigOperation extends \Icybee\Operation\Module\Config
{
	protected function process()
	{
		global $core;

		$request = $this->request;

		$key = $this->module->flat_id . '.scope';
		$scope = null;

		if ($request['local'][$key])
		{
			$scope = implode(',', array_keys($request['local'][$key]));

			unset($request->params['local'][$key]);
		}

		$core->site->metas[$key] = $scope;

		return parent::process();
	}
}