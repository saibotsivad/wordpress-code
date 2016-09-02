<div class="colmask blogstyle">
	<div class="colmid">
		<div class="colleft">
			<div class="col1">
				<!-- Column 1 start -->
				<h2>Divs versus Classes</h2>
				<p>There are two main ways to reference a div or any other HTML element from your style-sheet, you can use an id (id="idname") or a class (class="classname") so which one is best? Well the answer is it depends on what you are trying to do.</p>
				<p>An id must be unique, this means you cannot have two elements on the one page with the same id. So an id is useful for something that never repeats on a page like a header or footer. Classes don't have to be unique so they can be used as many times as necessary. A good example of a repeatable element would be a link style, you can have as many links on a page as you like and they can all look the same by giving them the same class name.</p>
				<!-- Column 1 end -->
			</div>
			<div class="col2">
				<!-- Column 2 start -->
				<h2>Use classes for stackable columns</h2>
				<p>Because the columns in this stackable design can be repeated any number of times we cannot use ids because they would be duplicated. Only classes allow us to repeat the columns as many times as we like. So please keep that in mind if you are modifying this design. Of course if your modified layout does not have repeated columns then you can change some of the classes back to ids.</p>
				<!-- Column 2 end -->
			</div>
			<div class="col3">
				<!-- Column 3 start -->
				<h2>Equal height columns</h2>
				<p>It doesn't matter which column has the longest content in each column stack, the background colour of all columns will stretch down to meet the next stack. This feature was traditionally only available with table based layouts but now with a little CSS trickery we can do exactly the same with divs. Say goodbye to annoying short columns!</p>
				<!-- Column 3 end -->
			</div>
		</div>
	</div>
</div>