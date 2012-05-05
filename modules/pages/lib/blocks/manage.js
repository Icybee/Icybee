var WdDroppableTableRow = new Class
({
	initialize: function(el)
	{
		this.element = $(el);
	},

	getParent: function()
	{
		var level = this.getLevel();

		for (parent = this.element.getPrevious() ; parent ; parent = parent.getPrevious())
		{
			var parent_level = this.getLevel(parent);

			if (parent_level < level)
			{
				return parent;
			}
		}
	},

	getDirectChildren: function()
	{
		var children = [];
		var level = this.getLevel();

		for (var child = this.element.getNext() ; child ; child = child.getNext())
		{
			var child_level = this.getLevel(child);

			//console.info('getDirectChildren:> level: %d, child_level: %d', level, child_level);

			if (child_level <= level)
			{
				break;
			}

			if (child_level > level + 1)
			{
				continue;
			}

			children.push(new WdDroppableTableRow(child));
		}

		//console.log('get children for %a: %a', this.element, children);

		return children;
	},

	inject: function(target, how)
	{
		//console.info('inject: %a %s', target, how);
	},

	getLevel: function(el)
	{
		if (!el)
		{
			el = this.element;
		}

		return el.getElements('div.indentation').length;
	},

	setLevel: function(level)
	{
		/* update our children */

		/*var weight = 0;*/

		this.getDirectChildren().each
		(
			function(child)
			{
				child.setLevel(level + 1);
				/*child.setWeight(weight++);*/
			}
		);

		/* update our level */

		var indentations = this.element.getElements('div.indentation');
		var diff = level - indentations.length;

		if (diff < 0)
		{
			while (diff++)
			{
				indentations.pop().destroy();
			}
		}
		else if (diff > 0)
		{
			var target = this.element.getElement('div.handle');

			while (diff--)
			{
				var indentation = new Element('div', { 'class': 'indentation', 'html': '&nbsp;' });

				indentation.inject(target, 'before');
			}
		}
	},

	/*

	getWeightElement: function(el)
	{
		if (!el)
		{
			el = this.element;
		}

		return el.getElement('input[name^=weights]');
	},

	getWeight: function(el)
	{
		return this.getWeightElement(el).value;
	},

	setWeight: function(weight, el)
	{
		this.getWeightElement(el).value = weight;
	},

	updateWeights: function(weight)
	{
		var weight = 0;

		this.getDirectChildren().each
		(
			function(child)
			{
				child.setWeight(weight++);
			}
		);
	},

	*/

	getParentIdElement: function(el)
	{
		if (!el)
		{
			el = this.element;
		}

		return el.getElement('input[name^=parents]');
	},

	getParentId: function(el)
	{
		return this.getParentIdElement(el).value;
	},

	setParent: function(parent)
	{
		var nid = parent ? parent.id.substr(4) : 0;

		//console.log('set parent: %d', nid);

		this.getParentIdElement().value = nid;
	},

	/**
	 *
	 * check parent adoption while moving items, updating parents as needed :
	 *
	 * * the element after the target is a child of the target, the dragged elements
	 * become children of the target element
	 *
	 * * the element after the target element has a higher level, the dragged elements
	 * can become children of their previous sibling
	 *
	 */

	updateParent: function()
	{
		var level = this.getLevel();

		for (var parent = this.element.getPrevious() ; parent ; parent = parent.getPrevious())
		{
			var parent_level = this.getLevel(parent);

			//console.log('level: %d, previous level: %d (%a)', level, previous_level, previous);

			if (parent_level >= level)
			{
				continue;
			}

			//console.log('found parent: %a', previous);

			break;
		}

		this.setParent(parent);
	}
});


