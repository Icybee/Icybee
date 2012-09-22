<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Files;

use ICanBoogie\Uploaded;

class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_UPLOAD = 'upload';
	const OPERATION_UPLOAD_RESPONSE = 'uploadResponse';

	const SESSION_UPLOAD_RESPONSE = 'resources.files.upload.responses';

	static protected $repository = array();

	static protected function repository($name)
	{
		global $core;

		if (empty(self::$repository[$name]))
		{
			self::$repository[$name] = $core->config['repository'] . '/' . $name . '/';
		}

		return self::$repository[$name];
	}

	/**
	 * Overrides the method to create the "/repository/tmp/" and "/repository/files/" directories,
	 * and add a ".htaccess" file in the "/repository/tmp/" direcotry which denies all access and
	 * a ".htaccess" file in the "/repository/files/" directory which allows all access.
	 *
	 * @see ICanBoogie.Module::install()
	 */
	public function install(\ICanBoogie\Errors $errors)
	{
		global $core;

		$root = \ICanBoogie\DOCUMENT_ROOT;
		$path = $core->config['repository.temp'];

		if ($path)
		{
			$path = $root . $path;

			if (!file_exists($path))
			{
				$parent = dirname($path);

				if (is_writable($parent))
				{
					mkdir($path);

					file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'Deny from all');
				}
				else
				{
					$errors[$this->id] = t('Unable to create %directory directory, its parent is not writtable', array('%directory' => $path));
				}
			}
		}
		else
		{
			$errors[$this->id] = t('The %var var is empty is core config', array('%var' => 'repository.temp'));
		}

		$path = $core->config['repository.files'];

		if ($path)
		{
			$path = $root . $path;

			if (!file_exists($path))
			{
				$parent = dirname($path);

				if (is_writable($parent))
				{
					mkdir($path);

					file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'Allow from all');
				}
				else
				{
					$errors[$this->id] = t('Unable to create %directory directory, its parent is not writtable', array('%directory' => $path));
				}
			}
		}
		else
		{
			$errors[$this->id] = t('The %var var is empty is core config', array('%var' => 'repository.files'));
		}

		return parent::install($errors);
	}

	/**
	 * Overrides the method to check if the "tmp" and "files" directories exist in the repository.
	 *
	 * @see ICanBoogie.Module::is_installed()
	 */
	public function is_installed(\ICanBoogie\Errors $errors)
	{
		global $core;

		$root = \ICanBoogie\DOCUMENT_ROOT;
		$path = $core->config['repository.temp'];

		if (!is_dir($root . $path))
		{
			$errors[$this->id] = t('The %directory directory is missing.', array('%directory' => $path));
		}

		$path = $core->config['repository.files'];

		if (!is_dir($root . $path))
		{
			$errors[$this->id] = t('The %directory directory is missing.', array('%directory' => $path));
		}

		return parent::is_installed($errors);
	}

	public function clean_repository($repository=':repository.temp', $lifetime=3600)
	{
		global $core;

		$root = $_SERVER['DOCUMENT_ROOT'];

		if ($repository{0} == ':')
		{
			$repository = $core->config[substr($repository, 1)];
		}

		if (!is_dir($root . $repository))
		{
			\ICanBoogie\log_error('The directory %directory does not exists', array('%directory' => $repository));

			return;
		}

		if (!is_writable($root . $repository))
		{
			\ICanBoogie\log_error('The directory %directory is not writtable', array('%directory' => $repository));

			return;
		}

		$dh = opendir($root . $repository);

		if (!$dh)
		{
			return;
		}

		$now = time();
		$location = getcwd();

		chdir($root . $repository);

		while ($file = readdir($dh))
		{
			if ($file{0} == '.')
			{
				continue;
			}

			$stat = stat($file);

			if ($now - $stat['ctime'] > $lifetime)
			{
				unlink($file);

				\ICanBoogie\log
				(
					'The temporary file %file has been deleted form the repository %directory', array
					(
						'%file' => $file,
						'%directory' => $repository
					)
				);
			}
		}

		chdir($location);

		closedir($dh);
	}
}