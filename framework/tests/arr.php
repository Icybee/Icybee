<?php

require_once '../../includes/startup.php';

$model = $core->models['nodes'];

if (0)
{
	$core->db->create_table
	(
		'madonna', array
		(
			'fields' => array
			(
				'id1' => 'primary',
				'id2' => 'primary',
				'title' => 'varchar'
			)
		)
	);
}

function _2_5_filtres_dynamiques() { }

/*

Filtres dynamiques
==================

Pour chaque colonne définie par le modèle, l'API ActiveRecord fournie une méthode de recherche.
Parce le modèle _Noeuds_ définit le champ `slug`, on peut utiliser la méthode de recherche
`filter_by_slug` :

@php
$model->filter_by_slug('creer-nuage-mots-cle');
php@

Si l'on a besoin de connaitre les articles en ligne de l'utilisateur ayant pour idenfitiant `3` on
peut tout simplement enchainer les filtres avec le séparateur `_and_` :

@php
$model->filter_by_is_online_and_uid(true, 3);
php@

Ce qui est un équivalent la méthode de recherche `where` :

@php
$model->where(array('is_online' => true, 'uid' => 3));
php@

*/

if (0)
{
	$a = $model->filter_by_is_online_and_uid(false, 1)->all;

	var_dump($a);
}

function _2_6_portée() {}

/*

Portées
=======

Les portées peuvent être considérées comme des _macros_ de recherche, des options toutes prêtes,
rapides à utiliser. Chaque modèle peut définir ses propres portées ou surcharger
celles du modèle dont il hérite. Par exemple, voici la définition de la portée _visible_ par le
modèle _Nœuds_ :

@php
	use ICanBoogie\ActiveRecord\Query;

	...

	protected function scope_visible(Query $query)
	{
		global $core;

		return $query->where('is_online = 1 AND (siteid = 0 OR siteid = ?) AND (language = "" OR language = ?)', $core->site->siteid, $core->site->language);
	}

	...
php@

On peut alors très simplement obtenir la liste des enregistrement disponible pour le site :

@php
$model->visible;
php@

On peut bien sûr combiner les portées ainsi que les autre méthodes de recherche :

@php
$model->filter_by_uid(1)->visible->where('YEAR(created) = 2011');
php@


*/

if (0)
{
	$a = $model->visible->limit(2)->all;

	var_dump($a);

	$a = $model->filter_by_uid(1)->visible->limit(2)->all;

	var_dump($a);
}







/*

Grouper les données
===================

La clause SQL `GROUP BY` peut être spécifiée en utilisant la méthode `group()`. Par exemple, si
l'on souhaite obtenir le premier enregistrement crée par jour, on utilisera le code suivant :

@php
$model->group('date(created)')->order('created');
php@


Appliquer un filtre au groupe
-----------------------------

La clause `HAVING` est utilisée pour spécifier les conditions de la clause `GROUP BY`. Par exemple,
si l'on souhaite obtenir le premier enregistrement crée par jour pour le mois passé, on utilisera
le code suivant :

@php
$model->group('date(created)')->having('created > ?', date('Y-m-d', strtotime('-1 month')))->order('created')
php@

*/

if (0)
{
	/*
	$a = $model->group('date(created)')->order('created')->all;

	var_dump($a);
	*/

	$a = $model->group('date(created)')->having(array('is_online' => false))->order('created');
	$a = $model->group('date(created)')->having(array('is_online' => false))->order('created')->all;
	$a = $model->group('date(created)')->having('created > ?', date('Y-m-d', strtotime('-1 month')))->order('created')->all;

	var_dump($a);
}

