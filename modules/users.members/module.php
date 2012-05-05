<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users\Members;

use Brickrouge\Date;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \ICanBoogie\Modules\Users\Module
{
	protected function block_edit(array $properties, $permission)
	{
		return array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Element::GROUPS => array
				(
					'numbers' => array
					(
						'title' => 'Numéros de téléphone',
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
						'title' => 'Pièces attachées'
					)
				),

				Element::CHILDREN => array
				(
					'salutation' => new Element
					(
						'select', array
						(
							Form::LABEL => '.Salutation',
							Element::REQUIRED => true,
							Element::GROUP => 'contact',
							Element::WEIGHT => -10,
							Element::OPTIONS => array
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

					'notes' => new \moo_WdEditorElement
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
							Element::FILE_WITH_LIMIT => 256,
							Element::FILE_WITH_REMINDER => true
						)
					)
				)
			)
		);
	}
}