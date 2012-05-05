
Brickrouge.Widget.AdjustNode = new Class
({
	Implements: [ Options, Events ],

	options:
	{
		adjust: 'adjust-node',
		constructor: 'system.nodes'
	},

	initialize: function(el, options)
	{
		this.element = $(el);
		this.setOptions(options);
		this.element.addEvent('click', this.uberOnClick.bind(this));
		this.selected = this.element.getElement('.records li.selected');
		this.attachSearch();
	},

	uberOnClick: function(ev)
	{
		var target = ev.target;

		if (target.getParent('.pagination'))
		{
			ev.stop();

			if (target.tagName != 'A')
			{
				target = target.getParent('a');
			}

			var page = target.get('href').split('#')[1];

			this.fetchResults
			({
				page: page,
				search: (this.search && !this.search.hasClass('placeholder')) ? this.search.value : null
			});
		}
		else if (target.getParent('.records'))
		{
			if (target.tagName != 'LI')
			{
				el = target.getParent('li');
			}

			if (el)
			{
				if (el.hasClass('empty'))
				{
					return;
				}

				ev.stop();

				this.element.getElements('.records li').removeClass('selected');
				el.addClass('selected');
				this.selected = el;
				this.fireEvent('select', { target: el, event: ev });
				this.fireEvent('change', { target: el, widget: this, event: ev });
			}
		}
	},

	attachSearch: function()
	{
		var search = this.search = this.element.getElement('input.search');
		var searchLast = null;

		search.onsubmit = function() { return false; };

		search.addEvent
		(
			'keyup', function(ev)
			{
				if (ev.key == 'esc')
				{
					ev.target.value = '';
				}

				value = ev.target.value;

				if (value != searchLast)
				{
					this.fetchResults({ search: value });
				}

				searchLast = value;
			}
			.bind(this)
		);
	},

	fetchResults: function(params)
	{
		if (!this.fetchResultsOperation)
		{
			this.fetchResultsOperation = new Request.Element
			({
				url: '/api/widgets/' + this.options.adjust + '/results',
				onSuccess: function(el, response)
				{
					el.replaces(this.element.getElement('.results'));

					if (!this.selected)
					{
						this.selected = this.element.getElement('.results li.selected');
					}

					document.fireEvent('elementsready', { target: el });

					this.fireEvent('results', { target: this, response: response });
				}
				.bind(this)
			});
		}

		if (this.selected && !params.selected)
		{
			params.selected = this.selected.get('data-nid');
		}

		params.constructor = this.options.constructor;

		this.fetchResultsOperation.get(params);
	},

	setSelected: function(selected)
	{
		this.fetchResults({selected: selected });
	}
});
