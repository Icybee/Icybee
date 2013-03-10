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

use ICanBoogie\Operation;

class Member extends \Icybee\Modules\Users\User
{
	public $salutation_code;

	public $number_work;
	public $number_home;
	public $number_fax;
	public $number_pager;
	public $number_mobile;

	public $street;
	public $street_complement;
	public $city;
	public $state;
	public $postalcode;
	public $country;
	public $webpage;

	public $birthday;

	public $position;
	public $service;
	public $company;
	public $company_street;
	public $company_street_complement;
	public $company_city;
	public $company_state;
	public $company_postalcode;
	public $company_country;
	public $company_webpage;

	public $misc1;
	public $misc2;
	public $misc3;
	public $misc4;
	public $notes;

	public $photo;

	/**
	 * Defaults `$model` to "members".
	 *
	 * @param string|\ICanBoogie\ActiveRecord\Model $model
	 */
	public function __construct($model='members')
	{
		parent::__construct($model);
	}

	// TODO-20110108: thumbnailer should support the 'thumbnail' method for members as well,
	// or maybe the "images" module should have a special support for users.

	protected function get_thumbnail()
	{
		return $this->thumbnail('primary');
	}

	public function thumbnail($version) // TODO-20120922: use Thumbnail object
	{
		if (!$this->photo)
		{
			return;
		}

		return Operation::encode
		(
			'thumbnailer/get', array
			(
				'src' => $this->photo,
				'version' => $version
			)
		);
	}
}