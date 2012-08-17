<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Images;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Events;
use ICanBoogie\Modules;
use ICanBoogie\Modules\Contents\ConfigBlock as ContentsConfigBlock;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

use Icybee\Views\ActiveRecord\Provider\AlterResultEvent;

class Hooks
{
	/**
	 * Getter for the mixin magic property `image`
	 *
	 * @param Node $ar
	 *
	 * @return ICanBoogie\ActiveRecord\Image|null
	 */
	static public function get_image(Node $ar)
	{
		global $core;

		$imageid = $ar->metas['resources_images.imageid'];

		return $imageid ? $core->models['images'][$imageid] : null;
	}

	/**
	 * Finds the images associated to the records provided to the view.
	 *
	 * In order to avoid each record of the view to load its own image during rendering, we load
	 * them all and update the records.
	 *
	 * The method is canceled if there is only 3 records because bunch loading takes 3 database
	 * requests.
	 *
	 * TODO-20120713: Use an event to load images when the first `image` property is accessed.
	 *
	 * @param AlterResultEvent $event
	 * @param \ICanBoogie\Modules\Contents\Provider $provider
	 */
	public static function on_contents_provider_alter_result(AlterResultEvent $event, \ICanBoogie\Modules\Contents\Provider $provider)
	{
		global $core;

		$result = $event->result;

		if (!is_array($result) || count($result) < 4 || !(current($result) instanceof \ICanBoogie\ActiveRecord\Content)
		|| !$core->registry['resources_images.inject.' . $event->module->flat_id])
		{
			return;
		}

		$record_keys = array();

		foreach ($result as $record)
		{
			$record_keys[] = $record->nid;
		}

		$image_keys = $core->models['system.registry/node']
		->select('targetid, value')
		->where(array('targetid' => $record_keys, 'name' => 'resources_images.imageid'))
		->where('value + 0 != 0')
		->pairs;

		if (!$image_keys)
		{
			return;
		}

		$images = $core->models['images']->find($image_keys);

		foreach ($result as $record)
		{
			$nid = $record->nid;

			if (empty($image_keys[$nid]))
			{
				continue;
			}

			$imageid = $image_keys[$nid];
			$record->image = $images[$imageid];
		}
	}

	public static function on_contents_editblock_alter_children(Event $event, \ICanBoogie\Modules\Nodes\EditBlock $block)
	{
		global $core;

		$flat_id = $event->module->flat_id;
		$inject = $core->registry['resources_images.inject.' . $flat_id];

		if (!$inject)
		{
			return;
		}

		$group = null;

		if (isset($event->attributes[Element::GROUPS]['contents']))
		{
			$group = 'contents';
		}

		$imageid = null;

		if ($block->record)
		{
			$imageid = $block->record->metas['resources_images.imageid'];
		}

		$event->children['resources_images[imageid]'] = new Widget\PopImage
		(
			array
			(
				Form::LABEL => 'Image',
				Element::GROUP => $group,
				Element::REQUIRED => $core->registry['resources_images.inject.' . $flat_id . '.required'],

				'value' => $imageid
			)
		);
	}

