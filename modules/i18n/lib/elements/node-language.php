<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\I18n;

use Brickrouge\Element;
use Brickrouge\Form;

/**
 * An element to select the language of a node.
 */
class NodeLanguageElement extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'select', $attributes + array
			(
				Form::LABEL => 'language',
				Element::DESCRIPTION => 'language',
				Element::OPTIONS => array
				(
					null => '.neutral'
				)

				+ $this->collect_options()
			)
		);
	}

	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('elements.js');
	}

	protected function collect_options()
	{
		global $core;

		$languages = $core->models['sites']->count('language');

		foreach ($languages as $language => $dummy)
		{
			$languages[$language] = $core->locale->conventions['localeDisplayNames']['languages'][$language];
		}

		return $languages;
	}
}