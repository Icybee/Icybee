<?php

// http://www.ranks.nl/resources/stopwords.html

function wd_strip_stopwords($str, $stopwords=null)
{
	$stopwords = 'alors au aucuns aussi autre avant avec avoir à bon car ce cela ces ceux chaque
ci comme comment dans de des dedans dehors depuis deux devrait doit donc dos droite du début elle
elles en encore essai est et eu fait faites fois font force haut hors ici il ils je juste la le
les leur là ma maintenant mais mes mine moins mon mot même ni nommés notre nous nouveaux ou où
par parce parole pas personnes peut peu pièce plupart pour pourquoi quand que quel quelle quelles
quels qui sa sans ses seulement si sien son sont sous soyez sujet sur ta tandis tellement tels
tes ton tous tout trop très tu valeur voie voient vont votre vous vu ça étaient état étions été
être';

	$stopwords = explode(' ', preg_replace('#\s+#', ' ', $stopwords));

	$patterns = array();

	foreach ($stopwords as $word)
	{
		$patterns[] = '# ' . preg_quote($word) . ' #i';
	}

	return preg_replace($patterns, ' ', $str);
}

function wd_slugize($str, $stopwords=null)
{
	$str = wd_strip_stopwords($str);

	return trim(substr(wd_normalize($str), 0, 80), '-');
}