	/**
	 * Alters the config block of contents modules with controls for the associated image.
	 *
	 * @param Event $event
	 * @param \ICanBoogie\Modules\Contents\ConfigBlock $block
	 */
	public static function on_contents_configblock_alter_children(Event $event, ContentsConfigBlock $block)
	{
		global $core;

		$core->document->css->add('public/admin.css');
		$core->document->js->add('public/admin.js');

		$module_id = $event->module->id;

		$views = array
		(
			$module_id . '/home' => array
			(
				'title' => 'Accueil des enregistrements'
			),

			$module_id . '/list' => array
			(
				'title' => 'Liste des enregistrements'
			),

			$module_id . '/view' => array
			(
				'title' => "Detail d'un enregistrement"
			)
		);

		$thumbnails = array();

		foreach ($views as $view_id => $view)
		{
			$id = \ICanBoogie\normalize($view_id);

			$thumbnails["global[thumbnailer.versions][$id]"] = new Widget\PopThumbnailVersion
			(
				array
				(
					Element::GROUP => 'resources_images__inject_thumbnails',
					Form::LABEL => $view['title'],// . ' <span class="small">(' . $id . ')</span>',
					Element::DESCRIPTION => 'Identifiant de la version&nbsp;: <q>' . $id . '</q>.'
				)
			);
		}

		$target_flat_id = $event->module->flat_id;

		$event->children = array_merge
		(
			$event->children, array
			(
				"global[resources_images.inject][$target_flat_id]" => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'Associer une image aux enregistrements',
						Element::GROUP => 'resources_images__inject_toggler'
					)
				),

				"global[resources_images.inject][$target_flat_id.default]" => new Widget\PopImage
				(
					array
					(
						Form::LABEL => "Image par défaut",
						Element::GROUP => 'resources_images__inject'
					)
				),

				"global[resources_images.inject][$target_flat_id.required]" => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => "L'association est obligatoire",
						Element::GROUP => 'resources_images__inject'
					)
				)
			),

			$thumbnails
		);

		#
		# Listen to the block `alert_attributes` event to add our groups.
		#

		$event->attributes[Element::GROUPS] = array_merge
		(
			$event->attributes[Element::GROUPS], array
			(
				'resources_images__inject_toggler' => array
				(
					'title' => 'Associated image',
					'class' => 'group-toggler'
				),

				'resources_images__inject' => array
				(

				),

				'resources_images__inject_thumbnails' => array
				(
					'description' => 'Use the following elements to configure the
					thumbnails to create for the associated image. Each view provided by the
					module has its own thumbnail configuration:'
				)
			)
		);
	}

	public static function on_nodes_save(Event $event, \ICanBoogie\Modules\Nodes\SaveOperation $operation)
	{
		$params = &$event->request->params;

		if (!isset($params['resources_images']['imageid']))
		{
			return;
		}

		$entry = $operation->destination->model[$event->rc['key']];
		$imageid = $params['resources_images']['imageid'];

		$entry->metas['resources_images.imageid'] = $imageid ? $imageid : null;
	}

	public static function before_contents_config(Event $event, \ICanBoogie\Modules\Contents\ConfigOperation $operation)
	{
		if (!isset($event->request->params['global']['resources_images.inject']))
		{
			return;
		}

		$module_flat_id = $operation->destination->flat_id;
		$options = &$event->request->params['global']['resources_images.inject'];

		$options += array
		(
			$module_flat_id => false,
			$module_flat_id . '.required' => false,
			$module_flat_id . '.default' => null
		);

		$options[$module_flat_id] = filter_var($options[$module_flat_id], FILTER_VALIDATE_BOOLEAN);
		$options[$module_flat_id . '.required'] = filter_var($options[$module_flat_id . '.required'], FILTER_VALIDATE_BOOLEAN);
	}

	static public function textmark_images_reference(array $args, \Textmark_Parser $textmark, array $matches)
	{
		global $core;
		static $model;

		if (!$model)
		{
			$model = $core->models['images'];
		}

		$align = $matches[2];
		$alt = $matches[3];
		$id = $matches[4];

		# for shortcut links like ![this][].

		if (!$id)
		{
			$id = $alt;
		}

		$record = $model->where('nid = ? OR slug = ? OR title = ?', (int) $id, $id, $id)->order('created DESC')->one;

		if (!$record)
		{
			$matches[2] = $matches[3];
			$matches[3] = $matches[4];

			trigger_error('should call standard one !');

			//return parent::_doImages_reference_callback($matches);

			return;
		}

		$src = $record->path;
		$w = $record->width;
		$h = $record->height;

		$light_src = null;

		if ($w > 600)
		{
			$w = 600;
			$h = null;

			$light_src = $src;

			$src = $record->thumbnail("w:$w;method:fixed-width;quality:80")->url;
		}

		$params = array
		(
			'src' => $src,
			'alt' => $alt,
			'width' => $w,
			'height' => $h
		);

		if ($align)
		{
			switch ($align)
			{
				case '<': $align = 'left'; break;
				case '=':
				case '|': $align = 'middle'; break;
				case '>': $align = 'right'; break;
			}

			$params['align'] = $align;
		}

		$rc = new Element('img', $params);

		if ($light_src)
		{
			$rc = '<a href="' . $light_src . '" rel="lightbox[]">' . $rc . '</a>';
		}

		return $rc;
	}

	/**
	 * Adds assets to support lightbox links.
	 *
	 * This function is a callback for the `Icybee\Pagemaker::render` event.
	 *
	 * @param Event $event
	 */
	public static function on_icybee_render(\Icybee\Pagemaker\RenderEvent $event)
	{
		global $document;

		if (strpos($event->html, 'rel="lightbox') === false)
		{
			return;
		}

		$document->css->add('public/slimbox.css');
		$document->js->add('public/slimbox.js');
	}

	static private $attached;

	public static function on_alter_css_class_names(\ICanBoogie\ActiveRecord\Node\AlterCSSClassNamesEvent $event, ActiveRecord\Node $node)
	{
		global $core;

		if (self::$attached === null)
		{
			self::$attached = $core->models['system.registry/node']
			->select('targetid, value')
			->joins('INNER JOIN {prefix}nodes ON(targetid = nid)')
			->where('(siteid = 0 OR siteid = ?) AND name = "resources_images.imageid"', $core->site_id)
			->pairs;
		}

		if (empty(self::$attached[$node->nid]))
		{
			return;
		}

		$event->names['has-image'] = true;
	}
}