<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use ICanBoogie\Event;
use ICanBoogie\Operation;

class Form extends \Brickrouge\Form
{
	/**
	 * Tries to load the form associated with the operation.
	 *
	 * Booleans that were found in the form when it was stored are initialized to "0" and merged
	 * with the request params.
	 *
	 * This function is a callback for the `ICanBoogie\Operation::get_form` event. The event chain
	 * is stopped if the form is found.
	 *
	 * @param Operation\GetFormEvent $event
	 * @param Operation $operation
	 *
	 * @throws Operation\FormHasExpired
	 */
	static public function on_operation_get_form(Operation\GetFormEvent $event, Operation $operation)
	{
		$request = $event->request;

		if (!$request[self::STORED_KEY_NAME])
		{
			return;
		}

		try
		{
			$form = self::load($request->params);
		}
		catch (\Exception $e)
		{
			throw new Operation\FormHasExpired($e->getMessage());
		}

		if ($form)
		{
			if ($form->booleans)
			{
				$qs = implode('=0&', array_keys($form->booleans)) . '=0';

				parse_str($qs, $q);

				$request->params = \ICanBoogie\exact_array_merge_recursive($q, $request->params);
			}

			$event->form = $form;
			$event->stop();
		}
	}
}
