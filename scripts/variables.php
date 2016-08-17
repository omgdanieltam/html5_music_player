<?php
/*
* This only to hold variables for importing
*
*/

// mysql
$servername = ""; //mysql server
$username = ""; //mysql username
$password = ""; //mysql pw
$db = ""; // mysql database

// playlists - these are matched to the mysql database names
$playlist = array(
	"playlist_favorites",
	"playlist_vocaloid",
	"playlist_touhou",
	"playlist_kpop"
);

// playlist names
$playlist_names = array(
	"Favorites",
	"Vocaloids",
	"Touhou",
	"K-Pop"
);

//start arrays at 1
array_unshift($playlist, "blank");
unset($playlist[0]);
array_unshift($playlist_names, "blank");
unset($playlist_names[0]);


?>