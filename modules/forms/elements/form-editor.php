<?php

use Brickrouge\Element;

class form_WdEditorElement extends WdEditorElement
{
	protected $selector;

	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			'div', array
			(
				Element::CHILDREN => array
				(
					$this->selector = new \WdFormSelectorElement
					(
						'select', array
						(
							/*
							Element::LABEL => 'Formulaire',
							Element::LABEL_POSITION => 'before',
							*/
							Element::DESCRIPTION => 'Sélectionner le formulaire à afficher sur la page'
						)
					)
				),

//				'class' => 'combo'
			)

			+ $tags
		);
	}

	public function offsetSet($offset, $value)
	{
		if ($offset == 'name')
		{
			$this->selector['name'] = $value;
		}
		else if ($offset == 'value')
		{
			$this->selector['value'] = $value;
		}

		parent::offsetSet($offset, $value);
	}

	static public function render($data)
	{
		global $core;

		if (!$data)
		{
			return;
		}

		$form = $core->models['forms'][$data];

		return (string) $form;
	}
}