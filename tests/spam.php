<?php

namespace ICanBoogie;

use ICanBoogie\HTTP\Request;
use Brickrouge\Form;

try
{
	require_once '../startup.php';
}
catch (\Exception $e)
{

}

if (!$core->user->is_guest)
{
	$core->user->logout();
}

$form = new Form();
$form->save();
$form_key = $form->hiddens[Form::STORED_KEY_NAME];

$request = Request::from
(
	array
	(
		'request_parameters' => array
		(
			Form::STORED_KEY_NAME => $form_key,

			'nid' => 58,
			'author' => 'Monique de lacroix',
			'author_email' => 'cryday767@gmail.com',
// 			'author_url' => 'cryday548@gmail.com',
			'notify' => 'no',
			'contents' => <<<EOT
All of the native date methods work La promotion 2009 était arrivée sur un marché de l’emploi frappé
par la crise. La promotion 2010 s’insère elle entre deux crises. Huit mois après la fin de leurs
études, 88% de ces informaticiens ont un emploi, contre 70% un an plus tôt.
EOT
		)
	),

	array
	(
		array
		(
			'REMOTE_ADDR' => '199.15.234.222',
			'REQUEST_URI' => '/api/comments/save',
			'REQUEST_METHOD' => 'POST'
		)
	)
);

$response = $request();

var_dump(Debug::fetch_messages('debug'), Debug::fetch_messages('error'), Debug::$logs);

// var_dump($operation);
// return $operation($this);

var_dump($request);