<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\ActiveRecord;

use ICanBoogie\Operation;

/**
 * Extends the ICanBoogie\Operation\ActiveRecord\Save class to add save mode (continue, list, new).
 */
class Save extends Operation\ActiveRecord\Save
{
	const MODE = '#operation-save-mode';
	const MODE_CONTINUE = 'continue';
	const MODE_LIST = 'list';
	const MODE_NEW = 'new';

	/**
	 * @var string The save mode of the operation, one of the following: MODE_CONTINUE, MODE_LIST
	 * or MODE_NEW.
	 */
	protected $mode;

	/**
	 * Update the session save mode according to the save mode defined in the operation parameters.
	 *
	 * @see ICanBoogie.Operation::control()
	 */

	protected function control(array $controls)
	{
		global $core;

		$this->mode = $mode = $this->request[self::MODE];

		if ($mode)
		{
			$core->session->wdpmodule[self::MODE][(string) $this->module] = $mode;
		}

		return parent::control($controls);
	}

	/**
	 * Changes the operation location depending on the save mode.
	 *
	 * - list: The constructor index location.
	 * - continue: The record edit location.
	 * - new: The edit location for new records.
	 *
	 * @see ICanBoogie\Operation\ActiveRecord.Save::process()
	 */
	protected function process()
	{
		global $core;

		$rc = parent::process();
		$mode = $this->mode;

		if ($mode)
		{
			$route = '/admin/' . (string) $this->module;

			switch ($mode)
			{
				case self::MODE_CONTINUE:
				{
					$route .= '/' . $rc['key'] . '/edit';
				}
				break;

				case self::MODE_NEW:
				{
					$route .= '/new';
				}
				break;
			}

			$this->response->location = $core->site->path . $route;
		}

		return $rc;
	}
}