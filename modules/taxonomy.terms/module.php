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
use ICanBoogie\Event;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

class Module extends \Icybee\Module
{
	protected function block_manage()
	{
		return new Manager($this);
	}

	protected function block_edit(array $values, $permission)
	{
		global $core;

		$vid_options = array(null => '') + $core->models['taxonomy.vocabulary']->select('vid, vocabulary')->pairs;

		/* beware of the 'weight' property, because vocabulary also define 'weight' and will
		 * override the term's one */

		return array
		(
			Element::CHILDREN => array
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
				)/*,

				Term::WEIGHT => new Text
				(
					array
					(
						Form::LABEL => 'Weight'
					)
				)
				*/
			)
		);
	}
}