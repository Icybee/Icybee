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

class WdFormSelectorElement extends Element
{
	public function __toString()
	{
		global $core;

		$site = $core->site;
		$value = (int) $this->get('value');

		$options = $core->models['forms']->select('nid, title')
		->where('nid = ? OR ((siteid = 0 OR siteid = ?) AND (language = "" OR language = ?))', $value, $site->siteid, $site->language)
		->order('title')
		->pairs;

		if (!$options)
		{
			$url = wd_entities($core->site->path . '/admin/forms/new');

			return <<<EOT
<p><a href="$url">Créer un premier formulaire...</a></p>
EOT;
		}

		if ($this->type == 'select')
		{
			$options = array(null => '') + $options;
		}

		$this->set(self::OPTIONS, $options);

		return parent::__toString();
	}
}