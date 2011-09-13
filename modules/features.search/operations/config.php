<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Features\Search;

class Config extends \Icybee\Operation\Module\Config
{
	protected function process()
	{
		global $core;

		$params = &$this->params;

		$key = $this->module->flat_id . '.scope';
		$scope = null;

		if (isset($params['local'][$key]))
		{
			$scope = implode(',', array_keys($params['local'][$key]));

			unset($params['local'][$key]);
		}

		$core->site->metas[$key] = $scope;

		return parent::process();
	}
}