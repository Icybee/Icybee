<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Element;
use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\Modules\Pages\Model as PagesModel;

class WdI18nLinkElement extends Element
{
	const T_CONSTRUCTOR = '#i18n-constructor';

	public function __construct($tags)
	{
		global $core;

		$site = $core->site;
		$native = $site->native->language;

		parent::__construct
		(
			'select', $tags + array
			(
				Element::LABEL => '.nativeid',
				Element::LABEL_POSITION => 'before',
				Element::GROUP => 'i18n',

				Element::DESCRIPTION => t('nativeid', array(':native' => $native, ':language' => $site->language), array('scope' => array('element', 'description'))),

				'name' => Node::NATIVEID
			)
		);
	}

	public function __toString()
	{
		global $core;

		$native = $core->site->native->language;
		$constructor = $this->get(self::T_CONSTRUCTOR);
		$options = array();

		if ($constructor == 'pages')
		{
			$nodes = $core->models['pages']
			->select('nid, parentid, title')
			->find_by_language($native)
			->order('weight, created')
			->all(PDO::FETCH_OBJ);

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

		$this->set(self::OPTIONS, array(null => '.none') + $options);

		return parent::__toString();
	}
}