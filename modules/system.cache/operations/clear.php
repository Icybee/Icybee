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

class Clear extends Icybee\Operation\System\Cache\Base
{
	protected function process()
	{
		return $this->{$this->callback}();
	}

	protected function clear_core_catalogs()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';

		$files = glob($_SERVER['DOCUMENT_ROOT'] . $path . '/i18n_*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}

	protected function clear_core_assets()
	{
		global $core;

		$path = $core->config['repository.files'] . '/assets';

		$files = glob($_SERVER['DOCUMENT_ROOT'] . $path . '/*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}

	protected function clear_core_configs()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';
		$files = glob($_SERVER['DOCUMENT_ROOT'] . $path . '/config_*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}

	protected function clear_core_modules()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';
		$files = glob($_SERVER['DOCUMENT_ROOT'] . $path . '/modules_*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}

	/**
	 * Deletes files in a directory according to a RegEx pattern.
	 *
	 * @param string $path Path to the directory where the files shoud be deleted.
	 * @param string|null $pattern RegEx pattern to delete matching files, or null to delete all
	 * files.
	 */
	public function clear_files($path, $pattern=null)
	{
		$root = $_SERVER['DOCUMENT_ROOT'];

		if (!is_dir($root . $path))
		{
			return false;
		}

		$n = 0;
		$dh = opendir($root . $path);

		while (($file = readdir($dh)) !== false)
		{
			if ($file{0} == '.' || ($pattern && !preg_match($pattern, $file)))
			{
				continue;
			}

			$n++;
			unlink($root . $path . '/' . $file);
		}

		return $n;
	}
}