/*

Sélectionner des champs spécifiques
===================================

Par défaut, tous les champs sont sélectionnés (`SELECT *`) et les enregistrements sont retournés
sous la forme d'objets dont la classe dépend du modèle de données. Il est cependant possible de ne
sélectionner qu'un sous ensemble de champs grâce à la méthode `select`. Dans ce cas, chaque ligne
de résultat est renvoyée sous la forme d'un tableau associatif. Par exemple si l'on souhaite
obtenir l'identifiant d'un nœud, sa date de création et son titre :

@php
$model->select('nid, created, title');
php@

Parce que les champs spécifiés sont utilisés tels-quels pour construire la requête SQL, il est
tout à fait possible d'utiliser les fonctions SQL :

@php
$model->select('nid, created, CONCAT_WS(":", title, language)');
php@

*/

if (0)
{
	$a = $model->select('nid, title')->where('constructor = "articles"')->pairs;

	var_dump($a);
}

/*

Récupérer les données
=====================

Il existe de nombreuses façons de récupérer les lignes du jeu d'enregistrement.



Par itération
-------------

Parce que l'objet `ICanBoogie\ActiveRecord\Query` est traversable, l'itération est la façon la plus simple
de récupérer les lignes du jeu d'enregistrements :

@php
foreach ($model->where('is_online = 1') as $node)
{
	...
}
php@



Récupérer tous les enregistrements
----------------------------------

Le jeu de résultat peut être renvoyé sous la forme d'un tableau associatif grâce à la propriété
`all` :

@php
$array = $model->where('is_online = 1')->all;
php@



Récupérer seulement le premier enregistrement
---------------------------------------------

Il arrive souvent que l'on ne souhaite récupérer que le premier objet d'une requête, dans ce cas
on utilisera la propriété `one` :

@php
$record = $model->order('created DESC')->one;
php@



Récupérer des paires de valeurs
-------------------------------

Lorsque l'on ne sélectionne que deux colonnes, il est possible de récupérer un résultat sous la
forme clé/valeur grâce à la propriété `pairs` :

@php
$model->select('nid, title')->pairs;
php@

Dans ce cas la première colonne est utilisée comme clé et la seconde comme valeur, pour un
résultat similaire à celui-ci :

@raw
array
  34 => string 'Créer un nuage de mots-clé' (length=28)
  57 => string 'Générer à la volée des miniatures avec mise en cache' (length=56)
  307 => string 'Mes premiers pas de développeur sous Ubuntu 10.04 (Lucid Lynx)' (length=63)
  ...
raw@






Choisir le type des enregistrements
===================================

En général on laissera le framework décider du type des données, mais il est possible de décider à
sa place grâce à la méthode `mode` :

@php
$model->select('nid, title')->mode(PDO::FETCH_NUM);
php@

La méthode `mode` prend les même arguments que la méthode
[PDOStatement::setFetchMode](http://php.net/manual/fr/pdostatement.setfetchmode.php).

Il est également possible de définir le type des données depuis les méthodes `all` et `one` :

@php
$array = $model->where('is_online = 1')->all(PDO::FETCH_ASSOC);
$record = $model->where('is_online = 1')->one(PDO::FETCH_ASSOC);
php@


*/
#
# scope
#

if (0)
{
	$a = $model->where('1 = 1')->online(false)->all;

	var_dump($a);
}

function _3_joindre_des_tables() {}

/*

Joindre des tables
==================

L'API Active Record fournie une méthode de recherche qui permet de spécifier la clause `JOIN` de la
requête SQL. Il est possible de spécifier un fragment brut ou d'utiliser les relations qu'il existe
entre les modèles.



Utiliser un fragment brut
-------------------------

On peut spécifier un fragment brut, il sera inclu tel quel dans la requête finale :

@php
$model->joins('INNER JOIN contents USING(nid)');
php@


Utiliser une référence à un modèle
----------------------------------

On peut profiter des relations définies entre les modèles et laisser faire le framework :

@php
$model->joins(':contents');
php@

La requête produira le même effet que la précédente, sans que nous ayons à nous soucier des
conditions de la jointure. On notera les deux points ":" utilisés pour identifier l'utilisation
d'un nom de modèle plutôt qu'un fragment brut.

*/

function _4_exsitence_objets() {}

