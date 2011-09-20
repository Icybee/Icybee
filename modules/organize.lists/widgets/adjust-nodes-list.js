BrickRouge.Widget.AdjustNodesList  = new Class
({
	Implements: [ Options, Events ],

	options:
	{
		constructor: 'system.nodes',
		name: null
	},

	initialize: function(el, options)
	{
		this.element = $(el);
		this.element.store('adjust', this);

		this.attachSearch(this.element.getElement('input.search'));

		this.setOptions(options);

		this.list = this.element.getElement('div.list ul');
		this.listHolder = this.list.getElement('li.holder');

		this.setConstructor(this.options.constructor);
		this.attachResults();
		this.attachList();

		this.element.addEvent('click', this.uberClick.bind(this));
	},

	uberClick: function(ev)
	{
		var target = ev.target;

		if (target.getParent('.pager'))
		{
			ev.stop();

			if (target.tagName != 'A')
			{
				target = target.getParent('a');
			}

			if (target)
			{
				var uri = new URI(target.get('href'));

				this.getResults({ page: uri.parsed.fragment, search: this.search.hasClass('empty') ? null : this.search.value });
			}
		}
	},

	attachSearch: function(el)
	{
		var self = this;
		var lastSearched = null;

		this.search = $(el);

		//
		// prevent form submission
		//

		this.search.addEvent
		(
			'keypress', function(ev)
			{
				if (ev.key == 'enter')
				{
					ev.stop();
				}
			}
		);

		//
		// search as you type
		//

		this.search.addEvent
		(
			'keyup', function(ev)
			{
				if (ev.key == 'esc')
				{
					this.value = '';
				}

				if (lastSearched === this.value)
				{
					return;
				}

				lastSearched = this.value;

				self.getResults({ search: this.value });
			}
		);
	},

	setConstructor: function(constructor)
	{
		this.get_results_operation = null;

		if (!constructor)
		{
			constructor = 'system.nodes';
		}

		this.constructor = constructor;
	},

	getResults: function(options)
	{
		if (this.get_results_operation)
		{
			this.get_results_operation.cancel();
		}
		else
		{
			this.get_results_operation = new Request.Element
			({
				url: '/api/' + this.constructor + '/blocks/adjustResults',
				onSuccess: function(el)
				{
					el.replaces(this.element.getElement('div.results'));

					this.attachResults();
					this.fireEvent('change', {});
				}
				.bind(this)
			});
		}

		this.get_results_operation.get(options);
	},

	attachResults: function()
	{
		var results = this.element.getElement('div.results');

		results.addEvent
		(
			'click', function(ev)
			{
				var target = ev.target;

				if (target.get('tag') == 'a')
				{
					/* uberclick */
				}
				else
				{
					if (target.match('button.add'))
					{
						target = target.getParent('li');
					}
					else if (target.get('tag') != 'li')
					{
						return;
					}

					ev.stop();

					this.add(target);
				}
			}
			.bind(this)
		);

		results.getElements('li').each
		(
			function(el)
			{
				var add = new Element
				(
					'button',
					{
						'class': 'add',
						type: 'button',
						html: '+'
					}
				);

				add.inject(el);
			}
		);
	},

	attachList: function()
	{
		var i = 0;

		this.list.getElements('li.sortable').each
		(
			function(el)
			{
				i++;
				this.attachListEntry(el);
			},

			this
		);

		//this.listHolder.setStyle(i ? 'none' : '');

		/*
		 * the span.handle must be present in the entry, otherwise the entry is used as handle.
		 */

		this.sortable = new Sortables
		(
			this.list,
			{
				clone: true,
				constrain: true,
				opacity: 0.2,
				handle: 'span.handle',

				onStart: function(el, clone)
				{
					clone.setStyle('z-index', 10000);
				},

				onComplete: function()
				{
					this.fireEvent('change', {});
				}
				.bind(this)
			}
		);

		this.list.addEvent
		(
			'click', function(ev)
			{
				if (!ev.target.match('button.remove'))
				{
					return;
				}

				ev.stop();

				this.remove(ev.target.getParent('li'));
			}
			.bind(this)
		);
	},

	attachListEntry: function(el)
	{
		/*
		var handle = new Element
		(
			'span',
			{
				'class': 'handle',
				html: '↕'
			}
		);
		*/

		var remove = new Element
		(
			'button',
			{
				'class': 'remove',
				type: 'button',
				html: '-'
			}
		);

		//handle.inject(el, 'top');

		remove.inject(el);

		/*
		var title = el.getElement('span.title');
		var titleOriginal = title.get('text');

		title.setStyle('cursor', 'normal');
		title.title = 'Click to rename the entry ↕';

		var rename = new Element
		(
			'input',
			{
				type: 'text',
				name: 'labels[]'
			}
		);

		rename.hide();
		title.hide();

		rename.inject(title, 'before');

		title.addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				//console.log('inside: ', title);

				rename.value = title.get('text');

				title.hide();
				rename.show();

				this.sortable.detach();

				rename.addEvent
				(
					'blur', function()
					{
						//console.log('blur: %a', this);

						title.set('text', rename.value ? rename.value : titleOriginal);

						rename.hide();
						title.show();

						this.sortable.attach();
					}
					.bind(this)
				);

				rename.focus();
			}
			.bind(this)
		);
		*/

		if (this.options.name)
		{
			var input = el.getElement('input.nid');

			input.name = this.options.name + '[]';
		}
	},

	add: function(nid)
	{
		if (typeOf(nid) == 'element')
		{
			nid = nid.getElement('input.nid').value;
		}

		var self = this;

		var op = new Request.API
		(
			{
				url: 'widgets/adjust-nodes-list/add/' + nid,
				onSuccess: function(response)
				{
					if (!response.rc)
					{
						return;
					}

					var el = new Element
					(
						'li',
						{
							'class': 'sortable',
							'html': response.rc
						}
					);

					this.attachListEntry(el);

					el.inject(this.list);

					this.sortable.addItems(el);

					//this.listHolder.setStyle('display', 'none');
					this.fireEvent('change', {});
				}
				.bind(this)
			}
		);

		op.get();

		op.get({ nid: nid });
	},

	remove: function(el)
	{
		this.sortable.removeItems(el).destroy();

		if (this.list.childNodes.length == 1)
		{
			//this.listHolder.setStyle('display', '');
		}

		this.fireEvent('change', {});
	}
});