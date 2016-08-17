<?php
// GET based song return
if(isset($_GET['song']) && isset($_GET['playlist'])) // change the current card
{
	//setup
	require('variables.php');
	$table = $playlist[1];
	
	if(isset($playlist[$_GET['playlist']]))
		$table = $playlist[$_GET['playlist']];

	// Create connection
	$conn = new mysqli($servername, $username, $password, $db);
	
	$stmt = $conn->prepare("SELECT path FROM ".$table." WHERE id=?");
	$stmt->bind_param('i', $_GET['song']);
	$stmt->execute();
	$stmt->bind_result($song);
	$stmt->fetch();
	print_r($song);
	$stmt->close();
	
	
	$conn->close();
}
?>