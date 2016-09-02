					<h2>Percentage dimensions of the right menu layout</h2>
					<img src="http://matthewjamestaylor.com/blog/perfect-2-column-right-menu-dimensions.gif" width="350" height="370" alt="Two column right menu layout dimensions" />
					<p>All the dimensions are in percentage widths so the layout adjusts to any screen resolution. Vertical dimensions are not set so they stretch to the height of the content.</p>
					<h3>Maximum column content widths</h3>
					<p>To prevent wide content (like long URLs) from destroying the layout (long content can make the page scroll horizontally) the column content divs are set to overflow:hidden. This chops off any content that is wider than the div. Because of this, it's important to know the maximum widths allowable at common screen resolutions. For example, if you choose 800 x 600 pixels as your minimum compatible resolution what is the widest image that can be safely added to each column before it gets chopped off? Here are the figures:</p>
					<dl>
						<dt><strong>800 x 600</strong></dt>
						<dd>Right column: 162 pixels</dd>
						<dd>Main page: 550 pixels</dd>
						<dt><strong>1024 x 768</strong></dt>
						<dd>Right column: 210 pixels</dd>
						<dd>Main page: 709 pixels</dd>
					</dl>
					<h2>The nested div structure</h2>
					<p>I've colour coded each div so it's easy to see:</p>
					<img src="http://matthewjamestaylor.com/blog/perfect-2-column-right-menu-div-structure.gif" width="350" height="369" alt="Two column right menu layout nested div structure" />
					<p>The header, colmask and footer divs are 100% wide and stacked vertically one after the other. Colleft is inside colmask. The two column content divs (col1 &amp; col2) are inside colleft. Notice that the main content column (col1) comes before the side column.</p>
