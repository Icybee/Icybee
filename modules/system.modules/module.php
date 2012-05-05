<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Modules;

use Brickrouge\Button;

use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Element;
use Brickrouge\Form;

use Icybee;

class Module extends \Icybee\Module
{
	const MANAGE_MODE = '#manage-mode';
	const MANAGE_MODE_INSTALLER = 'installer';

	const OPERATION_ACTIVATE = 'activate';
	const OPERATION_DEACTIVATE = 'deactivate';

	protected function block_install($module_id)
	{
		global $core;

		if (!$core->user->has_permission(self::PERMISSION_ADMINISTER, $this))
		{
			return '<div class="alert alert-error">' . t('You don\'t have enought privileges to install packages.') . '</div>';
		}

		if (empty($core->modules[$module_id]))
		{
			return '<div class="alert alert-error">' . t('The module %module_id does not exists.', array('%module_id' => $module_id)) . '</div>';
		}

		$errors = new \ICanBoogie\Errors;
		$module = $core->modules[$module_id];

		$is_installed = $module->is_installed($errors);

		if ($is_installed && !count($errors))
		{
			return '<div class="alert alert-error">' . t('The module %module is already installed', array('%module' => $module_id)) . '</div>';
		}

		$errors->clear();
		$is_installed = $module->install($errors);

		if (!$is_installed || count($errors))
		{
			return '<div class="alert alert-error">' . t('Unable to install the module %module', array('%module' => $module_id)) . '</div>';
		}

		return '<div class="alert alert-success">' . t('The module %module has been installed. <a href="' . $core->site->path . '/admin/' . $this . '">Retourner à la liste.</a>', array('%module' => $module_id)) . '</div>';
	}
}