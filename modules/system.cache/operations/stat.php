<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\System\Cache;

use Icybee;

/**
 * Returns the usage (memory, files) of a specified cache.
 */
class Stat extends Icybee\Operation\System\Cache\Base
{
	/**
	 * The method is defered to the "usage_<cache_id>" method.
	 *
	 * Using the mixin features of the Object class, one can add callbacks to get the usage of
	 * its cache.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		list($count, $label) = $this->{$this->callback}();

		$this->response->count = (int) $count;

		return $label;
	}

	public function get_files_stat($path, $pattern=null)
	{
		$root = $_SERVER['DOCUMENT_ROOT'];

		if (!file_exists($root . $path))
		{
			mkdir($root . $path, 0777, true);

			if (!file_exists($root . $path))
			{
				return array
				(
					0, '<span class="warn">Impossible de créer le dossier&nbsp: <em>' . $path . '</em></span>'
				);
			}
		}

		if (!is_writable($root . $path))
		{
			return array
			(
				0, '<span class="warn">Dossier vérouillé en écriture&nbsp: <em>' . $path . '</em></span>'
			);
		}

		$n = 0;
		$size = 0;

		$dh = opendir($root . $path);

		while (($file = readdir($dh)) !== false)
		{
			if ($file{0} == '.' || ($pattern && !preg_match($pattern, $file)))
			{
				continue;
			}

			$n++;
			$size += filesize($root . $path . '/' . $file);
		}

		if (!$n)
		{
			return array(0, 'Le cache est vide');
		}

		return array
		(
			$n, $n . ' fichiers<br /><span class="small">' . wd_format_size($size) . '</span>'
		);
	}

	protected function stat_core_assets()
	{
		global $core;

		$path = $core->config['repository.files'] . '/assets';

		return $this->get_files_stat($path);
	}

	protected function stat_core_catalogs()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';

		return $this->get_files_stat($path, '#^i18n_#');
	}

	protected function stat_core_configs()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';

		return $this->get_files_stat($path, '#^config_#');
	}

	protected function stat_core_modules()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';

		return $this->get_files_stat($path, '#^modules_#');
	}
}