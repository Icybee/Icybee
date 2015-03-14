<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class WdEMailNotifyElement extends \Brickrouge\Group
{
	protected $elements;

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			$attributes + array
			(
				Element::CHILDREN => array
				(
					'subject' => $this->elements['subject'] = new Text
					(
						array
						(
							Form::LABEL => "Subject",
							Element::REQUIRED => true
						)
					),

					'from' => $this->elements['from'] = new Text
					(
						array
						(
							Form::LABEL => "Sender address",
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => $this->app->site->email,
							Element::VALIDATOR => array('Brickrouge\Form::validate_email')
						)
					),

					'bcc' => $this->elements['bcc'] = new Text
					(
						array
						(
							Form::LABEL => "Blind copy",
						)
					),

					'template' => $this->elements['template'] = new Element
					(
						'textarea', array
						(
							Form::LABEL => "Message template",
							Element::REQUIRED => true,

							'rows' => 8
						)
					)
				),

				'class' => 'combo'
			)
		);
	}

	/**
	 * Forward the `DEFAULT_VALUE` and `name` attribute to its children.
	 */
	public function offsetSet($offset, $value)
	{
		switch ($offset)
		{
			case self::DEFAULT_VALUE:
			{
				foreach ($value as $identifier => $default)
				{
					$this->elements[$identifier][self::DEFAULT_VALUE] = $default;
				}
			}
			break;

			case 'name':
			{
				foreach ($this->elements as $identifier => $el)
				{
					$el[$offset] = $value . '[' . $identifier . ']';
				}
			}
			break;
		}

		parent::offsetSet($offset, $value);
	}
}
