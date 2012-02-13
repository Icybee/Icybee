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

use ICanBoogie\FileCache;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * Manages cache for thumbnails.
 *
 * The cache is always active.
 */
class CacheManager extends \ICanBoogie\Object implements \ICanBoogie\Modules\System\Cache\CacheInterface
{
	public $title = "Miniatures";
	public $description = "Miniatures générées à la volée par le module <q>Thumbnailer</q>.";
	public $group = 'resources';
	public $state = null;

	/**
	 * Configuration for the module.
	 *
	 * - cleanup_interval: The interval between cleanups, in minutes.
	 *
	 * - repository_size: The size of the repository, in Mo.
	 */
	static public $config = array
	(
		'cleanup_interval' => 15,
		'repository_size' => 8
	);

	protected function __get_config_preview()
	{
		global $core;

		$registry = $core->registry;

		$rc = t("La taille du cache ne dépasse pas :cache_sizeMo.", array('cache_size' => $registry['thumbnailer.cache_size'] ?: 8));
		$rc .= ' ' . t("Le cache est nettoyé toutes les :cleanup_interval minutes.", array('cleanup_interval' => $registry['thumbnailer.cleanup_interval'] ?: 15));

		return $rc;
	}

	protected function __get_editor()
	{
		global $core;

		$registry = $core->registry;

		return new Form
		(
			array
			(
				Form::RENDERER => 'Simple',
				Element::CHILDREN => array
				(
					'cache_size' => new Text
					(
						array
						(
							Form::LABEL => 'Taille maximale du cache',
							Text::ADDON => 'Mo',

							'size' => 5,
							'class' => 'measure',
							'value' => $registry['thumbnailer.cache_size'] ?: 8
						)
					),

					'cleanup_interval' => new Text
					(
						array
						(
							Form::LABEL => "Intervale entre deux nettoyages",
							Text::ADDON => 'minutes',

							'size' => 5,
							'class' => 'measure',
							'value' => $registry['thumbnailer.cleanup_interval'] ?: 15
						)
					),


				),

				'class' => 'stacked'
			)
		);
	}

	/**
	 * Path to the cache's directory.
	 *
	 * @return string
	 */
	protected function __get_path()
	{
		return \ICanBoogie\REPOSITORY . 'thumbnailer' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Handler for the cache entries.
	 *
	 * @return FileCache
	 */
	protected function __get_handler()
	{
		return new FileCache
		(
			array
			(
				FileCache::T_REPOSITORY => $this->path,
				FileCache::T_REPOSITORY_SIZE => self::$config['repository_size'] * 1024
			)
		);
	}

	public function enable()
	{

	}

	public function disable()
	{

	}

	public function stat()
	{
		return \ICanBoogie\Modules\System\Cache\Module::get_files_stat(\ICanBoogie\REPOSITORY . 'thumbnailer');
	}

	public function clear()
	{
		global $core;

		$files = glob(\ICanBoogie\REPOSITORY . 'thumbnailer/*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}


	/**
	 * Periodically clears the cache.
	 */
	public function clean()
	{
		$marker = \ICanBoogie\REPOSITORY . 'thumbnailer/.cleanup';

		$time = file_exists($marker) ? filemtime($marker) : 0;
		$interval = self::$config['cleanup_interval'] * 60;
		$now = time();

		if ($time + $interval > $now)
		{
			return;
		}

		$this->handler->clean();

		touch($marker);
	}

	public function config($params)
	{
		global $core;

		if (!empty($params['cache_size']))
		{
			$core->registry['thumbnailer.cache_size'] = (int) $params['cache_size'];
		}

		if (!empty($params['cleanup_interval']))
		{
			$core->registry['thumbnailer.cleanup_interval'] = (int) $params['cleanup_interval'];
		}
	}

	public function retrieve($key, array $callback, array $userdata)
	{
		$this->clean();

		return call_user_func_array(array($this->handler, 'get'), func_get_args());
	}
}