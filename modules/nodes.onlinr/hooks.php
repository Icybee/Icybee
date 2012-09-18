<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes\Onlinr;

use ICanBoogie\ActiveRecord\RecordNotFound;
use ICanBoogie\Event;
use ICanBoogie\Exception;

use Brickrouge\Element;
use Brickrouge\Form;

class Hooks
{
	/**
	 * This event callback adds a new element to the "online" group defined by the system.nodes
	 * module.
	 *
	 * @param Event $event
	 */
	public static function on_alter_block_edit(Event $event)
	{
		global $core;

		$nid = $event->key;

		if ($nid)
		{
			try
			{
				$onlinr = $core->models['nodes.onlinr'][$nid];

				$event->tags[Form::VALUES]['nodes_onlinr'] = (array) $onlinr;
			}
			catch (RecordNotFound $e) {}
		}

		//\ICanBoogie\log('onlinr: \1', array($onlinr));

		$event->tags = \ICanBoogie\array_merge_recursive
		(
			$event->tags, array
			(
				Element::CHILDREN => array
				(
					'nodes_onlinr' => new Element\DateRange
					(
						array
						(
							Element::GROUP => 'visibility',
							Element::WEIGHT => 100,

							Element::DESCRIPTION => "Les dates de <em>publication</em> et de
							<em>dépublication</em> permettent de définir un intervalle pendant
							lequel l'entrée est visible. Si la date de publication est définie,
							l'entrée sera visible à partir de la date définie. Si la date de
							dépublication est définie, l'entrée ne sera plus visible à partir de
							la date définie.",

							Element\DateRange::T_START_TAGS => array
							(
								Element::LABEL => 'Publication',

								'name' => 'nodes_onlinr[publicize]'
							),

							Element\DateRange::T_FINISH_TAGS => array
							(
								Element::LABEL => 'Dépublication',

								'name' => 'nodes_onlinr[privatize]'
							)
						)
					)
				)
			)
		);
	}
}
