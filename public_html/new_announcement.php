<?php
	include_once('../lib/connect_db.php');
	include_once('../lib/login.php');
	include_once("../lib/access_rules.php");
	include_once("../lib/localization.php");
	include_once("../lib/validate.php");
	include_once("../config/general.php");
	include_once("../lib/log.php");

        if(!isset($error)) 
                $error = array();

	if(!isset($message))
		$message = array();

	/* Data */
	$title = isset($_POST['title'])?$_POST['title']:'';
	$text = isset($_POST['text'])?$_POST['text']:'';
	$class = isset($_POST['class'])?$_POST['class']:'';
	$urgent = isset($_POST['urgent'])?$_POST['urgent']:'0';
	
	/* check if input is valid */
	if(!(($e = class_id_validation($class)) || ($e = xml_validation($text)) || ($e = name_validation($title))))
	{
		if(can_post_announcement($logged_userid,$class))
		{	
			$query = "INSERT INTO announcements (class,poster,is_urgent,title,text,post_time,update_time)
					VALUES('$class','$logged_userid','$urgent','"
					.mysql_real_escape_string($title)."','"
					.mysql_real_escape_string(sanitize_html($text))."','".time()."','".time()."')";
			mysql_query($query) || ($error[] = mysql_error());
			if($announcement = mysql_insert_id())
			{
				$message[] = _('Announcement was posted succesfully.');
				announcement_creation_log($logged_userid,$announcement);			
			}	

			if($urgent)
			{
				//email the users of this class//
				$query = "SELECT title FROM classes WHERE id='$class'";
				$ret = mysql_query($query);
				if(!($ret && mysql_num_rows($ret) && ($class_title = mysql_result($ret,0,0))))
				{
					$class_title = _('ethmmy class');
				}
				$query = "SELECT id,email FROM users WHERE FIND_IN_SET($class, classes) AND email_urgent = '1'";
				$ret = mysql_query($query);
				if($ret && mysql_numrows($ret))
				{
					$fail = false;
					while($row = mysql_fetch_array($ret))
					{
						$to = $row['email'];
						$subject = sprintf(_('[ethmmy] Urgent announcement for %s'),$class_title);
						$message_body = $title.'\n'.$text;
						$headers = 'From: '.$NOTIFY_EMAIL_ADDRESS.'\n';
						
						if(!mail($to, $subject, $message_body, $headers))
						{
							$fail = true;
						}
						else
						{
							email_notification_log($row['id'],$ann);
						}
					}
					if($fail) 
						$error[] = _("Urgent delivery failed.");
				}		
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

		$redirect = "announcements/$class/";
		include('redirect.php');
	}
?>
