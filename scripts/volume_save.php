<?php
if(isset($_GET['volume']))
{
	setcookie("volume", $_GET['volume'], time() + (86400*365), "/"); // 864000 = 1day
}
?>