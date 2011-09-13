<?php

/*
 * This file is part of the Element package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;

class WdEMailNotifyElement extends Form\Section
{
	protected $elements;

	public function __construct($tags)
	{
		parent::__construct
		(
			'div', $tags + array
			(
				Element::T_CHILDREN => array
				(
					'subject' => $this->elements['subject'] = new Text
					(
						array
						(
							Form::T_LABEL => 'Sujet du message',
							Element::T_REQUIRED => true
						)
					),

					'from' => $this->elements['from'] = new Text
					(
						array
						(
							Form::T_LABEL => 'Adresse d\'expédition',
							Element::T_REQUIRED => true
						)
					),

					'bcc' => $this->elements['bcc'] = new Text
					(
						array
						(
							Form::T_LABEL => 'Copie cachée',
						)
					),

					'template' => $this->elements['template'] = new Element
					(
						'textarea', array
						(
							Form::T_LABEL => 'Patron du message',
							Element::T_REQUIRED => true,
							'rows' => 8
						)
					)
				),

				'class' => 'combo'
			)
		);

		$group = $this->get(self::T_GROUP);

		if ($group)
		{
			$this->set(self::T_GROUP, $group);
		}
	}

	public function set($name, $value=null)
	{
		switch ($name)
		{
			case self::T_GROUP:
			{
				foreach ($this->elements as $el)
				{
					$el->set($name, $value);
				}
			}
			break;

			case self::T_DEFAULT:
			{
				foreach ($value as $identifier => $default)
				{
					$this->elements[$identifier]->set(self::T_DEFAULT, $default);
				}
			}
			break;

			case 'name':
			{
				foreach ($this->elements as $identifier => $el)
				{
					$el->set($name, $value . '[' . $identifier . ']');
				}

				return;
			}
			break;

			case 'value':
			{
				// TODO-20091204: should handle value

				//wd_log(__CLASS__ . '# set value: \1', array($value));

				return;
			}
			break;
		}

		parent::set($name, $value);
	}
}