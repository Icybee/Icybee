<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\ManageBlock;

use ICanBoogie\I18n;
use ICanBoogie\Module;

/**
 * A translator that leverages module inheritance to translate strings.
 */
class Translator
{
	protected $module;

	public function __construct(Module $module)
	{
		$this->module = $module;
	}

	public function __invoke($native, array $args = [], array $options = [])
	{
		$module = $this->module;

		$user_scope = isset($options['scope']) ? $options['scope'] : null;
		$user_scope_dotted = $user_scope ? "{$user_scope}." : '';
		$user_default = isset($options['default']) ? $options['default'] : null;

		$options['scope'] = "{$this->module->flat_id}.manage" . ($user_scope ? ".$user_scope" : '');

		$options['default'] = function(\ICanBoogie\I18n\Translator $translator, $native) use($module, $user_scope_dotted, $user_default) {

			while ($module = $module->parent)
			{
				$try = "$module->flat_id.manage.{$user_scope_dotted}$native";
				$translated = $translator[$try];

				if ($translated)
				{
					return $translated;
				}
			}

			return $translator["manage.{$user_scope_dotted}$native"]
			?: $translator["{$user_scope_dotted}$native"]
			?: ($user_default instanceof \Closure ? $user_default($translator, $native) : $translator[$user_default])
			?: $user_default;
		};

		return self::app()->translate($native, $args, $options);
	}

	/**
	 * @return \ICanBoogie\Core|\Icybee\Binding\CoreBindings
	 */
	static private function app()
	{
		return \ICanBoogie\app();
	}
}
