<?php
	include_once("../lib/connect_db.php");
	include_once("../lib/access_rules.php");
	include_once("../config/security.php");
	include_once("../config/general.php");
	include_once("../lib/login.php");
	include_once("../lib/localization.php");
	include_once("../lib/validate.php");
	include_once('../lib/log.php');

        if(!isset($error))
                $error = array();

	if(!isset($message))
		$message = array();

	/*Get user type*/
	$user_type = isset($_POST['user_type'])?$_POST['user_type']:false;
	$uid = isset($_POST['uid'])?$_POST['uid']:0;
	if(!(($uid_is_invalid = $e = user_id_validation($uid)) || ($e = user_type_validation($user_type))))
	{
		if(can_edit_user_type($logged_userid,$uid))
		{
			/* update database */
			$query = "UPDATE users 
					SET user_type = '$user_type'
					WHERE id='$uid' LIMIT 1";
			if(mysql_query($query))
			{
				user_type_edit_log($logged_userid,$uid);
				$message[] = _('User type updated successfully.');
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

	/* Activate/deactivate */
	$activate = isset($_POST['active_status'])?$_POST['active_status']:'-1';
	if(!$uid_is_invalid)
	{
		if(($activate === '0') || ($activate === '1'))
		{
			if(can_edit_active_status($logged_userid,$uid))
			{
				/* update database */
				$query = "UPDATE users 
						SET is_active = '$activate'
						WHERE id='$uid' LIMIT 1";
				if(mysql_query($query))
				{
					user_active_status_edit_log($logged_userid,$uid);
					$message[] = _('Active status changed successfully.');
				}			
			}
			else
			{
				$error[] = _('Access denied.');
			}
		}
		elseif($activate !== '-1')
		{
			$error[] = _('Invalid active status.');
		}
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

		$redirect = "profile/$uid/";
		include('redirect.php');
	}
?>	
