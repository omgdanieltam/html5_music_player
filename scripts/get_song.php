<?php
// function based song return
function get_song ($songid)
{
	require('variables.php');
	$table = $playlist[1];
	
	if(isset($playlist[$_GET['playlist']]))
		$table = $playlist[$_GET['playlist']]; 
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $db);
	
	$stmt = $conn->prepare("SELECT path FROM ".$table." WHERE id=?");
	$stmt->bind_param('i', $songid);
	$stmt->execute();
	$stmt->bind_result($song);
	$stmt->fetch();
	return $song;
	$stmt->close();
	
	
	$conn->close();
	
}
?>
