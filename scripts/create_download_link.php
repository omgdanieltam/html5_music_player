<?php
function base64url_encode($data) { 
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 

if(isset($_GET['link']))
{
	echo base64url_encode($_GET['link']);
}