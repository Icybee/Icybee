/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Changes the `enabled` class of the `.group-toggler` element according to the state of its
 * checkbox child.
 */
document.body.addEvent('click:relay(.group-toggler input[type="checkbox"])', (ev, el) => {

	const parent = el.getParent('.group-toggler')

	parent[el.checked ? 'addClass' : 'removeClass']('enabled')

})
