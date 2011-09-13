<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Widget;

class Thumbnailer
{
	/**
	 * Callback for the `thumbnail` getter added to the active records of the "images" module.
	 *
	 * The thumbnail is created using options of the 'primary' version.
	 *
	 * @param ICanBoogie\ActiveRecord\Image $ar An active record of the "images" module.
	 * @return string The URL of the thumbnail.
	 */
	static public function method_get_thumbnail(ActiveRecord\Image $ar)
	{
		return self::method_thumbnail($ar, 'primary');
	}

	/**
	 * Callback for the `thumbnail()` method added to the active records of the "images" module.
	 *
	 * @param ICanBoogie\ActiveRecord\Image $ar An active record of the "images" module.
	 * @param string $version The version used to create the thumbnail, or a number of options
	 * defined as CSS properties. e.g. 'w:300;h=200'.
	 * @return string The URL of the thumbnail.
	 */
	static public function method_thumbnail(ActiveRecord\Image $ar, $version)
	{
		/*
		$base = '/api/' . $ar->constructor . '/' . $ar->nid . '/thumbnail';

		if (strpos($version, ':') !== false)
		{
			$args = self::parse_style($version);

			return $base . '?' . http_build_query($args, null, '&');
		}

		return $base . 's/' . $version;
		*/

		return new Module\Thumbnailer\Thumbnail($ar, $version);
	}

	/*
	static private function parse_style($style)
	{
		preg_match_all('#([^:]+):\s*([^;]+);?#', $style, $matches, PREG_PATTERN_ORDER);

		return array_combine($matches[1], $matches[2]);
	}
	*/

	/**
	 * Callback for the `alter.block.config` event, adding AdjustThumbnail elements to the
	 * `config` block if image versions are defined for the constructor.
	 *
	 * @param Event $ev
	 */
	static public function on_alter_block_config(Event $event, Module $sender)
	{
		global $core;

		$module_id = (string) $sender;

		$c = $core->configs->synthesize('thumbnailer', 'merge');

		$configs = array();

		foreach ($c as $version_name => $config)
		{
			if (empty($config['module']) || $config['module'] != $module_id)
			{
				continue;
			}

			$configs[$version_name] = $config;
		}

		if (!$configs)
		{
			return;
		}

		$core->document->css->add('assets/admin.css');

		$children = array();

		foreach ($configs as $version_name => $config)
		{
			list($defaults) = $config;

			$config += array
			(
				'description' => null
			);

			$children['global[thumbnailer.versions][' . $version_name . ']'] = new Widget\PopThumbnailVersion
			(
				array
				(
					Form::T_LABEL => $config['title'] . ' <small>(' . $version_name . ')</small>',
					Element::T_DEFAULT => $defaults,
					Element::T_GROUP => 'thumbnailer',
					Element::T_DESCRIPTION => $config['description'],

					'value' => $core->registry["thumbnailer.verison.$version_name"]
				)
			);
		}

		$event->tags = wd_array_merge_recursive
		(
			$event->tags, array
			(
				Element::T_GROUPS => array
				(
					'thumbnailer' => array
					(
						'title' => 'Miniatures',
						'class' => 'form-section flat',
						'description' => "Ce groupe permet de configurer les différentes
						versions de miniatures qu'il est possible d'utiliser pour
						les entrées de ce module."
					)
				),

				Element::T_CHILDREN => $children
			)
		);
	}

	/**
	 * Callback for the `properties:before` event, pre-parsing thumbnailer versions if they are
	 * defined.
	 *
	 * @param Event $ev
	 */
	static public function before_config_properties(Event $event)
	{
		global $core;

		$properties = &$event->properties;

		if (empty($properties['global']['thumbnailer.versions']))
		{
			return;
		}

		$config = $core->configs->synthesize('thumbnailer', 'merge');

		foreach ($properties['global']['thumbnailer.versions'] as $name => &$options)
		{
			if (is_string($options))
			{
				$options = json_decode($options, true);
			}

			$options = (array) $options;

			$options += (isset($config[$name][0]) ? $config[$name][0] : array()) + array
			(
				'no-upscale' => false,
				'interlace' => false
			);

			$options['no-upscale'] = filter_var($options['no-upscale'], FILTER_VALIDATE_BOOLEAN);
			$options['interlace'] = filter_var($options['interlace'], FILTER_VALIDATE_BOOLEAN);

			$options = (empty($options['w']) && empty($options['h'])) ? null : json_encode($options);
		}
	}

	/*
	 * SYSTEM.CACHE SUPPORT
	 */

	static public function on_alter_block_manage(Event $event)
	{
		global $core;

		$event->caches['thumbnails'] = array
		(
			'title' => 'Miniatures',
			'description' => "Miniatures générées à la volée par le module <q>Thumbnailer</q>.",
			'group' => 'resources',
			'state' => null,
			'size_limit' => array(4, 'Mo'),
			'time_limit' => array(7, 'Jours')
		);
	}

	static public function method_stat_cache(Operation\System\Cache\Stat $operation)
	{
		global $core;

		$path = $core->config['repository.cache'] . '/thumbnailer';

		return $operation->get_files_stat($path);
	}

	static public function method_clear_cache(Operation\System\Cache\Clear $operation)
	{
		global $core;

		$path = $core->config['repository.cache'] . '/thumbnailer';

		$files = glob($_SERVER['DOCUMENT_ROOT'] . $path . '/*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}
}