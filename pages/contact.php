<?

if (
	isset($_POST['email-address']) &&
	isset($_POST['email-subject']) &&
	isset($_POST['email-message']) &&
	$_POST['email-address'] != '' &&
	$_POST['email-subject'] != '' &&
	$_POST['email-message'] != ''
) {
	mail('jneal@liferay.com', 'CMS.txt | ' . $_POST['email-subject'], $_POST['email-message'], 'From: ' . $_POST['email-address'] . "\r\n" . 'Reply-To: ' . $_POST['email-address'] . "\r\n" . 'X-Mailer: PHP/' . phpversion());
	header('Location: ./');
}

?>
<div style="float: left; margin: 0 10px;"><img class="logo" src="pages/cmsdottext.jpg"></div>
<div style="float: left; margin: 0 10px;"><form method="post" action="./contact">
<h3>
	Your Email:
</h3>
<input class="input" type="text" name="email-address" style="width: 400px;">
<h3>
	Subject:
</h3>
<input class="input" type="text" value="Hey Nerd!" name="email-subject" style="width: 400px;">
<h3>
	Message:
</h3>
<textarea class="input" cols="45" name="email-message" rows="22" style="width: 400px;"></textarea>
<div class="clear padding"></div>
<input class="input" type="submit" value="Write Me!">
</form></div>
<div style="clear:both;"></div>