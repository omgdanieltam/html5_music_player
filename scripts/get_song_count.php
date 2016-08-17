<?php
if(isset($_GET['playlist']))
{
	require('variables.php');
	$table = $playlist[1];

	if(isset($playlist[$_GET['playlist']]))
		$table = $playlist[$_GET['playlist']]; 
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $db);

	if($result = $conn->query("SELECT * FROM ".$table))
	{
		$rows = $result->num_rows;
		print_r($rows);
		$result->close();
	}
	
	$conn->close();
	
}
?>