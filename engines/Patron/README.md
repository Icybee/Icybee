WdPatron
========

WdPatron is a template engine for PHP. It facilitates a mangeable way to seperate application logic
and content from its presentation. Templates are usually written in HTML and include keywords that
will be replaced as the template is parsed and special markups that control the logic or fetch
data.

A typical exemple :

	<wdp:articles limit="10">
		<wdp:foreach>
			<h1>#{@title!}</h1>
			
			#{this}
			
			<wdp:if test="@comments">
				<h2>User comments</h2>
			
				<wdp:foreach in="@comments">
					<h3>Comment ##{self.position} by #{@author!}</h3>
					
					#{this}
				</wdp:foreach>
			</wdp:if>
		</wdp:foreach>
		
		<wdp:pager range="self.range" />
	</wdp:articles>
	
	
Features
--------

* The markup set is easily extensible.

* Variable and function scope with the 'this' and 'self' special variables.

* Easy to translate using the #{t:String to translate} notation.