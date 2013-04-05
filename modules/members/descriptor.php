<?php

namespace Icybee\Modules\Members;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'users',
	Module::T_EXTENDS => 'users',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_EXTENDS => 'users',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'salutation_code' => array('integer', 'tiny'),

					#
					# numbers
					#

					'number_work' => array('varchar', 30),
					'number_home' => array('varchar', 30),
					'number_fax' => array('varchar', 30),
					'number_pager' => array('varchar', 30),
					'number_mobile' => array('varchar', 30),

					#
					# private
					#

					'street' => 'varchar',
					'street_complement' => 'varchar',
					'city' => array('varchar', 80),
					'state' => array('varchar', 80),
					'postalcode' => array('varchar', 10),
					'country' => array('varchar', 80),
					'webpage' => 'varchar',

					'birthday' => 'date',

					#
					# professional
					#

					'position' => array('varchar', 80),
					'service' => array('varchar', 80),
					'company' => array('varchar', 80),
					'company_street' => 'varchar',
					'company_street_complement' => 'varchar',
					'company_city' => array('varchar', 80),
					'company_state' => array('varchar', 80),
					'company_postalcode' => array('varchar', 10),
					'company_country' => array('varchar', 80),
					'company_webpage' => 'varchar',

					#
					# misc
					#

					'misc1' => 'varchar',
					'misc2' => 'varchar',
					'misc3' => 'varchar',
					'misc4' => 'varchar',
					'notes' => 'text',

					#
					# photo
					#

					'photo' => 'varchar'
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_TITLE => 'Membres',
	Module::T_VERSION => '1.0'
);