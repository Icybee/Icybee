<?php

use ICanBoogie\Object;

require dirname(__DIR__) . '/wdcore/wdcore.php';

$core = new WdCore();

class Increment extends Object
{
	/**
	 * Parce que la propriété est définie comme _private_, elle n'est ni accessible depuis une
	 * sous-classe, ni depuis l'extérieur, notre _setter_ sera donc appelé à chaque modification de
	 * la valeur.
	 *
	 * @var int Valeur de l'incrément.
	 */
	private $increment=0;

	/**
	 * Modifie la valeur de la propriété `increment` en ajoutant la valeur donnée à la valeur
	 * courante de la propriété.
	 *
	 * @param int $value
	 */
	protected function __set_increment($value)
	{
		$this->increment += $value;
	}

	/**
	 * Retourne la valeur de la propriété `increment`.
	 */
	protected function __get_increment()
	{
		return $this->increment;
	}
}

$i = new Increment();

$i->increment = 1;
$i->increment = 4;

$rc = $i->increment;

echo "<p>The value of the `increment` property should be 5, the current value is:</p>";

echo "<pre>$rc</pre>";

echo "<p>Calling the `non_existing_method` which does not exists yet:</p>";

try
{
	$i->non_existing_method();
}
catch (Exception $e)
{
	echo "<pre>The following exception was raised: " . $e->getMessage() . '</pre>';
}

echo "<p>Adding the `non_existing_method` to the `Test` class, and invoking the method:</p>";

Object::add_method
(
	'non_existing_method', array
	(
		create_function('', 'return "I\'m the method that was non existent !";'),

		'instanceof' => 'Test'
	)
);

$rc = $i->non_existing_method();

echo "<pre>$rc</pre>";

#
# getters and setters mixins
#

echo "<p>Calling the `non_existing_property` which does not exists yet:</p>";

try
{
	$rc = $i->non_existing_property;
}
catch (Exception $e)
{
	echo "<pre>The following exception was raised: " . $e->getMessage() . '</pre>';
}

echo "<p>Adding the `non_existing_property` getter to the `Test` class, and getting the property:</p>";

Object::add_method
(
	'__get_non_existing_property', array
	(
		create_function('', 'return "I\'m the value of the property that was non existent !";'),

		'instanceof' => 'Test'
	)
);

$rc = $i->non_existing_property;

echo "<pre>$rc</pre>";