var WdDraggableTableRow = new Class
({
	Extends: WdDroppableTableRow,

	initialize: function(el)
	{
		this.parent(el);

		this.handle = this.element.getElement('div.handle');

		this.handle.addEvents
		({
			mousedown: function(ev)
			{
				ev.stop();

				this.dragStart();
			}
			.bind(this),

			click: function(ev)
			{
				ev.stop();
			}
		});
	},

	dragStart: function()
	{
		this.mouseup_callback = this.dragFinish.bind(this);
		this.mousemove_callback = this.dragQuery.bind(this);

		//console.info('drag start with: %a', this.element);

		document.body.addEvent('mouseup', this.mouseup_callback);
		document.body.addEvent('mousemove', this.mousemove_callback);

		/* search children */

		var level = this.getLevel();

		//console.log('level: %d', level);

		this.dragged = [ new WdDroppableTableRow(this.element) ];

		for (child = this.element.getNext() ; child ; child = child.getNext())
		{
			var child_level = child.getElements('div.indentation').length;

			if (child_level <= level)
			{
				break;
			}

			this.dragged.push(new WdDroppableTableRow(child));
		}

		this.dragged.each
		(
			function(el)
			{
				el.element.addClass('dragged');
			}
		);

		//console.log('%d elements dragged', this.dragged.length);
	},

	dragQuery: function(ev)
	{
		/*
		 * handle level
		 */

		var box = this.handle.getCoordinates();

		if (ev.page.x < box.left)
		{
			this.changeLevel(-1);
		}
		else if (ev.page.x > box.left + box.width)
		{
			this.changeLevel(1);
		}

		/*
		 * handle weight
		 */

		var coords = this.element.getCoordinates();
		var y = coords.top;
		var h = coords.height;

		//console.log('dragQuery: el: %a, %a (%a), coords: %a', this.element, ev, ev.page, coords);

		if (ev.page.y < y)
		{
			this.changeWeight(-1);
		}
		else if (ev.page.y > y + h)
		{
			this.changeWeight(1);
		}
	},

	dragFinish: function(ev)
	{
		//console.log('dragFinish: %a', ev);

		/* remove event listeners */

		document.body.removeEvent('mouseup', this.mouseup_callback);
		document.body.removeEvent('mousemove', this.mousemove_callback);

		/* remove the 'dragged' class upon children */

		if (!this.dragged)
		{
			return;
		}

		this.dragged.each
		(
			function(el)
			{
				el.element.removeClass('dragged');
			}
		);

		/* update weights */
		/*

		var parent_id = this.getParentId();

		var weight = 0;

		this.element.getParent().getElements('tr.draggable').each
		(
			function(el)
			{
				var el_parent_id = this.getParentId(el);

				if (el_parent_id != parent_id)
				{
					return;
				}

				this.setWeight(weight++, el);
			},

			this
		);
		*/

		this.dragged = null;
	},

	changeWeight: function(slide)
	{
		switch (slide)
		{
			case -1:
			{
				var target = this.element.getPrevious();

				if (!target)
				{
					return;
				}

				var level = this.getLevel();
				var parent = target.getPrevious();
				var parent_level = parent ? this.getLevel(parent) : 0;

				if (level - 1 > parent_level)
				{
					this.setLevel(parent_level + 1);
				}

				target.inject(this.dragged.getLast().element, 'after');
			}
			break;

			case 1:
			{
				var target = this.dragged.getLast().element.getNext();

				if (!target || !target.hasClass('draggable'))
				{
					return;
				}

				var level = this.getLevel();
				var target_level = this.getLevel(target);

				//console.log('after: %a with level %d, my level is %d', target, target_level, level);

				/*
				if (level == target_level || level - 1 > target_level)
				{
					this.setLevel(target_level + 1);
				}
				*/

				/* on déplace l'élement en premier dans un arbre */

				var next = target.getNext();

				if (next && level < this.getLevel(next))
				{
					this.setLevel(target_level + 1);
				}

				target.inject(this.element, 'before');
			}
			break;

			default:
			{
				return;
			}
			break;
		}

		/*
		//
		// update elements on the same level FIXME: all this sucks
		//

		var n = 0;
		var level = this.getLevel();

		this.element.getParent().getElements('tr.draggable').each
		(
			function(el)
			{
				if (this.getLevel(el) != level)
				{
					n = 0;

					return;
				}

				//console.info('mylevel: %d, el: %a, level: %d, weight: %d', level, el, this.getLevel(el), this.getWeight(el));

				this.setWeight(n++, el);
			},

			this
		);
		*/

		this.modified();
	},

	/**
	 *
	 * Change the level for the selected elements :
	 *
	 * * the element has no parent, the level is not changed
	 *
	 * * the element can be moved deeper if the previous element has the same or deeper level,
	 * its maximum depth can only be one deeper.
	 *
	 * * the element can be moved shallower if the next element has a shallower level
	 *
	 */

	changeLevel: function(slide)
	{
		var level = this.getLevel();
		var parent = null;

		switch (slide)
		{
			case -1:
			{
				if (level == 0)
				{
					return;
				}

				var next = this.dragged.getLast().element.getNext();

				if (next)
				{
					var next_level = this.getLevel(next);

					if (next_level >= level)
					{
						return;
					}
				}
			}
			break;

			case 1:
			{
				var previous = this.element.getPrevious();

				if (!previous)
				{
					return;
				}

				var previous_level = this.getLevel(previous);

				if (previous_level < level)
				{
					return;
				}
			}
			break;

			default:
			{
				throw 'slide value "' + slide + '" is not implemented';
			}
			break;
		}

		this.setLevel(level + slide);

		this.modified();
	},

	modified: function()
	{
		this.updateParent();

		if (this.element.getElement('sup.modified'))
		{
			return;
		}

		this.element.addClass('modified');

		var target = this.element.getElement('a.edit');

		var mark = new Element('sup', { 'class': 'modified', 'html': '*' });

		mark.inject(target, 'after');

		//
		// show update button
		//

		if (update_button.getStyle('display') == 'none')
		{
			update_button.set('opacity', 0);
			update_button.setStyle('display', 'inline');
			update_button.get('tween').start(1);
		}
	}
});