/*

Tester l'existence d'objets
===========================

Pour simplement vérifier l'existence d'objets on utilise la méthode `exists`. Comme la méthode
`find`, cette méthode interroge la base de données à la recherche d'objets, mais au lieu de
retourner un objet ou une collection d'objets elle retourne TRUE ou FALSE selon la présence de
l'objet, ou des objets, dans la base.

@php
$model->exists(1);
php@

La méthode `exists()` accepte également les jeux d'identifiants, mais au lieu de retourner TRUE ou
FALSE, elle retourne un tableau associatif où chaque clé est la valeur de la clé primaire de
l'objet, et la valeur de cette clé est TRUE ou FALSE selon que l'objet existe ou pas.

@php
$model->exists(1,2,999)
# ou
$model->exists(array(1,2,999));
php@

Ce qui peut donner le résultat suivant :

@raw
array
  1 => boolean true
  2 => boolean true
  999 => boolean false
raw@

Il est également possible d'utiliser la méthode `exists` sans argument sur un modèle ou une
requête :

@php
$model->where('author = ?', 'Madonna')->exists;
php@

La requête ci-dessus retourne TRUE si au moins un auteur a pour nom "Madonna", FALSE dans le cas
contraire.

@php
$model->exists;
php@

La requête ci-dessus retourne FALSE si la table est vide, et TRUE dans le cas contraire.

*/

if (0)
{
	$rc = $model->exists(1);

	var_dump($rc);

	$rc = $model->exists(1,2,999);

	var_dump($rc);

	$rc = $model->exists(array(1,2,999));

	var_dump($rc);

	$rc = $model->where(array('is_online' => true))->exists;

	var_dump($rc);

	echo "(system.nodes).exist?<br />";

	$rc = $model->exists;

	var_dump($rc);

	echo "(content.agenda).exist?<br />";

	$rc = $core->models['contents.agenda']->exists;

	var_dump($rc);
}

function _5_1_fonctions_de_calcul() {}

if (1)
{
/*

Fonctions de calcul
===================

Cette section utilise la méthode `count` comme exemple, mais les options décrites s'appliquent à
toutes les sous-sections, même si la méthode `count` possède quelques particularités.

Les méthodes de calcul peuvent s'appliquer directement sur le modèle :

@php
$model->count;
php@

Où sur une recherche :

@php
$model->where('firstname = ?', 'Ryan').count;
php@

Bien sûr, toutes les méthodes de recherche peuvent être utilisées :

@php
$model->filter_by_firstname('Ryan')->joins(':content')->where('YEAR(date) = 2011')->count;
php@



Compter
-------

La méthode `count` permet de connaitre le nombre d'enregistrements. Si l'on veut être plus
spécifique, on peut connaitre le nombre d'enregistrement selon la valeur d'un champ :

@php
$model->count('is_online');
php@

Renvera un tableau avec pour clé la valeur de la colonne, et pour valeur le nombre
d'enregistrements ayant la même valeur pour la colonne :

@raw
array
  0 => string '35' (length=2)
  1 => string '145' (length=3)
raw@

Ici, il y a 35 enregistrements en ligne et 145 hors ligne.

> Attention, ceci est une particularité de la méthode de calcul `count`. Aucune autre méthode de
calcul ne fonctionne de cette manière.



Moyenne, Minimum, Maximum et somme
----------------------------------

Les méthodes de calcul `average`, `minimum`, `maximum` et `sum` permettent respectivement,
pour une colonne, de calculer la moyenne de ses valeurs, la valeur minimum, la valeur maximum et
la somme de ses valeurs.

Contrairement à la méthode de calcul `count`, ces méthodes requièrent le nom de la colonne sur
laquelle appliquer le calcul :

@php
$model->average('comments_count');
$model->minimum('created');
$model->maximum('created');
$model->sum('comments_count');
php@

*/

	$a = $model->count('is_online');

	var_dump($a);

	$a = $model->average('uid');

	var_dump($a);

	$a = $model->minimum('created');

	var_dump($a);

	$a = $model->maximum('created');

	var_dump($a);

	$a = $model->sum('nid');

	var_dump($a);
}


