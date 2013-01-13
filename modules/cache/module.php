<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Cache;

use ICanBoogie\I18n;

class Module extends \Icybee\Module
{
	static public function get_files_stat($path, $pattern=null)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;

		if (!file_exists($path))
		{
			$path = $root . $path;
		}

		if (!file_exists($path))
		{
			mkdir($path, 0705, true);

			if (!file_exists($path))
			{
				return array
				(
					0, '<span class="warning">Impossible de créer le dossier&nbsp: <em>' . \ICanBoogie\strip_root($path) . '</em></span>'
				);
			}
		}

		if (!is_writable($path))
		{
			return array
			(
				0, '<span class="warning">Dossier vérouillé en écriture&nbsp: <em>' . \ICanBoogie\strip_root($path) . '</em></span>'
			);
		}

		$n = 0;
		$size = 0;
		$iterator = new \DirectoryIterator($path);

		if ($pattern)
		{
			$iterator = new \RegexIterator($iterator, $pattern);
		}

		foreach ($iterator as $file)
		{
			$filename = $file->getFilename();

			if ($filename{0} == '.')
			{
				continue;
			}

			++$n;
			$size += $file->getSize();
		}

		return array
		(
			$n, I18n\t(':count files<br /><span class="small">:size</span>', array(':count' => $n, 'size' => \ICanBoogie\I18n\format_size($size)))
		);
	}

	static public function get_vars_stat($regex)
	{
		global $core;

		$n = 0;
		$size = 0;

		foreach ($core->vars->matching($regex) as $pathname => $fileinfo)
		{
			++$n;
			$size += $fileinfo->getSize();
		}

		return array
		(
			$n, I18n\t(':count files<br /><span class="small">:size</span>', array(':count' => $n, 'size' => \ICanBoogie\I18n\format_size($size)))
		);
	}

	/**
	 * Deletes files in a directory according to a RegEx pattern.
	 *
	 * @param string $path Path to the directory where the files shoud be deleted.
	 * @param string|null $pattern RegEx pattern to delete matching files, or null to delete all
	 * files.
	 */
	static public function clear_files($path, $pattern=null)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;

		if (strpos($path, $root) !== 0)
		{
			$path = $root . $path;
		}

		if (!is_dir($path))
		{
			return false;
		}

		$n = 0;
		$dh = opendir($path);

		while (($file = readdir($dh)) !== false)
		{
			if ($file{0} == '.' || ($pattern && !preg_match($pattern, $file)))
			{
				continue;
			}

			$n++;
			unlink($path . '/' . $file);
		}

		return $n;
	}
}