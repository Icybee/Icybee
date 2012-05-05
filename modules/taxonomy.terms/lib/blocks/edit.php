<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Taxonomy\Terms;

use ICanBoogie\ActiveRecord\Taxonomy\Term;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

class EditBlock extends \Icybee\EditBlock
{
	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		global $core;

		$vid_options = array(null => '') + $core->models['taxonomy.vocabulary']->select('vid, vocabulary')->pairs;

		/*
		 * Beware of the 'weight' property, because vocabulary also define 'weight' and will
		 * override the term's one.
		 */

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				Term::TERM => new Widget\TitleSlugCombo
				(
					array
					(
						Form::LABEL => 'Term',
						Element::REQUIRED => true
					)
				),

				Term::VID => new Element
				(
					'select', array
					(
						Form::LABEL => 'Vocabulary',
						Element::OPTIONS => $vid_options,
						Element::REQUIRED => true
					)
				)
			)
		);
	}
}
