<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\I18n;

use ICanBoogie\Modules\Pages\Model as PagesModel;

use Brickrouge\Element;
use Brickrouge\Form;

/**
 * An element to select the language of a node.
 */
class NodeNativeElement extends Element
{
	const CONSTRUCTOR = '#node-native-constructor';

	public function __construct(array $attributes=array())
	{
		global $core;

		$site = $core->site;
		$native = $site->native->language;

		parent::__construct
		(
			'select', $attributes + array
			(
				Form::LABEL => 'nativeid',
				Element::GROUP => 'i18n',
				Element::DESCRIPTION => t('nativeid', array('native' => $native, 'language' => $site->language), array('scope' => 'element.description'))
			)
		);
	}

	protected function render_inner_html_for_select()
	{
		global $core;

		$native = $core->site->native->language;
		$constructor = $this[self::CONSTRUCTOR];
		$options = array();

		if ($constructor == 'pages')
		{
			$nodes = $core->models['pages']
			->select('nid, parentid, title')
			->find_by_language($native)
			->order('weight, created')
			->all(\PDO::FETCH_OBJ);

			$tree = PagesModel::nestNodes($nodes);

			if ($tree)
			{
				PagesModel::setNodesDepth($tree);
				$records = PagesModel::levelNodesById($tree);

				foreach ($records as $record)
				{
					$options[$record->nid] = str_repeat("\xC2\xA0", $record->depth * 4) . $record->title;
				}
			}
		}
		else
		{
			$options = $core->models['nodes']
			->select('nid, title')
			->find_by_constructor_and_language($constructor, $native)
			->order('title')
			->pairs;

			foreach ($options as &$label)
			{
				$label = wd_shorten($label);
			}

			unset($label);
		}

		$this[self::OPTIONS] = array(null => 'none') + $options;

		return parent::render_inner_html_for_select();
	}
}