<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Cache;

use ICanBoogie\Exception;
use ICanBoogie\Operation;

abstract class BaseOperation extends Operation
{
	protected $callback;
	static protected $internal = array('core.assets', 'core.catalogs', 'core.configs', 'core.modules');

	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function control(array $controls)
	{
		if (!parent::control($controls))
		{
			return false;
		}

		$operation_name = strtolower(substr(get_class($this), strlen(__NAMESPACE__) + 1, -9));
		$cache_id = $this->key;

		if ($operation_name != 'enable' && $operation_name != 'disable'
		|| (($operation_name == 'enable' || $operation_name == 'disable') && !in_array($cache_id, self::$internal)))
		{
			$this->callback = $callback = $operation_name . '_' . wd_normalize($this->key, '_');

			if (!$this->has_method($callback))
			{
				throw new Exception
				(
					"Unable to perform the %operation operation on the %name cache, the %callback callback is missing.", array
					(
						'%callback' => $callback,
						'%operation' => $operation_name,
						'%name' => $this->key
					),

					404
				);
			}
		}

		return true;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	protected function alter_core_config($name, $value)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . '/protected/all/config/core.php';

		$old = $value ? 'false' : 'true';
		$value = $value ? 'true' : 'false';

		$content = file_get_contents($path);
		$new_content = str_replace("'cache $name' => $old", "'cache $name' => $value", $content);

		if ($content == $new_content)
		{
			return false;
		}

		file_put_contents($path, $new_content);

		return true;
	}
}