<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Journal;

use ICanBoogie\Operation;

class Entry extends \ICanBoogie\ActiveRecord
{
	const SEVERITY = 'severity';
	const SEVERITY_DEBUG = 0;
	const SEVERITY_INFO = 1;
	const SEVERITY_WARNING = 2;
	const SEVERITY_DANGER = 3;

	public $id;
	public $siteid;
	public $uid;
	public $type;
	public $class;
	public $serialized_message;
	public $severity;
	public $link;
	public $location;
	public $referer;
	public $timestamp;

	/**
	 * Creates a journal entry.
	 *
	 * The entry can be created from an operation.
	 *
	 * @param Operation|mixed $properties
	 * @param array $construct_args
	 * @param string $class_name
	 *
	 * @return Entry
	 */
	static public function from($properties=null, array $construct_args=array(), $class_name=null)
	{
		if ($properties instanceof Operation)
		{
			return static::from_operation($properties, $construct_args=array());
		}

		return parent::from($properties, $construct_args, $class_name);
	}

	/**
	 * Creates a journal entry from an operation.
	 *
	 * @param Operation $operation
	 * @param array $construct_args
	 *
	 * @return Entry
	 */
	static protected function from_operation(Operation $operation, array $construct_args=array())
	{
		global $core;

		/* @var $request \ICanBoogie\HTTP\Request */

		$request = $operation->request;

		$siteid = $request->context->site_id;
		$uid = (int) $core->user_id;
		$type = 'operation';
		$class = get_class($operation);
		$message = $operation->response->message;
		$location = $request->uri;
		$referer = $request->referer;

		return static::from(array(

			'siteid' => $siteid,
			'uid' => $uid,
			'type' => $type,
			'severity' => self::SEVERITY_INFO,
			'class' => $class,
			'serialized_message' => serialize($message),
			'location' => $location,
			'referer' => (string) $referer

		), $construct_args);
	}

	/**
	 * Defaults model to "journal".
	 */
	public function __construct($model='journal')
	{
		parent::__construct($model);
	}

	/**
	 * Returns the message unserialized.
	 *
	 * @return mixed
	 */
	protected function volatile_get_message()
	{
		return unserialize($this->serialized_message);
	}
}