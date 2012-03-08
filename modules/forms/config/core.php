<?php

namespace ICanBoogie\Modules\Forms;

return array
(
	'autoload' => array
	(
		'WdFormSelectorElement' => $path . 'elements/form-selector.php',
		'form_WdEditorElement' => $path . 'elements/form-editor.php',

		'Brickrouge\Form\Contact' => $path . 'models/contact.php',
		'press_WdForm' => $path . 'models/contact-press.php',
		'Brickrouge\quick_contact_WdForm' => $path . 'models/contact-quick.php',

		'BrickRouge\EmailComposer' => $path . 'lib/elements/email-composer.php',

		__NAMESPACE__ . '\SentEvent' => $path . 'hooks.php'
	)
);