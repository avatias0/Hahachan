<?php
 /*
  * HaChan configuration
  * Remember to change the password!
  */
 
 class config
 {
 	public static $name = 'l0dschan';	// Your site name
	public static $user = 'root';			// Your username to acess the admin panel
	public static $pass = 'L15LUjEdbdfl8LV9';			// Your password to access the admin panel
	public static $banner_rotate = true;	// Allow banner rotation?
	public static $url = 'http://chan.esay.es';	// Your site url, without the final backslash '/'
	public static $copyright = 'All trademarks on this page are owned by their respective parties - Images uploaded are the responsibility of the poster - Comments are owned by the poster';
	
	// Here are all your forums, for more info on this check the README
 	public static $boards = array(
		'/l0ds/'=>'l0ds',
    '/b/'=>'random',
	);
	
	// Add all the styles that you want... If you want to define a style as default, just name it default.css
	public static $styles = array(
		'Red'=>'/styles/red.css',
		'Melon'=>'/styles/melon.css',
	);
	
	// All possible banners. If rotation is allowed, it will display a random one
	public static $banners = array(
		'rotate/20.png',
    'rotate/21.png',
    'rotate/22.png',
    'rotate/23.png',
    'rotate/24.png',
    'rotate/25.png',
    'rotate/26.png',
    'rotate/27.png',
    'rotate/28.png',
    'rotate/29.png',
    'rotate/30.png',
    'rotate/31.png',
	);
 }
 
 /*
  * Do not edit this if you dont know what you are doing!
  */
session_start();
function clean()
{
	foreach($_POST as $key => $val)
	{
		$_POST[$key] = stripslashes(strip_tags(htmlspecialchars($val, ENT_QUOTES)));
		$$key = stripslashes(strip_tags(htmlspecialchars($val, ENT_QUOTES)));
	}
	foreach($_GET as $key => $val)
	{
		$_GET[$key] = stripslashes(strip_tags(htmlspecialchars($val, ENT_QUOTES)));
		$$key = stripslashes(strip_tags(htmlspecialchars($val, ENT_QUOTES)));
	}
}
clean();

function error($str)
{
	echo '<div id="forumListContainer">
		<h1>Error!</h1>
		<div style="padding-left:5px;">'.$str.'<br />Click <a href="javascript:history.back();">here</a> to go back.</div><div style="clear:both ">&nbsp;</div>
	</div>';
}

function crop($filename, $destination, $th_width, $th_height, $forcefill, $quality)
{   
	list($width, $height) = getimagesize($filename);
	$file_class = substr($filename, -3, 3);
	if($file_class == 'jpg' || substr($filename, -4, 4) == 'jpeg')
		$source = imagecreatefromjpeg($filename);
	elseif($file_class == 'gif')
		$source = imagecreatefromgif($filename);
	elseif($file_class == 'png')
		$source = imagecreatefrompng($filename);
	else
		die('No valid file type. ('.$file_class.')');
	
	if($width > $th_width || $height > $th_height)
	{
		$a = $th_width/$th_height;
		$b = $width/$height;

		if(($a > $b)^$forcefill)
		{
			$src_rect_width  = $a * $height;
			$src_rect_height = $height;
			if(!$forcefill)
			{
				$src_rect_width = $width;
				$th_width = $th_height/$height*$width;
			}
		}
		else
		{
			$src_rect_height = $width/$a;
			$src_rect_width  = $width;
			if(!$forcefill)
			{
				$src_rect_height = $height;
				$th_height = $th_width/$width*$height;
			}
		}
		$src_rect_xoffset = ($width - $src_rect_width)/2*intval($forcefill);
		$src_rect_yoffset = ($height - $src_rect_height)/2*intval($forcefill);
		$thumb  = imagecreatetruecolor($th_width, $th_height);
		imagecopyresized($thumb, $source, 0, 0, $src_rect_xoffset, $src_rect_yoffset, $th_width, $th_height, $src_rect_width, $src_rect_height);
		imagejpeg($thumb,$destination, $quality);
		imagedestroy($thumb);
	}
}

function isAdmin()
{
	if($_SESSION['user'] == config::$user && $_SESSION['pass'] == config::$pass) return true;
	else return false;
}

function getFolderSize($d=".")
{
	// © kasskooye and patricia benedetto
	$h = @opendir($d);
	if($h==0) return 0;
	while ($f=readdir($h))
	{
		if ($f!= "..")
		{
			$sf+=filesize($nd=$d."/".$f);
			if($f!="."&&is_dir($nd))
				$sf+=GetFolderSize($nd);
        }
    }
    closedir($h);
    return $sf;
}

function hacode($content)
{
	if(isset($_GET['reply']))
		$content = preg_replace("#&gt;&gt;(\d+)#i","<span class='quoteLink'><a href='#\\1'>&gt;&gt;\\1</a></span>",$content);
	$content = preg_replace("#&gt;&gt;&gt;/(.+?)/(\d+)#i","<span class='quote'><a href='".config::$url."/\\1/?reply=\\2'>&gt;&gt;&gt;/\\1/\\2</a></span> ",$content);
	$content = preg_replace("#&gt;(.+?)&lt;#i","<span class='quote'>&gt; \\1 &lt;</span> ",$content);
	$content = nl2br($content);
	
	// "Custom"
	$content = str_replace('7', 'over 9000', $content);
	$content = str_replace('penis', '<span style="background-color: #'.randomColor().';"><b>
	<span style="color:#'.randomColor().';">P</span>
	<span style="color:#'.randomColor().';">E</span>
	<span style="color:#'.randomColor().';">N</span>
	<span style="color:#'.randomColor().';">I</span>
	<span style="color:#'.randomColor().';">S</span>
	</b></span>', $content);
	$content = str_replace('vagina', '<span style="background-color: #'.randomColor().';"><b>
	<span style="color:#'.randomColor().';">V</span>
	<span style="color:#'.randomColor().';">A</span>
	<span style="color:#'.randomColor().';">G</span>
	<span style="color:#'.randomColor().';">I</span>
	<span style="color:#'.randomColor().';">N</span>
	<span style="color:#'.randomColor().';">A</span>
	</b></span>', $content);
	
	return $content;
}

function randomColor()
{
	$c = null;
    while(strlen($c)<6)
		$c .= sprintf("%02X", rand(0, 255));
    return $c;
}

function tripcode($nick)
{
	$code = explode('#', $nick);
	if(empty($code[1]))
		return $nick;
	else
		return $code[0].'#'.substr(sha1(md5($code[1])),0,6);
}
?>