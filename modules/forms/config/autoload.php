<?php

namespace ICanBoogie\Modules\Forms;

return array
(
	__NAMESPACE__ . '\FormEditor' => $path . 'lib/editors/form/editor.php',
	__NAMESPACE__ . '\FormEditorElement' => $path . 'lib/editors/form/element.php',
	__NAMESPACE__ . '\SentEvent' => $path . 'hooks.php',

	'WdFormSelectorElement' => $path . 'elements/form-selector.php', // TODO-20120918: use namespace

	'Brickrouge\Form\Contact' => $path . 'models/contact.php',
	'press_WdForm' => $path . 'models/contact-press.php',  // TODO-20120918: use namespace
	'Brickrouge\quick_contact_WdForm' => $path . 'models/contact-quick.php',

	'BrickRouge\EmailComposer' => $path . 'lib/elements/email-composer.php'
);