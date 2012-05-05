<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Form;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Contact extends Form
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			\ICanBoogie\array_merge_recursive
			(
				$tags, array
				(
					Form::RENDERER => 'Simple',

					Element::CHILDREN => array
					(
						'gender' => new Element
						(
							Element::TYPE_RADIO_GROUP, array
							(
								Form::LABEL => 'Salutation',
								Element::OPTIONS => array('salutation.misses', 'salutation.mister'),
								Element::REQUIRED => true,

								'class' => 'inline-inputs'
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

						'lastname' => new Text
						(
							array
							(
								Form::LABEL => 'Lastname',
								Element::REQUIRED => true
							)
						),

						'company' => new Text
						(
							array
							(
								Form::LABEL => 'Company'
							)
						),

						'email' => new Text
						(
							array
							(
								Form::LABEL => 'E-mail',
								Element::REQUIRED => true,
								Element::VALIDATOR => array('Brickrouge\Form::validate_email')
							)
						),

						'message' => new Element
						(
							'textarea', array
							(
								Form::LABEL => 'Your message',
								Element::REQUIRED => true
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
			'notify_from' => 'Contact <no-reply@' . preg_replace('#^www#', '', $_SERVER['SERVER_NAME']) .'>',
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