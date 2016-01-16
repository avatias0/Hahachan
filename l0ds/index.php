<?php

/*
 * HaChan is a simple ImageBoard powered by PHP and FlatFiles
 * It uses a flat database system called Phlat <http://code.google.com/p/phlat/>
 * Author: Federico Ramirez (a.k.a fedekun)
 * More info can be found on README and <http://code.google.com/p/hachan/>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
include('../config.php');
include('../lib/phlat.class.php');

/*
 * Forum configuration
 * This configuration is separate so you can customize diferent forums
 */
 
 define(SHORT_NAME, '/l0ds/');
 define(BOARD_NAME, 'l0ds of emone');
 define(CUSTOM_TITLE, 'what\'s that spell?');	// Change this to a random title you want...
 define(IMAGES_FOLDER, 'src');		// Any name...
 define(THUMBS_FOLDER, 'thumb');	// Same...
 define(HOME, '../');				// You wont really need to change this, but its the link to the home page
 define(MAX_SIZE, 4);				// Max image size, 1 = 1MB
 define(MAX_W, 5000);
 define(MAX_H, 5000);
 define(TH_W, 150);
 define(TH_H, 200);
 define(IMAGES_PER_PAGE, 10);
 define(MAX_PAGES, 10);
 define(REPLIES_SHOWN, 4);			// Replies shown in the main page
 define(MAX_BUMPS, 20);				// Bumps allowed, it wont bump after that number of replies
 define(MAX_REPLIES, 100);
 define(USE_GZIP, false);			// GZIP compresses HTML so the page loads faster, but it consumes server resources
 define(USE_GZIP_DB, false);		// Compress database so its smaller but takes longer to load
 define(DB_GZ_LVL, 9);				// Compression level
 define(FLOOD_TIME, 20);				// Time in seconds before they can post again
 define(META_DESC, 'no robots');	// Site description for search engines
 define(META_KEYS, 'no robots');				// Site keywords for search engines, keep them short
 $banned_ips = array('123.123.123.123', '123.1.12.21'); 	// Just add banned ips here
 $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');	// Allowed image extensions, you cannot add more, but you can remove
 
 /*
  * End of configuration
  * Dont edit the following code if you dont know what you are doing
  */
 
$posts = new phlat('posts', array('time', 'bumps', 'name', 'email', 'subject', 'comment', 'thumb', 'image', 'pass', 'ip'), USE_GZIP_DB, DB_GZ_LVL);
$replies = new phlat('replies', array('post_id', 'time', 'name', 'email', 'subject', 'comment', 'thumb', 'image', 'pass', 'ip'), USE_GZIP_DB, DB_GZ_LVL);
$reported = new phlat('../reported', array('post_id', 'reply_id', 'reason', 'board'), USE_GZIP_DB, DB_GZ_LVL);
session_start();
if(USE_GZIP) ob_start("ob_gzhandler"); else ob_start();
if(in_array($_SERVER['REMOTE_ADDR'], $banned_ips)) die('Sorry! You have been banned from the board '.BOARD_NAME);
header('Content-Type:text/html; charset=UTF-8');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="<?= META_DESC ?>"/>
<meta name="keywords" content="<?= META_KEYS ?>"/>
<title><?=SHORT_NAME?> - <?=BOARD_NAME?></title>
<link href="<?= $n = (empty($_COOKIE['style']) || !in_array($_COOKIE['style'], config::$styles)) ? config::$styles['Melon'] : $_COOKIE['style']; ?>" rel="stylesheet" type="text/css" />
<script src="../jquery.js" language="JavaScript" type="text/javascript"></script>
<script src="../jquery.cookie.js" language="JavaScript" type="text/javascript"></script>
<script src="../hover.js" language="JavaScript" type="text/javascript"></script>
</head>
<body>
<div id="top">
	<div style="float:left; ">[ <?php foreach(config::$boards as $k=>$v) echo '<a href="'.config::$url.$k.'">'.$k.'</a> '; ?> ]</div> 
	<div style="float:right; ">[<a href="../main.php?main">Home</a>] - [<a href="../main.php?FAQ">F.A.Q</a>]</div>
