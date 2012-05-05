<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Taxonomy\Vocabulary;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Modules;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

class Module extends \Icybee\Module
{
	const OPERATION_ORDER = 'order';

	protected function block_order($vid)
	{
		global $core;

		$document = $core->document;

		$document->js->add('public/order.js');
		$document->css->add('public/order.css');

		$terms = $core->models['taxonomy.terms']->where('vid = ?', $vid)->order('term.weight, vtid')->all;

		$rc  = '<form id="taxonomy-order" method="post">';
		$rc .= '<input type="hidden" name="#operation" value="' . self::OPERATION_ORDER . '" />';
		$rc .= '<input type="hidden" name="#destination" value="' . $this . '" />';
		$rc .= '<input type="hidden" name="' . Operation::KEY . '" value="' . $vid . '" />';
		$rc .= '<ol>';

		foreach ($terms as $term)
		{
			$rc .= '<li>';
			$rc .= '<input type="hidden" name="terms[' . $term->vtid . ']" value="' . $term->weight . '" />';
			$rc .= wd_entities($term->term);
			$rc .= '</li>';
		}

		$rc .= '</ol>';

		$rc .= '<div class="actions">';
		$rc .= '<button class="save">' . t('label.save') . '</button>';
		$rc .= '</div>';

		$rc .= '</form>';

		return $rc;
	}
}