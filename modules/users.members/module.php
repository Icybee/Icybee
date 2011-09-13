<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Users;

use ICanBoogie\Module;
use BrickRouge\Element;
use BrickRouge\Form;

class Members extends Module\Users
{
	protected function block_edit(array $properties, $permission)
	{
		return array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Element::T_GROUPS => array
				(
					'numbers' => array
					(
						'title' => 'Numéros de téléphone',
						'class' => 'form-section flat',
						'template' => <<<EOT
<table class="panel">
<tr><td class="label">{\$number_work.label:}</td><td>{\$number_work}</td>
<td class="label">{\$number_fax.label:}</td><td>{\$number_fax}</td></tr>
<tr><td class="label">{\$number_home.label:}</td><td>{\$number_home}</td>
<td class="label">{\$number_pager.label:}</td><td>{\$number_pager}</td></tr>
<tr><td class="label">{\$number_mobile.label:}</td><td>{\$number_mobile}</td><td colspan="2">&nbsp;</td></tr>
</table>
EOT
					),

					'private' => array
					(
						'title' => 'Données privées',
						'class' => 'form-section flat',
						'template' => <<<EOT
<table class="panel">
<tr><td class="label">{\$street.label:}</td><td colspan="3">{\$street}</td></tr>
<tr><td>&nbsp;</td><td colspan="3">{\$street_complement}</td></tr>
<tr><td class="label">{\$city.label:}</td><td colspan="3">{\$city}</td></tr>
<tr><td class="label">{\$state.label:}</td><td>{\$state}</td>
<td class="label">{\$postalcode.label:}</td><td>{\$postalcode}</td></tr>
<tr><td class="label">{\$country.label:}</td><td colspan="3">{\$country}</td></tr>
<tr><td class="label">{\$webpage.label:}</td><td colspan="3">{\$webpage}</td></tr>
<tr><td class="label">{\$birthday.label:}</td><td colspan="3">{\$birthday}</td></tr>
</table>
EOT
					),

					'professional' => array
					(
						'title' => 'Données professionnelles',
						'class' => 'form-section flat',
						'template' => <<<EOT
<table class="panel">
<tr><td class="label">{\$position.label:}</td><td>{\$position}</td>
<td class="label">{\$service.label:}</td><td>{\$service}</td></tr>
<tr><td class="label">{\$company.label:}</td><td colspan="3">{\$company}</td></tr>
<tr><td class="label">{\$company_street.label:}</td><td colspan="3">{\$company_street}</td></tr>
<tr><td>&nbsp;</td><td colspan="3">{\$company_street_complement}</td></tr>
<tr><td class="label">{\$company_city.label:}</td><td colspan="3">{\$company_city}</td></tr>
<tr><td class="label">{\$company_state.label:}</td><td>{\$company_state}</td>
<td class="label">{\$company_postalcode.label:}</td><td>{\$company_postalcode}</td></tr>
<tr><td class="label">{\$company_country.label:}</td><td colspan="3">{\$company_country}</td></tr>
<tr><td class="label">{\$company_webpage.label:}</td><td colspan="3">{\$company_webpage}</td></tr>
</table>
EOT
					),

					'misc' => array
					(
						'title' => 'Informations complémentaires',
						'class' => 'form-section flat',
						'template' => <<<EOT
<table class="panel">
<tr><td class="label">{\$misc1.label:}</td><td>{\$misc1}</td></tr>
<tr><td class="label">{\$misc2.label:}</td><td>{\$misc2}</td></tr>
<tr><td class="label">{\$misc3.label:}</td><td>{\$misc3}</td></tr>
<tr><td class="label">{\$misc4.label:}</td><td>{\$misc4}</td></tr>
<tr><td class="label" style="vertical-align: top">{\$notes.label:}</td><td>{\$notes}</td></tr>
</table>
EOT
					),

					'attached' => array
					(
						'title' => 'Pièces attachées',
						'class' => 'form-section flat'
					)
				),

				Element::T_CHILDREN => array
				(
					'salutation' => new Element
					(
						'select', array
						(
							Form::T_LABEL => '.Salutation',
							Element::T_REQUIRED => true,
							Element::T_GROUP => 'contact',
							Element::T_WEIGHT => -10,
							Element::T_OPTIONS => array
							(
								null => '',
								t('salutation.misses'),
								t('salutation.miss'),
								t('salutation.mister')
							)
						)
					),

					#
					# numbers
					#

					'number_work' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Travail',
							Element::T_GROUP => 'numbers'
						)
					),

					'number_home' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Domicile',
							Element::T_GROUP => 'numbers'
						)
					),

					'number_fax' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'FAX',
							Element::T_GROUP => 'numbers'
						)
					),

					'number_pager' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Pager',
							Element::T_GROUP => 'numbers'
						)
					),

					'number_mobile' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Mobile',
							Element::T_GROUP => 'numbers'
						)
					),

					#
					# private
					#

					'street' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Rue',
							Element::T_GROUP => 'private'
						)
					),

					'street_complement' => new Element
					(
						Element::E_TEXT, array
						(
							Element::T_GROUP => 'private'
						)
					),

					'city' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Ville/Localité',
							Element::T_GROUP => 'private'
						)
					),

					'state' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'État/Province',
							Element::T_GROUP => 'private'
						)
					),

					'postalcode' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Code postal',
							Element::T_GROUP => 'private'
						)
					),

					'country' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Pays',
							Element::T_GROUP => 'private'
						)
					),

					'webpage' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Page Web',
							Element::T_GROUP => 'private'
						)
					),

					'birthday' => new WdDateElement
					(
						array
						(
							Form::T_LABEL => 'Date de naissance',
							Element::T_GROUP => 'private'
						)
					),

					#
					# professional
					#

					'position' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Poste',
							Element::T_GROUP => 'professional'
						)
					),

					'service' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Service',
							Element::T_GROUP => 'professional'
						)
					),

					'company' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Société',
							Element::T_GROUP => 'professional'
						)
					),

					'company_street' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Rue',
							Element::T_GROUP => 'professional'
						)
					),

					'company_street_complement' => new Element
					(
						Element::E_TEXT, array
						(
							Element::T_GROUP => 'professional'
						)
					),

					'company_city' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Ville/Localité',
							Element::T_GROUP => 'professional'
						)
					),

					'company_state' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'État/Province',
							Element::T_GROUP => 'professional'
						)
					),

					'company_postalcode' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Code postal',
							Element::T_GROUP => 'professional'
						)
					),

					'company_country' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Pays',
							Element::T_GROUP => 'professional'
						)
					),

					'company_webpage' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Page Web',
							Element::T_GROUP => 'professional'
						)
					),

					#
					# miscelaneous informations
					#

					'misc1' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Divers 1',
							Element::T_GROUP => 'misc'
						)
					),

					'misc2' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Divers 2',
							Element::T_GROUP => 'misc'
						)
					),

					'misc3' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Divers 3',
							Element::T_GROUP => 'misc'
						)
					),

					'misc4' => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'Divers 4',
							Element::T_GROUP => 'misc'
						)
					),

					'notes' => new moo_WdEditorElement
					(
						array
						(
							Form::T_LABEL => 'Notes',
							Element::T_GROUP => 'misc'
						)
					),

					#
					# photo
					#

					'photo' => new Element
					(
						Element::E_FILE, array
						(
							Form::T_LABEL => 'Photo',
							Element::T_GROUP => 'attached',
							Element::T_FILE_WITH_LIMIT => 256,
							Element::T_FILE_WITH_REMINDER => true,
						)
					)
				)
			)
		);
	}
}