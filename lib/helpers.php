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

/**
 * Renders CSS class names into a string suitable for the HTML `class` attribute.
 *
 * @param array $names CSS class names.
 * @param string|array $modifiers CSS class names modifiers.
 *
 * @return string
 */
function render_css_class(array $names, $modifiers=null)
{
	$names = array_filter($names);

	if ($modifiers)
	{
		if (is_string($modifiers))
		{
			$modifiers = explode(' ', $modifiers);
		}

		$modifiers = array_map('trim', $modifiers);
		$modifiers = array_filter($modifiers);

		foreach ($modifiers as $k => $modifier)
		{
			if ($modifier{0} == '-')
			{
				unset($names[substr($modifier, 1)]);
				unset($modifiers[$k]);
			}
		}

		if ($modifiers)
		{
			$names = array_intersect_key($names, array_combine($modifiers, $modifiers));
		}
	}

	array_walk($names, function(&$v, $k) {

		if ($v === true) $v = $k;

	});

	return implode(' ', $names);
}

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

function slugize($str, $stopwords=null)
{
	$str = \Icybee\strip_stopwords($str);

	return trim(substr(\ICanBoogie\normalize($str), 0, 80), '-');
}
