<?php
if(isset($_GET['lastsong']) && isset($_GET['lastplaylist']))
{
	setcookie("lastsong", $_GET['lastsong'].';'.$_GET['lastplaylist'], time() + (86400*10), "/"); // 864000 = 1day
}
?>