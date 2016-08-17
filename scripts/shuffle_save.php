<?php
if(isset($_GET['shuffle']))
{
	setcookie("shuffle", $_GET['shuffle'], time() + (86400*365), "/"); // 64000 = 1day
}
?>