<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Taxonomy;

use ICanBoogie\ActiveRecord\Taxonomy\Term;
use ICanBoogie\Event;

use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Widget;

use Icybee\Manager;

class Terms extends \Icybee\Module
{
	protected function block_manage()
	{
		return new Manager\Taxonomy\Terms($this);
	}

	protected function block_edit(array $values, $permission)
	{
		global $core;

		$vid_options = array(null => '') + $core->models['taxonomy.vocabulary']->select('vid, vocabulary')->pairs;

		/* beware of the 'weight' property, because vocabulary also define 'weight' and will
		 * override the term's one */

		return array
		(
			Element::T_CHILDREN => array
			(
				Term::TERM => new Widget\TitleSlugCombo
				(
					array
					(
						Form::T_LABEL => 'Term',
						Element::T_REQUIRED => true
					)
				),

				Term::VID => new Element
				(
					'select', array
					(
						Form::T_LABEL => 'Vocabulary',
						Element::T_OPTIONS => $vid_options,
						Element::T_REQUIRED => true
					)
				)/*,

				Term::WEIGHT => new Text
				(
					array
					(
						Form::T_LABEL => 'Weight'
					)
				)
				*/
			)
		);
	}
}