<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use Brickrouge\Actions;
use Brickrouge\Button;
use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\Form;

/**
 * An element to confirm an operation in bulk and display its process.
 */
class QueryOperationElement extends Element
{
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add(__DIR__ . '/QueryOperationElement.css');
		$document->js->add(__DIR__ . '/QueryOperationElement.js');
	}

	protected $options;

	public function __construct(array $options, array $attributes = [])
	{
		$this->options = $options;

		parent::__construct('div', [

			Element::IS => 'QueryOperation',
			Element::CHILDREN => $this->create_children($options, $attributes),

			'data-keys' => implode('|', $options['params']['keys']),
			'data-state' => 'confirm',
			'data-progress-pattern' => ":percent% complete",

			'class' => 'widget-query-operation'

		]);
	}

	/**
	 * Creates element's children.
	 *
	 * @param array $options
	 * @param array $attributes
	 *
	 * @return Element[]
	 */
	protected function create_children(array $options, array $attributes)
	{
		$count = count($options['params']['keys']);

		return [

			'title' => new Element('h3', [

				Element::INNER_HTML => $options['title']

			]),

			'progress' => <<<EOT
<div class="progress" brickrouge-is="Progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="$count">
<div class="progress-bar">
<span class="progress-bar-label"></span>
</div>
</div>
EOT
			,

			'errors' => '<div class="alert alert-error undismissable"></div>',

			'confirm' => $this->create_confirm_form($options, $attributes),

			'processing' => new Element('div', [

				Element::CHILDREN => [

					'actions' => new Actions([

						'cancel' => new Button("Cancel", [

							'data-action' => 'cancel',
							'class' => 'btn-danger'

						])

					])

				],

				'class' => 'screen screen--processing'

			]),

			'complete' => new Element('div', [

				Element::CHILDREN => [

					'actions' => new Actions([

						'complete' => new Button("Ok", [

							'data-action' => 'success',
							'class' => 'btn-success'

						])

					])

				],

				'class' => 'screen screen--success'

			])

		];
	}

	protected function create_confirm_form(array $options, array $attributes)
	{
		return new Form([

			Form::ACTIONS => [

				'cancel' => new Button($options['confirm'][0], [

					'data-action' => 'cancel'

				]),

				'start' => new Button($options['confirm'][1], [

					'data-action' => 'start',
					'class' => 'btn-warning'

				])

			],

			Element::CHILDREN => $this->create_confirm_children($options, $attributes),

			'class' => 'screen screen--confirm'

		]);
	}

	/**
	 * Creates _confirm_ children.
	 *
	 * @param array $options
	 * @param array $attributes
	 *
	 * @return Element[]
	 */
	protected function create_confirm_children(array $options, array $attributes)
	{
		return [

			'message' => new Element('p', [

				Element::INNER_HTML => $options['message']

			]),

			'params' => null

		];
	}
}
