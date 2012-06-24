<?php

namespace Testing;

use ICanBoogie;
use ICanBoogie\ActiveRecord;

$path = __DIR__;

#
# Emplacement de la classe WdCore, qui nous permet d'instancier l'objet `core`, cœur du framework.
# Il ne servira pas à grand chose, mais au moins le framework sera basiquement configuré, notamment
# l'autoloader.
#

require_once dirname($path) . '/wdcore/wdcore.php';

$core = new WdCore();

#
# Il s'agit de la classe utilisée pour instancier les enregistrements que nous récupérerons plus
# tard depuis notre modèle.
#

class Test extends ActiveRecord
{
	protected function get_reversed_number()
	{
		return strrev((string) $this->number);
	}
}

#
# On établie une connexion à la base de donnée "models-and-records.sq3" en utilisant le driver
# SQLite. On utilise SQLite parce que la base ne demandera aucun effort à mettre en place. Il faut
# tout de même vérifier les permissions d'écriture.
#

$connection = new WdDatabase("sqlite:$path/models-and-records.sq3");

#
# Définition et instanciation de notre modèle.
#

$model = new WdModel
(
	array
	(
		Model::T_ACTIVERECORD_CLASS => 'Test',
		Model::T_CONNECTION => $connection,
		Model::T_NAME => 'node',
		Model::T_SCHEMA => array
		(
			'fields' => array
			(
				'id' => 'serial',
				'title' => array('varchar', 80),
				'number' => array('integer', 'unsigned' => true)
			)
		)
	)
);

#
# Nous installons le modèle, s'il n'est pas déjà installé (création de la table correspondante).
#

if (!$model->is_installed())
{
	$model->install();
}

#
# On le peuple de quelques enregistrements
#

$i = 10;

while ($i--)
{
	$number = uniqid();

	$model->save
	(
		array
		(
			'title' => "title-$i-" . md5($number),
			'number' => $number
		)
	);
}

#
# On récupère tous les enregistrements du modèle et pour chacun d'entre eux on affiche la valeur
# de la clé primaire et un nombre renversé, utilisant un getter magique.
#

foreach ($model->all as $record)
{
	echo "record #{$record->id}, reversed number: {$record->reversed_number}<br />";
}