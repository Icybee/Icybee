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
		return \ICanBoogie\REPOSITORY . 'thumbnailer' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Creates the repository folder where generated thumbnails are saved.
	 *
	 * @see Module::install()
	 */
	public function install(Errors $errors)
	{
		$path = \ICanBoogie\REPOSITORY . 'thumbnailer' .  DIRECTORY_SEPARATOR;

		if (!file_exists($path))
		{
			$parent = dirname($path);

			if (is_writable($parent))
			{
				mkdir($path, 0755, true);
			}
			else
			{
				$errors[$this->id] = t('Unable to create %directory directory, its parent is not writable', array('%directory' => wd_strip_root($path)));
			}
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
		$path = \ICanBoogie\REPOSITORY . 'thumbnailer';

		if (!file_exists($path))
		{
			$errors[$this->id] = t('The %directory directory is missing.', array('%directory' => wd_strip_root($path)));
		}

		return 0 == count($errors);
	}
}