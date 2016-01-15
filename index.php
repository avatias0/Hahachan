<?php
include('config.php');
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= config::$name ?></title>
<link href="<?= config::$styles['Melon'] ?>" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript" src="jquery.js"></script>
<style>
#menu {
	position: absolute;
	left: 0px;
	top: 0px;
	margin: 0;
	padding: 0;
	border: 0px;
	height: 100%;
	width: 15%;
	background-color: white;
}
#main {
	position: absolute;
	left: 15%;
	top: 0px;
	border: 0px;
	height: 100%;
	width: 85%;
}
</style>
</head>
<body>
<?php if($_COOKIE['frames'] == 'no') header('Location:main.php'); ?>
<iframe id="menu" name="menu" src="menu.php"></iframe>
<iframe id="main" name="main" src="main.php"></iframe>
</body>
</html>
