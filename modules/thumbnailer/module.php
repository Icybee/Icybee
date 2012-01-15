<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\Errors;

/**
 * @property string $repository Path to the thumbnails repository.
 */
class Module extends \ICanBoogie\Module
{
	/**
	 * Getter for the $repository magic property.
	 */
	protected function __get_repository()
	{
		global $core;

		return $core->config['repository.cache'] ? $core->config['repository.cache'] . '/thumbnailer' : null;
	}

	/**
	 * Creates the repository folder where generated thumbnails are saved.
	 *
	 * @see Module::install()
	 */
	public function install(Errors $errors)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;
		$path = $this->repository;

		if ($path)
		{
			$path = $root . $path;

			if (!file_exists($path))
			{
				$parent = dirname($path);

				if (is_writable($parent))
				{
					mkdir($root . $repository, 0755, true);
				}
				else
				{
					$errors[$this->id] = t('Unable to create %directory directory, its parent is not writtable', array('%directory' => $path));
				}
			}
		}
		else
		{
			$errors[$this->id] = t('The %var var is empty is core config', array('%var' => 'repository.cache'));
		}

		return 0 == count($errors);
	}

	/**
	 * Check if the repository folder has been created.
	 *
	 * @see Module::is_installed()
	 */
	public function is_installed(Errors $errors)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;
		$path = $this->repository;

		if (!file_exists($root . $path))
		{
			$errors[$this->id] = t('The %directory directory is missing.', array('%directory' => $path));
		}

		return 0 == count($errors);
	}
}