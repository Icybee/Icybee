<?php

namespace Brickrouge;

class EmailComposer extends Group
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			array_merge_recursive
			(
				array
				(
					self::CHILDREN => array
					(
						'from' => new Text
						(
							array
							(
								Form::LABEL => 'email_from'
							)
						),

						'destination' => new Text
						(
							array
							(
								Form::LABEL => 'email_destination'
							)
						),

						'bcc' => new Text
						(
							array
							(
								Form::LABEL => 'email_bcc'
							)
						),

						'subject' => new Text
						(
							array
							(
								Form::LABEL => 'email_subject'
							)
						),

						'template' => new Element
						(
							'textarea', array
							(
								Form::LABEL => 'email_template'
							)
						)
					)
				),

				$attributes
			)
		);
	}

	public function offsetSet($offset, $value)
	{
		if ($offset == 'name')
		{
			$is_suffix = $value{strlen($value) - 1} == '_';

			foreach ($this->children as $name => $child)
			{
				$child['name'] = $is_suffix ? $value . $name : $value . "[$name]";
			}
		}
		else if ($offset == self::DEFAULT_VALUE && is_array($value))
		{
			foreach ($value as $name => $default_value)
			{
				if (empty($this->children[$name]))
				{
					continue;
				}

				$this->children[$name][self::DEFAULT_VALUE] = $default_value;
			}
		}

		parent::offsetSet($offset, $value);
	}
}
