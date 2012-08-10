<?php

return array
(
	'template' => array
	(
		'patron_native_WdMarkups::template', array
		(
			'name' => array('required' => true)
		),

		'no-binding' => true
	),

	'call-template' => array
	(
		'patron_native_WdMarkups::call_template', array
		(
			'name' => array('required' => true)
		),

		'no-binding' => true
	),

	'foreach' => array
	(
		array('patron_native_WdMarkups', 'foreach_'), array
		(
			'in' => array('default' => 'this', 'expression' => true),
			'as' => null
		)
	),

	'variable' => array
	(
		array('patron_native_WdMarkups', 'variable'), array
		(
			'name' => array('required' => true),
			'select' => array('expression' => true)
		),

		'no-binding' => true
	),

	'with' => array
	(
		array('patron_native_WdMarkups', 'with'), array
		(
			'select' => array('expression' => true)
		)
	),

	'choose' => array
	(
		array('patron_native_WdMarkups', 'choose'), array
		(

		),

		'no-binding' => true
	),

	'if' => array
	(
		array('patron_native_WdMarkups', 'if_'), array
		(
			'test' => array('expression' => array('silent' => true)),
			'select' => array('expression' => array('silent' => true)),
			'equals' => null
		),

		'no-binding' => true
	)
);