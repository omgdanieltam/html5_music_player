<?php
if(isset($_GET['playlist']))
{
	require('variables.php');
	$table = $playlist[1];
	$play_id = 1;
	
	if(isset($playlist[$_GET['playlist']]))
	{
		$table = $playlist[$_GET['playlist']];
		$play_id = $_GET['playlist'];
	}

	// Create connection
	$conn = new mysqli($servername, $username, $password, $db);

	if($result = $conn->query("SELECT * FROM ".$table))
	{
		foreach($result as $item)
		{
			//echo '<a href="javascript:;" class="song_select" data-id="'.$item['id'].'">'.$item['title'].' by '.$item['artist'].'</a><br />';
			echo '<a href="javascript:;" class="song_select" id="song_'.$item['id'].'" data-playlist="'.$play_id.'" data-id="'.$item['id'].'"><li><span style="font-size: 12pt;">'.$item['id'].'. '.$item['title'].'</span><br /><span style="font-size: 9pt">'.$item['artist'].'</span></li></a><hr />';
		}
		$result->close();
	}
}
?>