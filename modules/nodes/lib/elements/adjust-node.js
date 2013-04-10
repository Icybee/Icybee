
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
		this.element = document.id(el)
		this.setOptions(options)
		this.selected = this.element.getElement('.records li.selected a')
		this.attachSearch()

		this.element.addEvent('click:relay(.records a)', function(ev, el) {

			ev.stop()

			if (this.selected)
			{
				this.selected.getParent('li').removeClass('selected')
			}

			el.getParent('li').addClass('selected')

			this.selected = el
			this.fireEvent('select', { target: el, event: ev })
			this.fireEvent('change', { target: el, widget: this, event: ev })

		}.bind(this))

		this.element.addEvent('click:relay(.pagination a)', function(ev, el) {

			var page = el.get('href').split('#')[1]

			this.fetchResults
			({
				page: page,
				search: (this.search && !this.search.hasClass('placeholder')) ? this.search.value : null
			})

		}.bind(this))
	},

	attachSearch: function()
	{
		var search = this.search = this.element.getElement('input.search')
		, searchLast = null

		search.onsubmit = function() { return false }

		search.addEvent('keyup', function(ev) {

			if (ev.key == 'esc')
			{
				ev.target.value = ''
			}

			value = ev.target.value

			if (value != searchLast)
			{
				this.fetchResults({ search: value })
			}

			searchLast = value

		}.bind(this))
	},

	fetchResults: function(params)
	{
		if (!this.fetchResultsOperation)
		{
			this.fetchResultsOperation = new Request.Element({

				url: 'widgets/' + this.options.adjust + '/results',

				onSuccess: function(el, response)
				{
					el.replaces(this.element.getElement('.results'))

					if (!this.selected)
					{
						this.selected = this.element.getElement('.results li.selected a')
					}

					Brickrouge.updateDocument(el)

					this.fireEvent('results', { target: this, response: response })
				}
				.bind(this)
			})
		}

		if (this.selected && !params.selected)
		{
			params.selected = this.selected.get('data-nid')
		}

		params.constructor = this.options.constructor

		this.fetchResultsOperation.get(params)
	},

	setSelected: function(selected)
	{
		this.fetchResults({ selected: selected })
	}
})