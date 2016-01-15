<?php include_once('config.php'); ?>
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
	$('#removeFrame').click(function(e){
		e.preventDefault();
		$.cookie('frames', 'no');
		top.window.location = 'index.php';
	});
});
</script>
<style>	
	h1 {
		margin: 0px;
		font-size: 20px;
		padding-left: 3px;
		background-color: #999999;
		margin-bottom: 5px;
	}
	
	a {
		display: block;
		text-decoration: none;
		color: #222222;
	}
	
	a:hover {
		background-color: #999999;
	}
</style>
</head>
<body style="padding: 10px; margin: 0px;">
<a target="main" href="<?php echo config::$url ?>/main.php"><h1><?= config::$name ?></h1></a>
<?php
foreach(config::$boards as $shortName=>$board)
{
	echo '<a target="main" href="'.config::$url.$shortName.'index.php">'.$board.'</a>';
}
?>
<br />
<a href="#" id="removeFrame">Remove Frame</a>
</body>
</html>