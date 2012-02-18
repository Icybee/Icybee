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
use ICanBoogie\Modules;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

class Hooks
{
	/**
	 * Getter for the mixin magic property `image`
	 *
	 * @param Node $ar
	 *
	 * @return ICanBoogie\ActiveRecord\Image|null
	 */
	static public function __get_image(Node $ar)
	{
		global $core;

		$imageid = $ar->metas['resources_images.imageid'];

		return $imageid ? $core->models['images'][$imageid] : null;
	}

	static public function editblock__on_alter_children(Event $event, \ICanBoogie\Modules\Nodes\EditBlock $block)
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

	static public function on_alter_block_config(Event $event, Modules\Contents\Module $sender)
	{
		global $core;

		$core->document->css->add('public/admin.css');
		$core->document->js->add('public/admin.js');

		$sender_flat_id = $sender->flat_id;

		$views = array
		(
			$sender . '/home' => array
			(
				'title' => 'Accueil des enregistrements'
			),

			$sender . '/list' => array
			(
				'title' => 'Liste des enregistrements'
			),

			$sender . '/view' => array
			(
				'title' => "Detail d'un enregistrement"
			)
		);

		$thumbnails = array();

		foreach ($views as $view_id => $view)
		{
			$id = wd_normalize($view_id);

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

		$event->tags = wd_array_merge_recursive
		(
			$event->tags, array
			(
				Element::GROUPS => array
				(
					'resources_images__inject' => array
					(
						'title' => 'Associated image',
						'title' => new Element(Element::TYPE_CHECKBOX, array
						(
							Element::LABEL => 'Associated image',

							'name' => 'global[resources_images.inject][' . $sender_flat_id . ']',
							'checked' => !empty($core->registry['resources_images.inject.' . $sender_flat_id])
						))
					),

					'resources_images__inject_options' => array
					(

					),

					'resources_images__inject_thumbnails' => array
					(
						'description' => 'Use the following elements to configure the
						thumbnails to create for the associated image. Each view provided by the
						module has its own thumbnail configuration:'
					)
				),

				Element::CHILDREN => array
				(
					/*
					'global[resources_images.inject][' . $sender_flat_id . ']' => new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							Element::LABEL => 'Associer une image aux enregistrements',
							Element::GROUP => 'resources_images__inject'
						)
					),
					*/

					'global[resources_images.inject][' . $sender_flat_id . '.required]' => new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							Element::LABEL => "L'association est obligatoire",
							Element::GROUP => 'resources_images__inject'
						)
					),

					'global[resources_images.inject][' . $sender_flat_id . '.default]' => new Widget\PopImage
					(
						array
						(
							Form::LABEL => "Image par défaut",
							Element::GROUP => 'resources_images__inject'
						)
					)
				)

				+ $thumbnails
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

			Debug::trigger('should call standard one !');

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
	 * This function is a callback for the `Icybee::render` event.
	 *
	 * @param Event $event
	 */
	public static function on_icybee_render(Event $event)
	{
		global $document;

		if (strpos($event->rc, 'rel="lightbox') === false)
		{
			return;
		}

		$document->css->add('public/slimbox.css');
		$document->js->add('public/slimbox.js');
	}

	static private $attached;

	public static function on_get_css_class(Event $event, ActiveRecord\Node $node)
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

		$event->rc['has-image'] = 'has-image';
	}
}