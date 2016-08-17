<?php
function get_shuffle()
{
	if(isset($_COOKIE["shuffle"]))
	{
		return $_COOKIE["shuffle"];
	}
	else
	{
		return 0;
	}
}
?>