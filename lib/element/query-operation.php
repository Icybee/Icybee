<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use Brickrouge\Actions;
use Brickrouge\Button;
use Brickrouge\Element;

/**
 * An element to confirm an operation in bulk and display its process.
 */
class QueryOperationElement extends Element
{
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('query-operation.css');
		$document->js->add('query-operation.js');
	}

	protected $options;

	public function __construct(array $options, array $attributes=[])
	{
		$this->options = $options;
		$count = count($options['params']['keys']);

		parent::__construct('div', [

			Element::IS => 'QueryOperation',

			Element::CHILDREN => [

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

				, 'errors' => '<div class="alert alert-error undissmisable"></div>',

				'confirm' => new Element('div', [

					Element::CHILDREN => [

						'message' => new Element('p', [

							Element::INNER_HTML => $options['message']

						]),

						'actions' => new Actions([

							'cancel' => new Button($options['confirm'][0], [

								'data-action' => 'cancel'

							]),

							'start' => new Button($options['confirm'][1], [

								'data-action' => 'start',
								'class' => 'btn-warning'

							])

						])

					],

					'class' => 'screen screen--confirm'

				]),

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

			],

			'data-keys' => implode('|', $options['params']['keys']),
			'data-state' => 'confirm',
			'data-progress-pattern' => ":percent% complete",
			'class' => 'widget-query-operation'

		]);
	}
}