</div>
<div id="content">
  <div align="center"><a href="?main"><img src="../<?= config::$banners[rand(0,count(config::$banners)-1)]; ?>" alt="" border="0" style="border: 1px solid black; " /></a></div>
	<h1><?=SHORT_NAME?> - <?=BOARD_NAME?></h1>
  <h4><?=CUSTOM_TITLE?></h4><br />
	<hr />
	<?php
	if(isset($_GET['reply']))
		echo '<h1>Reply Mode</h1>';
	?>
	<form action="" method="post" enctype="multipart/form-data">
	<table border="0" align="center">
	  <tr>
	    <td class="submitTable">Name</td>
	    <td><input name="name" type="text" id="name" size="30" /></td>
	  </tr>
	  <tr>
	    <td class="submitTable">E-mail</td>
	    <td><input name="email" value="<?= $_SESSION['lastEmail'] ?>" type="text" id="email" size="30" /></td>
	  </tr>
	  <tr>
	    <td class="submitTable">Subject</td>
	    <td><input name="subject" type="text" id="subject" size="30" /> 
	    <input type="submit" name="<?php if(isset($_GET['r'])) echo 'SubmitReply'; else echo 'Submit'; ?>" value="Submit" /></td>
	  </tr>
	  <tr>
	    <td class="submitTable" valign="top">Comment</td>
	    <td><textarea name="comment" cols="40" rows="10" id="comment"></textarea></td>
	  </tr>
	  <tr>
	    <td class="submitTable">File</td>
	    <td><input name="file" type="file" id="file" size="30" /></td>
	  </tr>
	  <tr>
	    <td class="submitTable">Password</td>
	    <td><input name="pass" type="password" id="pass" size="15" /> <span class="postingList">(for file deletion)</span></td>
	  </tr>
	</table>
	<div align="center" class="postingList">
		<br />Supported types: <?php $str = null; foreach($allowedExtensions as $ext) $str.=$ext.', '; echo substr($str, 0, strlen($str)-2); ?>
		 | Maximum size allowed is <?=MAX_SIZE * 1024?>kb | Images greater than <?=TH_W?>x<?=TH_H?> will be thumbailed | Any questions? Read the <a href="<?=HOME?>?FAQ">F.A.Q</a><br /><br />
	</div>
	</form>
	<script language="JavaScript" type="text/javascript">
	$(document).ready(function(){
		$('.idLinkR').click(function(e){
			e.preventDefault();
			var id = $(this).attr('name');
			$('#comment').append('>>' + id + "\n");
		});
		
		$('#changeStyle').click(function(){
			$.cookie('style', $('#styleChooser').val());
			$('#styleStatus').html('Style changed! Just refresh the site').slideDown('slow').fadeOut('slow');
		});
	});	
	</script>
	<hr />
	
	<?php
	if(!is_dir(IMAGES_FOLDER)) error ("Images directory ".IMAGES_FOLDER."/ not found");
	else if(!is_dir(THUMBS_FOLDER)) error ("Thumbs directory ".THUMBS_FOLDER."/ not found");
	else if($_POST['Submit'])
	{ // Submit new post
		$name = $_POST['name'];
		$email = $_POST['email'];
		$subject = $_POST['subject'];
		$comment = $_POST['comment'];
		$pass = $_POST['pass'];
		$file = $_FILES['file']['tmp_name'];
		
		$post_id = $_GET['reply'];
		$isReply = isset($_GET['reply']) ? true : false;
		
		$prevUrl = null;
		$fileUrl = null;
		
		$addToDb = false;
		
		$_SESSION['lastEmail'] = $email;
		
		if(empty($name))
			$name = 'Anonymous';
		
		if(empty($file) && !$isReply)
			error("The file field is empty!");
		// else if(empty($comment) && !$isReply)
			// error("The comment is empty!");
		else if($isReply && (empty($comment) && empty($file)))
			error("The file or the comment field must not be empty");
		else if(!empty($file) && !eregi('image', $_FILES['file']['type']))
			error("Only images are allowed, you tried to upload: " . $_FILES['file']['type']);
		else
		{
			if(($isReply && !empty($file)) || !$isReply)
			{
				list($w, $h) = getimagesize($file);
				if($w > MAX_W || $h > MAX_H)
					error('Max size allowed for images is '.MAX_W.'x'.MAX_H.' pixels.');
				else if(filesize(($file/1024) > (MAX_SIZE*1024)))
					error('Max size allowed for an image is '.MAX_SIZE.'mb');
				else
				{
					$ext = explode('.', $_FILES['file']['name']);
					$ext = $ext[count($ext)-1];
					if(empty($ext) || !in_array(strtolower($ext), $allowedExtensions))
						error("Invalid extension", true);
					
					$fileNumber = 0;
					while(file_exists(IMAGES_FOLDER . '/'. $fileNumber . '.' . $ext))	
						$fileNumber++;
						
					$fileUrl = IMAGES_FOLDER.'/'.$fileNumber.'.'.$ext;
					
					if(copy($file, $fileUrl) && !empty($file))
					{
						if($w > TH_W || $h > TH_H)
						{
							$prevUrl = THUMBS_FOLDER.'/'.$fileNumber.'.'.$ext;
							crop($fileUrl, $prevUrl, TH_W, TH_H, true, 100);
						}
						else
							$prevUrl = $fileUrl;
						$addToDb = true;
					}
					else
						error('Could not upload ' . $_FILES['file']['name']);
				}
			}
			else
				$addToDb = true;
			
			// Flood test
			if(isset($_SESSION['last']) && (time() - ($_SESSION['last'] + FLOOD_TIME) < 0))
			{
				$addToDb = false;
				@unlink($fileUrl);
				if($prevUrl != $fileUrl)
					@unlink($prevUrl);
				error('You must wait '.(abs(time() - ($_SESSION['last'] + FLOOD_TIME))).' seconds before posting again.');
			}
			
			// Add post to DB
			if($addToDb)
			{
				$_SESSION['last'] = time();
				$time = time();
				$ip = $_SERVER['REMOTE_ADDR'];
				if($isReply)
				{
					$replies->add(array(
						'post_id'=>$post_id,
						'time'=>$time, 
						'name'=>$name, 
						'email'=>$email,
						'subject'=>$subject,
						'comment'=>$comment,
						'thumb'=>$prevUrl,
						'image'=>$fileUrl,
						'pass'=>md5(sha1($pass)),
						'ip'=>$ip,
					));
					
					// Bump post
					if($email != 'sage')
					{
						$p = $posts->selectAt('id', $post_id);
						if($p['bumps']< MAX_BUMPS)
						{
							$posts->editAt('id', $post_id, array(
								'time'=>$time, 
								'bumps'=>($p['bumps']+1),
								'name'=>$p['name'], 
								'email'=>$p['email'],
								'subject'=>$p['subject'],
								'comment'=>$p['comment'],
								'thumb'=>$p['thumb'],
								'image'=>$p['image'],
								'pass'=>$p['pass'],
								'ip'=>$p['ip'],
							));
						}
					}
					
					echo 'Reply added. Redirecting...';
					
					if($email == 'noko')
						header('refresh:1;url=?reply='.$post_id);
					else
						header('refresh:1;url=?main');
				}
				else
				{
					$posts->add(array(
						'time'=>$time, 
						'bumps'=>0,
						'name'=>$name, 
						'email'=>$email,
						'subject'=>$subject,
						'comment'=>$comment,
						'thumb'=>$prevUrl,
						'image'=>$fileUrl,
						'pass'=>sha1(md5($pass)),
						'ip'=>$ip,
					));
					
					echo 'Post added. Redirecting...';
					
					if($email == 'noko')
					{
						$r = $posts->select($posts->getSize()-1);
						header('refresh:1;url=?reply=' . $r['id']);
					}
					else
						header('refresh:1;url=?page=1');
				}
			}
		}
	}
	else if(isset($_GET['report']))
	{
		$post_id = $_GET['post'];
		$reply_id = $_GET['reply'];
		if($_POST['sendReport'])
		{
			$reason = $_POST['reason'];
			if(empty($reason) || $reason == 'Report Reason')
				error('Empty reason');
			else if(isset($_SESSION['last']) && (time() - ($_SESSION['last'] + FLOOD_TIME) < 0))
				error('You must wait '.(abs(time() - ($_SESSION['last'] + FLOOD_TIME))).' seconds before another db action.');
			else
			{
				$reported->add(array(
					'post_id'=>$post_id,
					'reply_id'=>$reply_id,
					'reason'=>$reason,
					'board'=>SHORT_NAME,
				));
				echo 'Reported. Redirecting...';
				header('refresh:1;url=?main');
			}
		}
		else
			echo '<form method="post" action=""><textarea rows="10" cols="10" style="width: 400px; height: 200px;" name="reason">Report Reason</textarea><br /><input type="submit" value="Report" name="sendReport" /></form>';
	}
	else if(isset($_GET['delete']))
	{
		if($_POST['delete'] && isset($_GET['delete']))
		{
			$pass = $_POST['dpass'];
			if($_GET['w'] != 'r')
			{
				$p = $posts->selectAt('id', $_GET['delete']);
				if(($p['pass'] == md5(sha1($pass)) && ($p['pass'])!= md5(sha1(""))) || isAdmin())
				{
					$rep = $replies->selectWhereAt('post_id', $p['id']);
					if(!empty($rep[0]['id']))
					{
						foreach($rep as $r)
						{
							if(!empty($r['image'])) @unlink($r['image']);
							if(!empty($r['thumb']) && ($r['image'] != $r['thumb'])) @unlink($r['thumb']);
							$replies->deleteAt('id', $r['id']);
						}
					}
					if(!empty($p['image'])) @unlink($p['image']);
					if(!empty($p['thumb']) && ($p['image'] != $p['thumb'])) @unlink($p['thumb']);
					$posts->deleteAt('id', $p['id']);
					
					echo 'Post deleted.';
				}
				else
					error('Invalid password.');
			}
			else
			{
				$rep = $replies->selectAt('id', $_GET['delete']);
				if(($rep['pass'] == md5(sha1($pass)) && ($rep['pass']) != md5(sha1(""))) || isAdmin())
				{
					if(!empty($rep['image'])) @unlink($rep['image']);
					if(!empty($rep['thumb']) && ($rep['image'] != $rep['thumb'])) @unlink($rep['thumb']);
					$replies->deleteAt('id', $rep['id']);
					
					echo 'Reply deleted.';
				}
				else
					error('Invalid password.');
			}
		}
		else
			echo '<form method="post" action="">Password: <input type="password" name="dpass" id="dpass" /> <input type="submit" value="Delete" name="delete" /></form>';
	}
	else if(isset($_GET['reply']))
	{ 
		$id = $_GET['reply'];
		
		// Show post with all replies
		$entry = $posts->selectAt('id', $id);
		
		$ip = isAdmin() ? $entry['ip'] : '';
		list($w, $h) = getimagesize($entry['image']);
		echo '<div class="post">File: <a target="_blank" href="'.$entry['image'].'">'.substr($entry['image'], strlen(IMAGES_FOLDER)+1).'</a> - ('.round(filesize($entry['image'])/1024).'kb - '.$w.'x'.$h.')<br />
		<a target="_blank" href="'.$entry['image'].'" class="preview"><img border="0" style="margin:4px;margin-right:8px;" align="left" alt="File deleted" src="'.$entry['thumb'].'" width="'.TH_W.'" height="'.TH_H.'" /></a> <span class="subject">'.$entry['subject'].'</span> <span class="name"> '.tripcode($entry['name']).'</span> No. <a class="idLinkR" name="'.$entry['id'].'" href="#">'.$entry['id'].'</a> [<a href="?delete='.$entry['id'].'">Delete</a>] [<a href="?report&amp;post='.$entry['id'].'">Report</a>] '.$ip.'<br />
		'.hacode($entry['comment']).'<br /><div style="clear:both;"><br /></div></div>';
		
		if($resp = $replies->selectWhereAt('post_id', $entry['id']))
		{
			foreach($resp as $reply)
			{
				$ip = isAdmin() ? $reply['ip'] : '';
				$thumb = empty($reply['thumb']) ? '<br /><br />' : '<br /> File: <a target="_blank" href="'.$reply['image'].'">'.substr($reply['image'], strlen(IMAGES_FOLDER)+1).'</a> - ('.round(filesize($reply['image'])/1024).'kb - '.$w.'x'.$h.') <br /> <a href="'.$reply['image'].'" class="preview"><img border="0" src="'.$reply['thumb'].'" align="left" style="margin:5px;margin-right:8px" alt="" width="'.TH_W.'" height="'.TH_H.'" /></a>';
				echo '<div class="box"><span class="subject">'.$reply['subject'].'</span> <span class="name"> '.tripcode($reply['name']).'</span> No. <a class="idLinkR" name="'.$reply['id'].'" href="#">'.$reply['id'].'</a> [<a href="?delete='.$reply['id'].'&amp;w=r">Delete</a>] [<a href="?report&amp;post='.$entry['id'].'&amp;reply='.$reply['id'].'">Report</a>] '.$ip.$thumb.hacode($reply['comment']).'</div><br /><br />';
			}
		}
	}
	else
	{ // Show posts
		// Delete old mesagges
		if($posts->getSize() > IMAGES_PER_PAGE * MAX_PAGES)
		{
			$postsToDelete = $posts->selectRange(0, ($posts->getSize() - (IMAGES_PER_PAGE * MAX_PAGES)));
			foreach($postsToDelete as $p)
			{
				$rep = $replies->selectWhereAt('post_id', $p['id']);
				if(!empty($rep[0]['id']))
				{
					foreach($rep as $r)
					{
						if(!empty($r['image'])) @unlink($r['image']);
						if(!empty($r['thumb']) && ($r['image'] != $r['thumb'])) @unlink($r['thumb']);
						$replies->deleteAt('id', $r['id']);
					}
				}
				
				if(!empty($p['image'])) @unlink($p['image']);
				if(!empty($p['thumb']) && ($p['image'] != $p['thumb'])) @unlink($p['thumb']);
				$posts->deleteAt('id', $p['id']);
			}
		}
		
		$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
		$entries = $posts->selectByAt('time', true, IMAGES_PER_PAGE, ($page-1)*IMAGES_PER_PAGE);
		foreach($entries as $entry)
		{
			$ip = isAdmin() ? $entry['ip'] : '';
			list($w, $h) = getimagesize($entry['image']);
			echo '<div class="post">File: <a target="_blank" href="'.$entry['image'].'">'.substr($entry['image'], strlen(IMAGES_FOLDER)+1).'</a> - ('.round(filesize($entry['image'])/1024).'kb - '.$w.'x'.$h.')<br />
			<a target="_blank" href="'.$entry['image'].'" class="preview"><img border="0" style="margin:4px;margin-right:8px" align="left" alt="File deleted" src="'.$entry['thumb'].'" width="'.TH_W.'" height="'.TH_H.'" /></a> <span class="subject">'.$entry['subject'].'</span> <span class="name"> '.tripcode($entry['name']).'</span> No. <a class="idLink" href="?reply='.$entry['id'].'">'.$entry['id'].'</a> [<a href="?reply='.$entry['id'].'">Reply</a>] [<a href="?delete='.$entry['id'].'">Delete</a>] [<a href="?report&amp;post='.$entry['id'].'">Report</a>] '.$ip.'<br />
			'.hacode($entry['comment']).'<br /><div style="clear:both;"><br /></div></div>';
			
			// Latest first
			if($resp = $replies->selectWhereAt('post_id', $entry['id']))
			{
				//$resp = array_reverse($resp);
				if(count($resp)>REPLIES_SHOWN) {
          $start_reply = count($resp) - REPLIES_SHOWN;
          $end_reply = count($resp);
					echo '<i>Hiding '.$start_reply.' replies, click Reply to view.</i><br /><br />';
        } else {
          $start_reply = 0;
          $end_reply = REPLIES_SHOWN;
        }
				for($i = $start_reply; $i < $end_reply;  $i++)
					if(!empty($resp[$i]['id'])) // If the reply exists...
					{
						$ip = isAdmin() ? $resp[$i]['ip'] : '';
						$thumb = empty($resp[$i]['thumb']) ? '<br />' : '<br /> File: <a target="_blank" href="'.$resp[$i]['image'].'">'.substr($resp[$i]['image'], strlen(IMAGES_FOLDER)+1).'</a> - ('.round(filesize($resp[$i]['image'])/1024).'kb - '.$w.'x'.$h.') <br /> <a href="'.$resp[$i]['image'].'" class="preview"><img border="0" src="'.$resp[$i]['thumb'].'" align="left" style="margin:5px" alt="" width="'.TH_W.'" height="'.TH_H.'" /></a>';
						echo '<div class="box" style="margin-top:6px;"><a name="'.$resp[$i]['id'].'"></a><span class="subject">'.$resp[$i]['subject'].'</span> <span class="name"> '.tripcode($resp[$i]['name']).'</span> No. <a class="idLink" href="?reply='.$entry['id'].'">'.$resp[$i]['id'].'</a> [<a href="?delete='.$resp[$i]['id'].'&amp;w=r">Delete</a>] [<a href="?report&amp;post='.$entry['id'].'&amp;reply='.$resp[$i]['id'].'">Report</a>] '.$ip.$thumb.hacode($resp[$i]['comment']).'</div><br />';
					}
			}
			
			echo '<hr /><br />';
		}
		
		echo '<div class="box"><a href="?page='.($n = $page > 1 ? $page - 1 : $page).'">Previous</a> ';
		for($i = 1; $i <= MAX_PAGES; $i++)
			echo $show = $i == $page ? '<span style="padding-left: 5px;">[</span> '.$i.' ] ' : '<span style="padding-left: 5px;">[</span> <a href="?page='.$i.'"> '.$i.'</a> ] ';
		echo ' <a style="padding-left: 5px;" href="?page='.($n = $page < MAX_PAGES ? $page + 1 : $page).'">Next</a></div>';
	}
	?>	
	
	<div align="right">
		Style: 
		<select id="styleChooser">
			<?php
			foreach(config::$styles as $k=>$v)
			{
				$selected = null;
				if($v == $_COOKIE['style'])
					$selected = 'selected="selected"';
				echo '<option value="'.$v.'" '.$selected.'>'.$k.'</option>';
			}
			?>
		</select> 
		<input type="button" value="Change" id="changeStyle" />
		<div style="display:none; padding:5px;" id="styleStatus"></div>
	</div>
</div>
<div id="footer">
	<?= config::$copyright ?> - Powered by <a href="http://code.google.com/p/hachan/" target="_blank">HaChan</a></div>
</body>
</html>