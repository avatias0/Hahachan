<?php include_once('config.php'); ob_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= config::$name ?></title>
<link href="<?= config::$styles['Melon'] ?>" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript" src="jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="jquery.cookie.js"></script>
<script language="JavaScript" type="text/javascript">
$(document).ready(function(){
	$('#showFrames').click(function(e){
		e.preventDefault();
		$.cookie('frames', 'yes');
		location = 'index.php';
	});
});
</script>
</head>
<body>
<div id="top">
	<div style="float:left; ">[ <?php foreach(config::$boards as $k=>$v) echo '<a href="'.config::$url.$k.'">'.$k.'</a> '; ?> ]</div> 
	<div style="float:right; "><?= $n = ($_COOKIE['frames'] == 'no') ? '[<a href="#" id="showFrames">Show Frames</a>] - ' : ''; ?>[<a href="?main">Home</a>] - [<a href="?FAQ">F.A.Q</a>]</div>
</div>
<div id="content">
  <div align="center"><a href="?main"><img src="../<?= config::$banners[rand(0,count(config::$banners)-1)]; ?>" alt="" border="0" style="border: 1px solid black; " /></a></div>
	<h1><?= config::$name ?></h1>
	<br />
	<?php if(isset($_GET['admin'])){ include('admin.php'); } else if(isset($_GET['FAQ'])) { include('FAQ.html'); } else { ?>
	<div id="forumListContainer">
		<h1>Latest News</h1>
		<div style="padding: 5px; padding-top: 0px;">
			<?php include('news.html'); ?>
		</div>
	</div>
	<?php } ?>
</div>
<div id="footer"><?= config::$copyright ?> - Powered by <a href="http://code.google.com/p/hachan/" target="_blank">HaChan</a></div>
</body>
</html>