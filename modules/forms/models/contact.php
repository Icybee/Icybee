<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Form;

class contact_WdForm extends Form
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			wd_array_merge_recursive
			(
				$tags, array
				(
					Element::T_CHILDREN => array
					(
						'gender' => new Element
						(
							Element::E_RADIO_GROUP, array
							(
								Form::T_LABEL => '.Salutation',
								Element::T_OPTIONS => array('salutation.misses', 'salutation.miss', 'salutation.mister'),
								Element::T_REQUIRED => true
							)
						),

						'lastname' => new Element
						(
							Element::E_TEXT, array
							(
								Form::T_LABEL => '.Lastname',
								Element::T_REQUIRED => true
							)
						),

						'firstname' => new Element
						(
							Element::E_TEXT, array
							(
								Form::T_LABEL => '.Firstname',
								Element::T_REQUIRED => true
							)
						),

						'company' => new Element
						(
							Element::E_TEXT, array
							(
								Form::T_LABEL => '.Company'
							)
						),

						'email' => new Element
						(
							Element::E_TEXT, array
							(
								Form::T_LABEL => '.E-mail',
								Element::T_REQUIRED => true,
								Element::T_VALIDATOR => array('BrickRouge\Form::validate_email')
							)
						),

						'message' => new Element
						(
							'textarea', array
							(
								Form::T_LABEL => '.Your message',
								Element::T_REQUIRED => true
							)
						)
					)
				)
			),

			'div'
		);
	}

	static public function get_defaults()
	{
		global $core;

		return array
		(
			'notify_destination' => $core->user->email,
			'notify_from' => 'Contact <no-reply@' . preg_replace('#^www#', '', $_SERVER['HTTP_HOST']) .'>',
			'notify_subject' => 'Formulaire de contact',
			'notify_template' => <<<EOT
Un message a été posté depuis le formulaire de contact :

Nom : #{@gender.index('Mme', 'Mlle', 'M')} #{@lastname} #{@firstname}
<wdp:if test="@company">Société : #{@company}</wdp:if>
E-Mail : #{@email}

Message : #{@message}
EOT
		);
	}
}