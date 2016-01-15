<script type="text/javascript" language="JavaScript">
$(document).ready(function(){
	$('.delLink').click(function(e){
		e.preventDefault();
		
		$('#' + $(this).attr('name')).css({
			backgroundColor:'red',
			color:'white'
		});
		$('#' + $(this).attr("name")).fadeOut('slow');
		$.get("admin.php", {'delete':'' , 'id':$(this).attr('name')});
	});
});
</script>
<div id="forumListContainer">
<h1>Admin Panel </h1>
<?php
include_once('lib/phlat.class.php');
include_once('config.php');
if(isAdmin())
{
	$reported = new phlat('reported', array('post_id', 'reply_id', 'reason', 'board'));
	
	echo '<div style="padding: 5px;" />';
	
	if(isset($_GET['logout']))
	{
		$_SESSION['user'] = '';
		$_SESSION['pass'] = '';
		echo 'You are now logged out.';
		header('refresh:2;url=?main');
	}
	else if(isset($_GET['delete']))
	{
		$reported->deleteAt('id', $_GET['id']);
	}
	elseif(isset($_GET['reported']))
	{
		$rep = $reported->get();
		echo '<ul>';
		if(empty($rep[0]['id']))
			echo '<li>No reports</li>';
		else
		{
			foreach($rep as $entry)
			{
				$type = empty($entry['reply_id']) ? 'post' : 'reply';
				$id = empty($entry['reply_id']) ? $entry['post_id'] : $entry['reply_id'];
				echo '<li id="'.$entry['id'].'"><a href="#" name="'.$entry['id'].'" class="delLink">Delete</a> - '.$type.' n&ordm; <a href="'.config::$url.$entry['board'].'index.php?reply='.$entry['post_id'].'#'.$id.'">'.$id.'</a>: '.$entry['reason'].'</li>';
			}
		}
		echo '</ul><br /><div align="right">[<a href="?admin&amp;readall">Mark all as read</a>] [<a href="?admin">Admin menu</a>] </div>';
	}
	elseif(isset($_GET['readall']))
	{
		$reported->clear();
		echo 'All reports marked as readed. [<a href="?admin&amp;reported">Go back</a>]<div align="right"> [<a href="?admin">Admin menu</a>] </div>';
	}
	else
		echo 'Welcome '.$_SESSION['user'].'!<br />You are now logged in.<br />You will be able to delete all posts and replies and view users IP.<br />HaChan is using <strong>'.round((getFolderSize()/1024)/1024).'</strong> Megabytes of disk space.<br /><div align="right">[<a href="?admin&amp;reported">View Reported Posts</a>] [<a href="?admin&amp;logout">Logout</a>] </div>';
	
	echo '</div>';
}
else
{
	if($_POST['Submit'])
	{
		$_SESSION['user'] = $_POST['user'];
		$_SESSION['pass'] = $_POST['pass'];
		echo 'Verifying data...';
		header('refresh:1;url=?admin');
	}
	else
	{
		echo '<form action="" method="post">
		<table border="0">
		<tr>
		<td>Username</td>
		<td><input name="user" type="text" id="user" /></td>
		</tr>
		<tr>
		<td>Password</td>
		<td><input name="pass" type="password" id="pass" /></td>
		</tr>
		<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="Submit" value="Log in" /></td>
		</tr>
		</table></form>';
	}
}
?>
</div>