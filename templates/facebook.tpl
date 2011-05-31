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
	cursor: hand;
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
</style>
<script type="text/javascript"> 
//<![CDATA[
function TabMenu(name) {
	this.name = name;
	this.show = function(x) {
		var base = document.getElementById(this.name);

		var li = document.getElementById(this.name + '-menu').getElementsByTagName('li');
		for(var i=0; i<li.length; i++) {
			li[i].className = li[i].id == 'menu-' + x ? 'activeTabMenu' : '';
		}
		
		li = base.getElementsByTagName('div');
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
	<div class="tabMenu" id="updates-menu">
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
