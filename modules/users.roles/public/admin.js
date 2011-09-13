

window.addEvent('domready', function()
{
	//
	// replace browser's gadgets
	//
	
	var selects = $$('table.resume select');
	
	if (selects)
	{
		selects.each
		(
			function(el)
			{
				new Wd.Elements.Select(el);
			}
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	//
	// setup jobs
	//
	
	var submit_job_func = function()
	{
		var operation = document.createElement('input');
		
		operation.type = 'hidden';
		operation.name = '#operation';
		operation.value = this.value;
		
		form.appendChild(operation);
		
		form.submit();
		
		return false;
	}

	//
	//
	//

	var form = document.forms['roles'];
	
//	console.info('form: %a', form);
	
	var jobs = form.getElementsByTagName('button');
	
	if (jobs.length == 0)
	{
		return;
	}
		
//	console.info('jobs: %a', jobs);
	
	for (var i = 0 ; i < jobs.length ; i++)
	{
		jobs[i].onclick = submit_job_func
		jobs[i].title = jobs[i].innerHTML;
	}
	
	//
	// theme checkboxes
	//
	
	var checkboxes = $$('table.resume input[type="checkbox"]');
	
	checkboxes.each
	(
		function(box)
		{
			new Wd.Elements.Checkbox(box);
		}
	);

	//
	// link checkboxes
	//
	
	var checkboxes = $$('table.resume tr.footer input[type="checkbox"]');

	if (!checkboxes.length)
	{
		return;
	}

	fx = new Fx.Style($(jobs[1]), 'opacity', { duration: 250 });
	fx.set(0);

	var count = 0;

	checkboxes.each
	(
		function(box)
		{
			box.addEvent
			(
			 	'click', function()
				{
					if (this.checked)
					{
						count++;
					}
					else
					{
						count--;
					}
					
					if (count > 0)
					{
						fx.stop();
						fx.start(1);
					}
					else
					{
						fx.stop();
						fx.start(0);
					}
					
		//			console.info('count: %d', count);
				}
			);
		}
	);
});