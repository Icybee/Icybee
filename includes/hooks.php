<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Event;
use ICanBoogie\Exception;

// TODO-20110622: move this to /publishr/hooks.php

class publisher_WdHooks
{
	static public function before_operation_components_all(Event $event)
	{
		global $core;

		$language = $core->user->language;

		if ($language)
		{
			$core->language = $language;
		}
	}

	/**
	 * This callback is used to alter the operation's response by adding the document's assets
	 * addresses.
	 *
	 * The callback is called when an event matches the 'operation.components/*' pattern.
	 *
	 * @param Event $event
	 */
	static public function operation_components_all(Event $event)
	{
		global $core;

		$operation = $event->operation;

		if (empty($core->document))
		{
			return;
		}

		$document = $core->document;

		$operation->response->assets = array
		(
			'css' => $document->css->get(),
			'js' => $document->js->get()
		);
	}
}