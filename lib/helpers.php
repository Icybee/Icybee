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

// http://www.ranks.nl/resources/stopwords.html

function strip_stopwords($str, $stopwords=null)
{
	$stopwords = "alors au aucuns aussi autre avant avec avoir à bon car ce cela ces ceux chaque
ci comme comment d' dans de des dedans dehors depuis deux devrait doit donc dos droite du début elle
elles en encore essai est et eu fait faites fois font force haut hors ici il ils j' je juste
l' la le les leur là ma maintenant m' mais mes mine moins mon mot même n' ni nommés notre nous
nouveaux ou où par parce parole pas personnes peut peu pièce plupart pour pourquoi quand que quel
quelle quelles quels qui sa sans ses seulement si sien son sont sous soyez sujet sur t' ta tandis
tellement tels tes ton tous tout trop très tu valeur voie voient vont votre vous vu ça étaient
état étions été être";

	$stopwords = explode(' ', preg_replace('#\s+#', ' ', $stopwords));

	$patterns = array();

	foreach ($stopwords as $word)
	{
		$patterns[] = '# ' . preg_quote($word) . ' #i';
	}

	return preg_replace($patterns, ' ', $str);
}

/**
 * Creates a string suitable for an URL path.
 *
 * To create the _slug_, stop words are removed with the {@link strip_stopwords} then the string
 * is normalized with {@link ICanBoogie\normalize} and limited to 80 characters.
 *
 * @param string $str
 *
 * TODO-20130128: Add a locale_id param so that a localized dictionnary is used to strip stop
 * words.
 *
 * @return string
 */
function slugize($str)
{
	$str = \Icybee\strip_stopwords($str);

	return trim(substr(\ICanBoogie\normalize($str), 0, 80), '-');
}