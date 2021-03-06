/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Shortens a string to given length from a given position.
 *
 * Example:
 *
 * var str = "Raccourcir une chaine de caractères à des endroits divers et variés.";
 *
 * console.log(str.shorten(32, 0)); // remove characters from the beginning of the string
 * console.log(str.shorten(32, .25));
 * console.log(str.shorten(32, .5)); // remove characters from the middle of the string
 * console.log(str.shorten(32, .75));
 * console.log(str.shorten(32, 1)); // remove characters from the end of the string
 *
 * @param {int} [length]
 * @param {number} [position]
 *
 * @return string A string shortened.
 */
String.prototype.shorten = function(length, position) {

	const ELLIPSIS = '…'

	if (length === undefined)
	{
		length = 32
	}

	if (position === undefined)
	{
		position = .75
	}

	const l = this.length

	if (l <= length)
	{
		return this
	}

	length--
	position = Math.round(position * length)

	if (position == 0)
	{
		return ELLIPSIS + this.substring(l - length)
	}

	if (position == length)
	{
		return this.substring(0, length) + ELLIPSIS
	}

	return this.substring(0, position) + ELLIPSIS + this.substring(l - (length - position))
}
