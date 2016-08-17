<?php
/*
* Updates MySQL server with playlist
* Only use once per playlist
*
*
*/
require_once('scripts/variables.php');
ini_set('max_execution_time', 300);

// Vocaloid Playlist
/*
$table = "playlist_vocaloid";
$playlist = "music/vocaloid/vocaloid.m3u";
$outer_folder = "music/vocaloid/";
*/

// Favorites Playlist
/*
$table = "playlist_favorites";
$playlist = "music/dat favorite doe.m3u";
$outer_folder = "music/";
*/
	

// Touhou Playlist
/*
$table = "playlist_touhou";
$playlist = "music/touhou/touhou.m3u";
$outer_folder = "music/touhou/";
*/

// K-Pop Playlist
/*
$table = "playlist_kpop";
$playlist = "music/kppop/Kooreaaaa.m3u";
$outer_folder = "music/";
*/

if(!isset($playlist))
	die("page error.");

// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// truncate the current table
mysqli_query($conn, "TRUNCATE TABLE " . $table);

// getID3 setup
require_once('getid3/getid3.php');
$getID3 = new getID3;
$getID3->encoding = 'UTF-8';

// read playlist file
$playlist = fopen($playlist, 'r');
$playlist_items = array();
while(!feof($playlist))
{
	$line = fgets($playlist);
	$line = trim($line);
	array_push($playlist_items, $outer_folder.$line);
}
fclose($playlist);

// get playlist information
$id = 1;
$stmt = $conn->prepare("INSERT INTO ".$table." VALUES (?, ?, ?, ?)");
foreach($playlist_items as $item)
{
	$title = "";
	$artist = "";
	$info = $getID3->analyze($item);
	
	getid3_lib::CopyTagsToComments($info);
	
	//echo (!empty($info['comments_html']['title'])  ? implode('<br>', $info['comments_html']['title'])          : chr(160));
	if(count($info['comments_html']['title']) > 1)
	{
		$title = $info['comments_html']['title'][1];
	}
	else
	{
		$title =  $info['comments_html']['title'][0];
	}
	if(count($info['comments_html']['artist']) > 1)
	{
		$artist = $info['comments_html']['artist'][1];
	}
	else
	{
		$artist = $info['comments_html']['artist'][0];
	}
	
	$stmt->bind_param('isss', $id, $title, $artist, $item);
	$stmt->execute();
	
	$id++;
}

$stmt->close();
?>