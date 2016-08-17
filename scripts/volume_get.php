<?php
function get_volume()
{
	if(isset($_COOKIE["volume"]))
	{
		return $_COOKIE["volume"];
	}
	else
	{
		return .8;
	}
}
?>