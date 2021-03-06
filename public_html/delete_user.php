<?php
	include_once('../lib/connect_db.php');
	include_once('../lib/login.php');
	include_once("../lib/access_rules.php");
	include_once("../lib/localization.php");
	include_once("../lib/validate.php");
	include_once("../config/general.php");
	include_once('../lib/log.php');

        if(!isset($error)) 
                $error = array();

	if(!isset($message))
		$message = array();

	/* Data */
	$uid = isset($_POST['uid'])?$_POST['uid']:'';
	$user = '';
	if(!($e = user_id_validation($uid)))
	{
		$user = $uid;
		if(can_delete_user($logged_userid,$uid))
		{
			$query = "DELETE FROM users WHERE id='$uid' LIMIT 1";
			if(mysql_query($query))
			{
				user_deletion_log($logged_userid,$uid);
				$message[] = _("User account was deleted successfully.");	
			}
		}
		else
		{
			$error[] = _('Access denied.');
		}	
	}
	else
	{
		$error[] = $e;
	}

	if(isset($_GET['AJAX']))
	{ 
		echo '{ "error" : ['.(count($error)?('"'.implode('","',$error).'"'):'').'],
			"message" : ['.(count($message)?('"'.implode('","',$message).'"'):'').']}';
	}
	elseif(!(isset($DONT_REDIRECT) && $DONT_REDIRECT))
	{
		if(isset($message) && count($message))
			setcookie('message',implode($MESSAGE_SEPERATOR,$message),time()+3600,$INDEX_ROOT);

		if(isset($error) && count($error))
			setcookie('notify',implode($MESSAGE_SEPERATOR,$error),time()+3600,$INDEX_ROOT);

		$redirect = ($class && $error)?"profile/$class/":"home/";
		include('redirect.php');
	}
?>