/*
$i = 3;

while ($i--)
{
	echo "madonna: $i<br />";
}

$core->models['nodes']->find(12);
$core->models['nodes']->find(31);

$a = $core->models['nodes']
	->find(12, 31, 348);

var_dump($a);

return;

$a = $core->models['nodes']
	->exists(12, 31, 348);

var_dump($a);

$a = $core->models['nodes']
	->exists(12, 31, 999);

var_dump($a);

$a = $core->models['nodes']
	->exists(9990, 9991, 9992);

var_dump($a);

$a = $core->models['nodes']
	->where(array('slug' => 'premiers-pas-developpeur-ubuntu'))
	->exists;

var_dump($a);

$a = $core->models['nodes']
	->where(array('slug' => 'does-not-exists'))
	->exists;

var_dump($a);


$a = $core->models['nodes']
	->where(array('nid' => array(12, 31, 348)))
	->joins(':users')
	->all();

var_dump($a);


$a = $core->models['taxonomy.vocabulary']
	->where('? IN (scope)', 'articles')
	->where(array('vocabularyslug' => 'category', 'siteid' => 1))
	->one();

var_dump($a);


$rc = $core->models['users']
	->where('username = ? OR email = ?', array('olivier', 'lovepub'))
	->where(array('password' => md5('lovepub')))->one();

var_dump($rc);



$model = $core->models['contents'];

$rc = $model->where(array('constructor' => 'articles'))->select('slug')->rc;

var_dump($rc);

*/


/*

Un récapitulatif sous forme d'exemples
======================================

Obtenir des objets :

@php
$record = $model[10];
$records = $model->find(10, 15, 19);
$records = $model->find(array(10, 15, 19));
php@

Conditions :

@php
$model->where('is_online = ?', true);
$model->where(array('is_online' => true, 'is_home_excluded' => false));
$model->where('siteid = 0 OR siteid = ?', 1)->where('language = '' OR language = ?', "fr");

$model->where(array('order_count' => array(1, 2, 3));
$model->where(array('!order_count' => array(1, 2, 3)); # contraire

# Filtres dynamiques

$model->filter_by_nid(1);
$model->filter_by_siteid_and_language(1, 'fr');

# Portées

$model->visible;
php@

Grouper, ordonner :

@php
$model->group('date(created)')->order('created');
$model->group('date(created)')->having('created > ?', date('Y-m-d', strtotime('-1 month')))->order('created');
php@

Limites et décalage :

@php
$model->where('is_online = 1')->limit(10); // retourne les 10 premiers enregistrements
$model->where('is_online = 1')->limit(5, 10); // retourne les enregistrements 6 à 16

$model->where('is_online = 1')->offset(5); // retourne les enregistrements de 6 jusqu'au dernier
$model->where('is_online = 1')->offset(5)->limit(10);
php@

Sélection de champs :

@php
$model->select('nid, created, title');
$model->select('nid, created, CONCAT_WS(":", title, language)');
php@

Jointures :

@php
$model->joins('INNER JOIN contents USING(nid)');
$model->joins(':contents');
php@

Récupérer les données :

@php
$model->all;
$model->order('created DESC')->all(PDO::FETCH_ASSOC);
$model->order('created DESC')->mode(PDO::FETCH_ASSOC)->all;
$model->order('created DESC')->one;
$model->select('nid, title')->pairs;
php@

Tester l'existence d'objets :

@php
$model->exists;
$model->exists(1, 2, 3);
$model->exists(array(1, 2, 3));
$model->where('author = ?', 'madonna')->exists;
php@

Fonctions de calcul :

@php
$model->count;
$model->count('is_online');
$model->average('score');
$model->minimum('age');
$model->maximum('age');
$model->sum('comments_count');
php@

Bref, de quoi faire.

 */