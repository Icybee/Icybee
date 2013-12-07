<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Taxonomy\Terms;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

use Icybee\Modules\Nodes\TitleSlugCombo;

class EditBlock extends \Icybee\EditBlock
{
	protected function lazy_get_children()
	{
		global $core;

		$vid_options = array(null => '') + $core->models['taxonomy.vocabulary']->select('vid, vocabulary')->pairs;

		/*
		 * Beware of the 'weight' property, because vocabulary also define 'weight' and will
		 * override the term's one.
		 */

		return array_merge
		(
			parent::lazy_get_children(), array
			(
				Term::TERM => new TitleSlugCombo
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