var update_button;

manager.addEvent
(
	'ready', function()
	{
		var handles = manager.element.getElements('div.handle');

		if (!handles.length)
		{
			//
			// if there are no handle that because rows are not sortable
			//

			return
		}

		manager.element.getElements('tr.draggable').each
		(
			function(el)
			{
				new WdDraggableTableRow(el);
			}
		);

		var table = $(document.body).getElement('table.manage');

		update_button = $(document.body).getElement('button[name=update]');

		if (update_button)
		{
			update_button.set('tween', { property: 'opacity', duration: 'short' });

			update_button.addEvent
			(
				'click', function(ev)
				{
					ev.stop();

					this.disabled = true;

					var weights = {};

					table.getElements('input[name^=weights]').each
					(
						function(el)
						{
							var nid = el.name.match(/(\d+)/)[0];

							weights[nid] = el.value;
						}
					);

					var parents = {};

					table.getElements('input[name^=parents]').each
					(
						function(el)
						{
							var nid = el.name.match(/(\d+)/)[0];

							parents[nid] = el.value;
						}
					);

					var op = new Request.API
					({
						url: 'pages/update_tree',
						onSuccess: function(response)
						{
							table.getElements('tr.modified').each
							(
								function(el)
								{
									el.removeClass('modified');
									el.getElement('sup.modified').destroy();

									update_button.get('tween').start(0).chain
									(
										function()
										{
											update_button.setStyle('display', '');
											update_button.disabled = false;
										}
									);
								}
							);
						}
						.bind(this)
					});

					op.post({ weights: weights, parents: parents });
				}
			);
		}
	}
);


manager.addEvent
(
	'ready', function()
	{
		/*
		manager.element.addEvent
		(
			'click', function(ev)
			{
				var target = ev.target;

				console.log('target: %a', target);

				if (!target.match('label.navigation'))
				{
					return;
				}

				target = target.getElement('input');

				console.log('target: ', target);

				new Request.API
				({
					url: manager.destination + '/' + target.get('value') + '/' + (target.checked ? 'navigation_exclude' : 'navigation_include'),

					onRequest: function()
					{
						target.disabled = true;
					},

					onFailure: function()
					{
						target.checked = !target.checked;

						target.fireEvent('change', {});
					},

					onSuccess: function(response)
					{
						target.disabled = false;

						target.fireEvent('change', {});
					}
				})
				.post();
			}
		);
		*/

		manager.element.getElements('input.navigation').each
		(
			function(el)
			{
				el.addEvent
				(
					'click', function(ev)
					{
						var target = ev.target;

						new Request.API
						({
							url: manager.destination + '/' + el.get('value') + '/' + (target.checked ? 'navigation_exclude' : 'navigation_include'),

							onRequest: function()
							{
								target.disabled = true;
							},

							onFailure: function()
							{
								target.checked = !target.checked;

								target.fireEvent('change', {});
							},

							onSuccess: function(response)
							{
								target.disabled = false;

								target.fireEvent('change', {});
							}
						})
						.post();
					}
				);
			}
		);

		manager.element.getElements('tr.volatile-highlight').each
		(
			function (el)
			{
				el.set('tween', { duration: 2000, transition: 'sine:out' });
				el.highlight('#FFE');

				( function() { el.setStyle('background-color', ''); el.removeClass('volatile-highlight'); } ).delay(2100);
			}
		);
	}
);