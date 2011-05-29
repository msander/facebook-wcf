<style type="text/css">
.border {
	border: 1px solid #afafaf;
}
.hidden {
	display: none;
}
.tabMenu {
	margin-top: 5px;
	clear: both;
	width: 100%;
}

.tabMenu:after {
	content: "";
	clear: both;
	display: block;
	height: 0;
}

.tabMenu ul {
	margin: 0; 
	padding: 0 0 0 10px;  
}

.tabMenu li {
	list-style: none;
	border-width: 1px 1px 0 1px;
	margin-right: 3px;
	float: left;
}

.tabMenu li a {
	text-decoration: none;
	white-space: nowrap;
	border-width: 1px;
	border-style: solid;
	padding: 2px 5px 0 5px;
	display: block;
	position: relative;
	z-index: 10;
}

.tabMenu li.activeTabMenu a {
	border-bottom: none;
	padding-bottom: 3px;
	position: relative;
	z-index: 20;
}

.tabMenu li a, .tabMenu li.activeTabMenu a {
	min-height: 24px;
}

.tabMenuContent {
	clear: both;
}

.tabMenuContent > div {
	padding: 15px 25px;
}

.tabMenuContent .subHeadline {
	margin-bottom: 2px;
}

.tabMenuContent h3.containerContent {
	margin-top: 0;
	margin-bottom: 0;
}

.tabMenuContent fieldset.noJavaScript {
	background: transparent;
	border: 0;
	margin: 0;
	padding-top: 13px;
}

.tabMenuContent legend.noJavaScript {
	display: none;
}

.tabMenuContentContainer {
	clear: both;
}

.tabMenuContent .message {
	margin-bottom: 7px;
}

.tabMenuContent .messageInner .smallButtons:after {
	clear: none;
}

.tabMenuContent .message .messageHeading {
	margin-top: 0 !important;
}

/* ### -- -- -- -- -- Specials -- -- -- -- -- ### */

.tabNavigation {
	float: right;
}
</style>
<script type="text/javascript"> 
//<![CDATA[
function TabMenu(name) {
	this.name = name;
	this.show = function(x) {
		var base = document.getElementById(this.name);

		var lis = base.getElementsByTagName('tabMenu')[0].getElementsByTagName('li');
		for(var i=0; i<li.length; i++) {
			li[i].className = li[i].id == 'menu-' + x ? 'activeTabMenu' : '';
		}
		
		lis = base.getElementsByTagName('tabMenu')[0].getElementsByTagName('li');
		for(var i=0; i<li.length; i++) {
			if(!li[i].className || !li[i].className.match(/tabMenuContent/)) {
				continue;
			}

			li[i].className = li[i].id == 'content-' + x ? 'border tabMenuContent' : 'border tabMenuContent hidden';
		}
	};
};
var tabMenu = new TabMenu('updates');
//]]>
</script>

<div id="updates">
	<div class="tabMenu">
		<ul>
			<li id="menu-post"><a onclick="tabMenu.show('post');"><span>Posts</span></a></li>
			<li id="menu-blog"><a onclick="tabMenu.show('blog');"><span>Blogs</span></a></li>
			<li id="menu-wiki"><a onclick="tabMenu.show('wiki');"><span>Wikis</span></a></li>
			<li id="menu-job"><a onclick="tabMenu.show('job');"><span>Jobs</span></a></li>
		</ul>
	</div>
	<div class="border tabMenuContent hidden" id="content-post">
		<div class="container-1">
			<h3 class="subHeadline">Posts</h3>
			xxx
		</div>
	</div>
	<div class="border tabMenuContent hidden" id="content-blog">
		<div class="container-1">
			<h3 class="subHeadline">Blogs</h3>
			xxx
		</div>
	</div>
	<div class="border tabMenuContent hidden" id="content-wiki">
		<div class="container-1">
			<h3 class="subHeadline">Wikis</h3>
			xxx
		</div>
	</div>
	<div class="border tabMenuContent hidden" id="content-job">
		<div class="container-1">
			<h3 class="subHeadline">Jobs</h3>
			xxx
		</div>
	</div>
</div>


<script type="text/javascript"> 
//<![CDATA[
tabMenu.show('post') 
//]]>
</script>
