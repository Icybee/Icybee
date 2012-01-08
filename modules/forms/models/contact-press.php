<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Element;
use BrickRouge\Form;

class press_WdForm extends Form
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			wd_array_merge_recursive
			(
				$tags, array
				(
					Element::CHILDREN => array
					(
						'gender' => new Element
						(
							Element::TYPE_RADIO_GROUP, array
							(
								Form::LABEL => 'Gender',
								Element::OPTIONS => array('salutation.misses', 'salutation.miss', 'salutation.mister'),
								Element::REQUIRED => true
							)
						),

						'lastname' => new Text
						(
							array
							(
								Form::LABEL => 'Lastname',
								Element::REQUIRED => true
							)
						),

						'firstname' => new Text
						(
							array
							(
								Form::LABEL => 'Firstname',
								Element::REQUIRED => true
							)
						),

						'media' => new Text
						(
							array
							(
								Form::LABEL => 'Média'
							)
						),

						'email' => new Text
						(
							array
							(
								Form::LABEL => 'E-Mail',
								Element::REQUIRED => true,
								Element::VALIDATOR => array('BrickRouge\Form::validate_email')
							)
						),

						'subject' => new Text
						(
							array
							(
								Form::LABEL => 'Subject',
								Element::REQUIRED => true
							)
						),

						'message' => new Element
						(
							'textarea', array
							(
								Form::LABEL => 'Your message'
							)
						)
					)
				)
			)
		);
	}

	static public function get_defaults()
	{
		global $core;

		return array
		(
			'notify_destination' => $core->user->email,
			'notify_bcc' => $core->user->email,
			'notify_from' => 'Contact <no-reply@wdpublisher.com>',
			'notify_subject' => 'Formulaire de contact presse',
			'notify_template' => <<<EOT
Un message a été posté depuis le formulaire de contact presse :

Nom : #{@gender.index('Mme', 'Mlle', 'M')} #{@lastname} #{@firstname}
Média : #{@media.or('N/C')}
E-Mail : #{@email}

Message :

#{@message}
EOT
		);
	}
}