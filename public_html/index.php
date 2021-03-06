<?php
	include_once("../lib/localization.php");
	include_once("../lib/login.php");
	include_once("../views/views.php");
	include_once("../lib/connect_db.php");
	include_once("../config/general.php");

	$message_cookie = isset($_COOKIE['message'])?$_COOKIE['message']:'';
	$notify_cookie = isset($_COOKIE['notify'])?$_COOKIE['notify']:'';
	setcookie('ref',$_SERVER['REQUEST_URI'],0,$INDEX_ROOT);
	setcookie('notify','',time()-3600,$INDEX_ROOT);
	setcookie('message','',time()-3600,$INDEX_ROOT);
?>
<!DOCTYPE HTML>
<html>
<head>
	<base href=<?php echo $INDEX_ROOT;?> />
	<meta charset="utf-8" />
	<meta name="description" content="<?php echo _('Ηλεκτρονική τάξη Τμήματος Ηλεκτρολόγων Μηχανικών και Μηχανικών Υπολογιστών Αριστοτελείου Πανεπιστημίου Θεσσαλονίκης');?>">
	<title><?php echo "$TITLE - nethmmy";?></title>
	<link rel="shortcut icon" type="image/png" href="<?php echo "$INDEX_ROOT/images/resource/icon.png";?>" />
	<link rel="stylesheet" type="text/css" href="css/index.css" />
	<link rel="stylesheet" type="text/css" href="css/views.css" />
	<link rel="stylesheet" type="text/css" href="css/anytime.css" />
	<script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js"></script>
	<script type="text/javascript" src="../public_html/js/nicEdit.js"></script>
	<script src='js/anytime.js'></script>
</head>
<body>
<div class='bodyWrapper'>
	<div class='topBar'>
		<div class='loginInfo'>
<?php		if($logged_userid)
		{
			$username = $logged_userid;
			$last_login = time();
			$query = "SELECT username,last_login FROM users WHERE id='$logged_userid' LIMIT 1";
			$ret = mysql_query($query);
			if($ret && mysql_numrows($ret))
			{
				$result = mysql_fetch_array($ret);
				$username = $result['username'];
				$last_login = $result['last_login'];
			}
			echo "<span><a href='profile/$logged_userid/'>$username</a></span>";
		}
		else
		{?>
			<script type="text/javascript" >
				var loginExpanded = false;
				$(document).ready(function(){
					$('#showLoginLink').click(function(){
						if(!loginExpanded){
							$('#loginPrompt').show('fast');
							$(this).addClass('showLoginLinkExpanded');	
							loginExpanded = true;
						}
						else{
							$('#loginPrompt').hide('fast');
							$(this).removeClass('showLoginLinkExpanded');
							loginExpanded = false;
						}					
					});
					$('#forgotHref').click(function(){
						var email = prompt("<?php echo _('Please enter your email address');?>");
						if(email!=null && email!='') {
							$.ajax({
							   type: "POST",
							   url: "../public_html/request_change_password_email.php?AJAX",
							   data: 'email='+email,
							   cache: false,
							   success: function(response) {
									//works only when email server is set up correctly
									var ob = $.parseJSON(response);
									if(ob.error!='') {
										alert(ob.error);
									}
									else {
										alert(ob.message);
									}
								}
							});
						}
					});
				});
				
			</script>
			<a id='showLoginLink' href='javascript:void(0)'><?php echo _('Login');?></a>
			<a href='register/'><?php echo _('Register');?></a>
			<div id='loginPrompt'>
				<form method='post' action='login.php'>
					<div class='loginPromptLine1'>
						<input type='text' name='username' placeholder="<?php echo _('Username');?>" />
						<input type='password' name='password' placeholder="<?php echo _('Password');?>" />
						<span><input class='rememberCheck' type='checkbox' name='remember' value='1' <?php if(isset($_COOKIE['remember']) && $_COOKIE['remember']) echo " checked='checked'";?> />Remember me</span>
						<input type='submit' value="<?php echo _('Login');?>" /><span><a href='javascript:void(0)' id='forgotHref' class='forgotHref'>Forgot your password?</a></span>
					</div>
				</form>
			</div>
<?php		}?>
		</div>
		<div class='topNavigation'>
			<ul>
				<li><a id='homeLink' href='home/'><?php echo _('Home');?></a></li>
<?php				if($logged_userid)
				{?>
					<li><a href='logout.php'><?php echo _('Logout');?></a></li>			
<?php				}?>
			</ul>		
		</div>
	</div>
	<div class='header'>
		<div class='headerMain'>
			<h1> <?php echo _('NeTHMMY');?> </h1>
			<p> <?php echo _('Online classes application');?> </p>		
		</div>
	</div>
	<div class='mainBody'>
		<div class='navigationSide'>
<?php			include('../views/navigation.php'); ?>
		</div>
		<div class='mainView'>
			<div class='notificationSide'>
			</div>		
			<div class='viewContainer'>
			<?php
				include($VIEW);
			?>
			</div>
		</div>
	</div>
	<div class='footer'>
		<?php echo _('Aristotle University of Thessaloniki &copy;2012');?>
	</div>
</div>
<script type='text/javascript'>
	function report_error(error){
		if(error.length){
			var er = "<div class='notificationContainer'><img class='notifyIcon' src='images/resource/exclamation_sign.png' /><p id='notificationText'>"+error+"</p></div>";
			$('.notificationSide').append(er);
		}
	}
	function report_message(message){
		if(message.length){
			var er = "<div class='notificationContainer'><img class='notifyIcon' src='images/resource/notify_sign.png' /><p id='messageText'>"+message+"</p></div>";
			$('.notificationSide').append(er);
		}	
	}
	$(document).ready(function(){
<?php		foreach($error as $er){?>
			report_error("<?php echo $er;?>");
<?php		}?>
<?php		foreach($message as $mes){?>
			report_message("<?php echo $mes;?>");
<?php		}?>
<?php		if($notify_cookie) {
			$messages = explode($MESSAGE_SEPERATOR,$notify_cookie);
			foreach($messages as $message){?>
				report_error("<?php echo $message;?>");
<?php			}?>
<?php		}?>
<?php		if($message_cookie) {
			$messages = explode($MESSAGE_SEPERATOR,$message_cookie);
			foreach($messages as $message){?>
				report_message("<?php echo $message;?>");
<?php			}?>
<?php		}?>
	});
</script>
</body>
</html>
