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

class Module extends \Icybee\Module
{
	public static function get_files_stat($path, $pattern=null)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;

		if (!file_exists($path))
		{
			$path = $root . $path;
		}

		if (!file_exists($path))
		{
			mkdir($path, 0777, true);

			if (!file_exists($path))
			{
				return array
				(
					0, '<span class="warn">Impossible de créer le dossier&nbsp: <em>' . wd_strip_root($path) . '</em></span>'
				);
			}
		}

		if (!is_writable($path))
		{
			return array
			(
				0, '<span class="warn">Dossier vérouillé en écriture&nbsp: <em>' . wd_strip_root($path) . '</em></span>'
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

		if (!$n)
		{
			return array(0, 'Le cache est vide');
		}

		return array
		(
			$n, $n . ' fichiers<br /><span class="small">' . wd_format_size($size) . '</span>'
		);
	}

	public static function get_vars_stat($regex)
	{
		global $core;

		$n = 0;
		$size = 0;

		foreach ($core->vars->matching($regex) as $pathname => $fileinfo)
		{
			++$n;
			$size += $fileinfo->getSize();
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

	/**
	 * Deletes files in a directory according to a RegEx pattern.
	 *
	 * @param string $path Path to the directory where the files shoud be deleted.
	 * @param string|null $pattern RegEx pattern to delete matching files, or null to delete all
	 * files.
	 */
	public static function clear_files($path, $pattern=null)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;

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