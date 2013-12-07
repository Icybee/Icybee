<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Members;

use ICanBoogie\I18n;

use Brickrouge\Element;
use Brickrouge\Date;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Editor\RTEEditorElement;

class EditBlock extends \Icybee\Modules\Users\EditBlock
{
	protected function lazy_get_attributes()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::lazy_get_attributes(), array
			(
				Element::GROUPS => array
				(
					'numbers' => array
					(
						'title' => 'Numéros de téléphone'
					),

					'private' => array
					(
						'title' => 'Données privées'
					),

					'professional' => array
					(
						'title' => 'Données professionnelles'
					),

					'misc' => array
					(
						'title' => 'Informations complémentaires'
					),

					'attached' => array
					(
						'title' => 'Pièces attachées'
					)
				)
			)
		);
	}

	protected function lazy_get_children()
	{
		return array_merge
		(
			parent::lazy_get_children(), array
			(
				'salutation_code' => new Element
				(
					'select', array
					(
						Form::LABEL => 'Salutation',
						Element::REQUIRED => true,
						Element::WEIGHT => 'firstname:before',
						Element::OPTIONS => array
						(
							null => '',
							I18n\t('salutation.misses'),
							I18n\t('salutation.miss'),
							I18n\t('salutation.mister')
						)
					)
				),

				#
				# numbers
				#

				'number_work' => new Text
				(
					array
					(
						Form::LABEL => 'Travail',
						Element::GROUP => 'numbers'
					)
				),

				'number_home' => new Text
				(
					array
					(
						Form::LABEL => 'Domicile',
						Element::GROUP => 'numbers'
					)
				),

				'number_fax' => new Text
				(
					array
					(
						Form::LABEL => 'FAX',
						Element::GROUP => 'numbers'
					)
				),

				'number_pager' => new Text
				(
					array
					(
						Form::LABEL => 'Pager',
						Element::GROUP => 'numbers'
					)
				),

				'number_mobile' => new Text
				(
					array
					(
						Form::LABEL => 'Mobile',
						Element::GROUP => 'numbers'
					)
				),

				#
				# private
				#

				'street' => new Text
				(
					array
					(
						Form::LABEL => 'Rue',
						Element::GROUP => 'private'
					)
				),

				'street_complement' => new Text
				(
					array
					(
						Element::GROUP => 'private'
					)
				),

				'city' => new Text
				(
					array
					(
						Form::LABEL => 'Ville/Localité',
						Element::GROUP => 'private'
					)
				),

				'state' => new Text
				(
					array
					(
						Form::LABEL => 'État/Province',
						Element::GROUP => 'private'
					)
				),

				'postalcode' => new Text
				(
					array
					(
						Form::LABEL => 'Code postal',
						Element::GROUP => 'private'
					)
				),

				'country' => new Text
				(
					array
					(
						Form::LABEL => 'Pays',
						Element::GROUP => 'private'
					)
				),

				'webpage' => new Text
				(
					array
					(
						Form::LABEL => 'Page Web',
						Element::GROUP => 'private'
					)
				),

				'birthday' => new Date
				(
					array
					(
						Form::LABEL => 'Date de naissance',
						Element::GROUP => 'private'
					)
				),

				#
				# professional
				#

				'position' => new Text
				(
					array
					(
						Form::LABEL => 'Poste',
						Element::GROUP => 'professional'
					)
				),

				'service' => new Text
				(
					array
					(
						Form::LABEL => 'Service',
						Element::GROUP => 'professional'
					)
				),

				'company' => new Text
				(
					array
					(
						Form::LABEL => 'Société',
						Element::GROUP => 'professional'
					)
				),

				'company_street' => new Text
				(
					array
					(
						Form::LABEL => 'Rue',
						Element::GROUP => 'professional'
					)
				),

				'company_street_complement' => new Text
				(
					array
					(
						Element::GROUP => 'professional'
					)
				),

				'company_city' => new Text
				(
					array
					(
						Form::LABEL => 'Ville/Localité',
						Element::GROUP => 'professional'
					)
				),

				'company_state' => new Text
				(
					array
					(
						Form::LABEL => 'État/Province',
						Element::GROUP => 'professional'
					)
				),

				'company_postalcode' => new Text
				(
					array
					(
						Form::LABEL => 'Code postal',
						Element::GROUP => 'professional'
					)
				),

				'company_country' => new Text
				(
					array
					(
						Form::LABEL => 'Pays',
						Element::GROUP => 'professional'
					)
				),

				'company_webpage' => new Text
				(
					array
					(
						Form::LABEL => 'Page Web',
						Element::GROUP => 'professional'
					)
				),

				#
				# miscelaneous informations
				#

				'misc1' => new Text
				(
					array
					(
						Form::LABEL => 'Divers 1',
						Element::GROUP => 'misc'
					)
				),

				'misc2' => new Text
				(
					array
					(
						Form::LABEL => 'Divers 2',
						Element::GROUP => 'misc'
					)
				),

				'misc3' => new Text
				(
					array
					(
						Form::LABEL => 'Divers 3',
						Element::GROUP => 'misc'
					)
				),

				'misc4' => new Text
				(
					array
					(
						Form::LABEL => 'Divers 4',
						Element::GROUP => 'misc'
					)
				),

				'notes' => new RTEEditorElement
				(
					array
					(
						Form::LABEL => 'Notes',
						Element::GROUP => 'misc'
					)
				),

				#
				# photo
				#

				'photo' => new \Brickrouge\File
				(
					array
					(
						Form::LABEL => 'Photo',
						Element::GROUP => 'attached',
// 						Element::FILE_WITH_LIMIT => 256,
// 						Element::FILE_WITH_REMINDER => true
					)
				)
			)
		);
	}
}