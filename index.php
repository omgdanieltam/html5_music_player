<?php
/*
TODO:
Update mute button to current UI
Fix the play button picture, it's rough
Categories for playlist? -- probably not..
Save last playing song time?

.htaccess rewrite rules:
Options +FollowSymLinks
RewriteEngine on
RewriteRule ^/playlist/([0-9]+)/?$      /index.php?playlist=$1
RewriteRule ^/playlist/([0-9]+)/song/([0-9]+)/?$        /index.php?playlist=$1&song=$2


Libraries: 
PHP Getid3
jPlayer
jQuery

*/
require_once('scripts/shuffle_get.php');
require_once('scripts/volume_get.php');
require_once('scripts/get_song.php');
require_once('scripts/variables.php');
$song = "";

// check if there was a previous song playing
if(isset($_COOKIE['lastsong']) && (!isset($_GET['playlist'])))
{
	$lastplaying = explode(';', $_COOKIE['lastsong']);
	$_GET['song'] = $lastplaying[0];
	$_GET['playlist'] = $lastplaying[1];
}
?>
<!DOCTYPE html>
<html>
<meta charset="UTF-8">
<head>
<title>Music Player</title>
<?php
// change base url based on https or not
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
	echo '<base href="https://music.danieltam.net/">';
}
else
	echo '<base href="http://music.danieltam.net/">';
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script type="text/javascript" src="jPlayer/jquery.jplayer.min.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	/*
	 * jQuery UI ThemeRoller
	 *
	 * Includes code to hide GUI volume controls on mobile devices.
	 * ie., Where volume controls have no effect. See noVolume option for more info.
	 *
	 * Includes fix for Flash solution with MP4 files.
	 * ie., The timeupdates are ignored for 1000ms after changing the play-head.
	 * Alternative solution would be to use the slider option: {animate:false}
	 */
	var song_id = 0; // current playing song
	<?php
		if(isset($_GET['playlist']))
		{
			if(isset($playlist[$_GET['playlist']]))
				echo 'var playlist_id = '.$_GET['playlist'].'; // current playlist loaded
';
			else
				echo 'var playlist_id = 1; // current playlist loaded
';	
		}
		else
			echo 'var playlist_id = 1; // current playlist loaded
';
	?>
	var song_count = 0; // total amount of songs
	var song_history = new Array(101); // holds the song history
	var song_history_count = 0; // counter for the history
	var current_volume = 0; // holds the value for the current volume
	
	var myPlayer = $("#jquery_jplayer_1"),
		myPlayerData,
		fixFlash_mp4, // Flag: The m4a and m4v Flash player gives some old currentTime values when changed.
		fixFlash_mp4_id, // Timeout ID used with fixFlash_mp4
		ignore_timeupdate, // Flag used with fixFlash_mp4
		options = {
			ready: function (event) {
				// Hide the volume slider on mobile browsers. ie., They have no effect.
				if(event.jPlayer.status.noVolume) {
					// Add a class and then CSS rules deal with it.
					$(".jp-gui").addClass("jp-no-volume");
				}
				// Determine if Flash is being used and the mp4 media type is supplied. BTW, Supplying both mp3 and mp4 is pointless.
				fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);
				// Setup the player with media.
				<?php 
				if(isset($_GET['song']) && isset($_GET['playlist']))
				{
					$song = get_song($_GET['song']);
					echo '$(this).jPlayer("setMedia", {
					m4a: "'.$song.'"
					});
					song_id = '.$_GET['song'].';';
				}
				else
				{
					$song = get_song(1);
					echo '$(this).jPlayer("setMedia", {
					m4a: "'.$song.'"
					});
					song_id = 1;';
				}
				
				?>
			},
			play: function(event) {
				$("#play-button").css("display", "none");
				$("#pause-button").css("display", "inline-block");
			},
			pause: function(event) {
				$("#play-button").css("display", "inline-block");
				$("#pause-button").css("display", "none");
			},
			ended: function(event) { // change the song at the end
				if(!($("#jp_container_1").hasClass("jp-state-looped"))) // check to see if song is looped
				{
					updateSongHistory(song_id);
					if(isShuffleOn())
					{
						song_id = random_songid();
					}
					else
					{
						song_id++;
					}
					//change the song
					get_song(song_id);
				}
			},
			timeupdate: function(event) {
				if(!ignore_timeupdate) {
					myControl.progress.slider("value", event.jPlayer.status.currentPercentAbsolute);
				}
			},
			volumechange: function(event) {
				if(event.jPlayer.options.muted) {
					myControl.volume.slider("value", 0);
				} else {
					myControl.volume.slider("value", event.jPlayer.options.volume);
				}
			},
			swfPath: "/js",
			supplied: "m4a",
			<?php
				echo 'volume: "'.get_volume().'",';
			?>
			cssSelectorAncestor: "#jp_container_1",
			wmode: "window",
			keyEnabled: true
		},
		myControl = {
			progress: $(options.cssSelectorAncestor + " .jp-progress-slider"),
			volume: $(options.cssSelectorAncestor + " .jp-volume-slider")
		};

	// Instance jPlayer
	myPlayer.jPlayer(options);
	// A pointer to the jPlayer data object
	myPlayerData = myPlayer.data("jPlayer");
	// Define hover states of the buttons
	$('.jp-gui ul li').hover(
		function() { $(this).addClass('ui-state-hover'); },
		function() { $(this).removeClass('ui-state-hover'); }
	);
	// Create the progress slider control
	myControl.progress.slider({
		animate: "fast",
		max: 100,
		range: "min",
		step: 0.1,
		value : 0,
		slide: function(event, ui) {
			var sp = myPlayerData.status.seekPercent;
			if(sp > 0) {
				// Apply a fix to mp4 formats when the Flash is used.
				if(fixFlash_mp4) {
					ignore_timeupdate = true;
					clearTimeout(fixFlash_mp4_id);
					fixFlash_mp4_id = setTimeout(function() {
						ignore_timeupdate = false;
					},1000);
				}
				// Move the play-head to the value and factor in the seek percent.
				myPlayer.jPlayer("playHead", ui.value * (100 / sp));
			} else {
				// Create a timeout to reset this slider to zero.
				setTimeout(function() {
					myControl.progress.slider("value", 0);
				}, 0);
			}
		}
	});
	// Create the volume slider control
	myControl.volume.slider({
		animate: "fast",
		max: 1,
		range: "min",
		step: 0.01,
		value : <?php echo get_volume(); ?>,
		slide: function(event, ui) {
			myPlayer.jPlayer("option", "muted", false);
			myPlayer.jPlayer("option", "volume", ui.value);
			//save_volume(ui.value);
			update_volume(ui.value);
		}
	});
	
	// on song click
	$("#playlist_form").on("click", ".song_select", function () {
			// if the playlist changes
			if(playlist_id != $(this).attr("data-playlist"))
			{
				playlist_id = $(this).attr("data-playlist");
				clearSongHistory();
				updateSongCount();
				song_history_count = -1;
				$(".playlist_current_changetext").text($(".playlist_change_select option[value='"+$(this).attr("data-playlist")+"']").text());
			}
			get_song($(this).attr("data-id"));
			updateSongHistory(song_id);
     });
	 
	 // on play click
	 $(".player_play").click(function () {
			$("#jquery_jplayer_1").jPlayer("play");
			$("#play-button").css("display", "none");
			$("#pause-button").css("display", "inline-block");
     });
	 
	 // on pause click
	 $(".player_pause").click(function () {
			$("#jquery_jplayer_1").jPlayer("pause");
			$("#pause-button").css("display", "none");
			$("#play-button").css("display", "inline-block");
     });
	 
	 // on repeat on click
	 $(".player_repeat_on").click(function() {
			myPlayer.jPlayer("option", "loop", false);
			$("#repeat-button-on").css("display", "none");
			$("#repeat-button-off").css("display", "inline-block");
	 });
	 
	 // on repeat off click
	 $(".player_repeat_off").click(function() {
			myPlayer.jPlayer("option", "loop", true);
			$("#repeat-button-off").css("display", "none");
			$("#repeat-button-on").css("display", "inline-block");
	 });
	 
	 // on shuffle on click
	 $(".player_shuffle_on").click(function() {
			$("#jp_container_1").removeClass("shuffle");
			$("#shuffle-button-on").css("display", "none");
			$("#shuffle-button-off").css("display", "inline-block");
			save_shuffle(0);
	 });
	 
	 // on shuffle off click
	 $(".player_shuffle_off").click(function() {
			$("#jp_container_1").addClass("shuffle");
			$("#shuffle-button-off").css("display", "none");
			$("#shuffle-button-on").css("display", "inline-block");
			save_shuffle(1);
	 });
	 
	 // on prev click
	 $(".player_prev").click(function() {
			// make it so that under 5 seconds to restart the song, otherwise go back a song
			if(($('.jp-current-time').text() == "00:00") || ($('.jp-current-time').text() == "00:01") || ($('.jp-current-time').text() == "00:02") || ($('.jp-current-time').text() == "00:03") || ($('.jp-current-time').text() == "00:04") || ($('.jp-current-time').text() == "00:05") || ($('.jp-current-time').text() == "00:06"))
			{
				get_song(getPrevSong());
			}
			else
			{
				get_song(song_id);
			}
			$("#play-button").css("display", "none");
			$("#pause-button").css("display", "inline-block");
	 });
	 
	 // on next click
	 $(".player_next").click(function() {
			updateSongHistory(song_id);
			// if shuffle is on
			if(isShuffleOn())
			{
				song_id = random_songid();
			}
			else
			{
				song_id++;
			}
			get_song(song_id);
	 });
	 
	 // on dropdown playlist change
	 $(".playlist_change_select").on('change', function() {
		if(this.value != 0)
		{
			if($(".playlist_change_select option[value='"+this.value+"']").text() != $(".playlist_under_changetext").text())
			{
				updatePlaylist(this.value);
				$(".playlist").scrollTop(0);
				$(".playlist_under_changetext").text($(".playlist_change_select option[value='"+this.value+"']").text());
			}
			setTimeout(function(){
			$(".playlist_change_select").val('0');
			}, 1000);
		}
	 });
	 
	 // request new song info based on id
	 function get_song(id)
	 {
		 $.ajax({
			type: "GET",
			url: 'scripts/song_change.php',
			data:{song: id, playlist: playlist_id},
			success:function(response){
				changeSong(response, id);
				}
			});
	 };
	 
	 // get a random song id
	 function random_songid()
	 {
		 var newid;
		 while(newid = (1 + Math.floor(Math.random() * song_count))) // impossible to get the same song twice
		 {
			 if(newid != song_id)
				 break;
		 }
		 return newid;
	 };
	 
	 // update volume after 5 seconds to save constant save page spam
	 function update_volume(volume_value)
	 {
		current_volume = volume_value;
		setTimeout(function(){
			if(current_volume === volume_value)
			{
				save_volume(current_volume);
			}
		}, 5000);
	 };
	 
	 // update cookie for volume
	 function save_volume(volume_value)
	 {
		console.log("Volume value saved");
		 $.ajax({
			type: "GET",
			url: 'scripts/volume_save.php',
			data:{volume: volume_value},
			success:function(response){
				}
			});
	 };
	 
	 // update cookie for shuffle
	 function save_shuffle(shuffle_status)
	 {
		console.log("Shuffle value saved");
		$.ajax({
			type: "GET",
			url: 'scripts/shuffle_save.php',
			data:{shuffle: shuffle_status},
			success:function(response){
				}
			});
	 };
	 
	 // save the last playing song and playlist
	 function save_lastplaying()
	 {
		 $.ajax({
			type: "GET",
			url: 'scripts/last_song_save.php',
			data:{lastsong: song_id, lastplaylist: playlist_id},
			success:function(response){
				}
			});
	 }
	 
	 // get the song info
	 function getSongInfo(song)
	 {
		 $.ajax({
			type: "GET",
			url: 'scripts/get_info.php',
			data:{path: song},
			success:function(response){
				$('.info').html(response);
				}
			});
		updateDownloadLink(song);
	 };
	 
	 // is shuffle on
	 function isShuffleOn()
	 {
		return $('#jp_container_1').hasClass("shuffle");
	 };
	 
	 // change the page url
	 function changePage($page)
	 {
		history.replaceState(null, null, "/playlist/"+playlist_id+"/song/"+$page);
		song_id=$page;
	 };
	 
	 // change the song
	 function changeSong(song, id)
	 {
		changePage(id);
		getSongInfo(song);
		save_lastplaying();
		$("#jquery_jplayer_1").jPlayer("setMedia", {m4a: song}).jPlayer("play");
	 };
	 
	 // determine the previous song that was played
	 function getPrevSong()
	 {
		song_history_count--;
		if(song_history_count < 0)
		{
			song_history_count = 100;
		}
			
		if(song_history[song_history_count] === undefined)
		{
			return song_id;
		}
		else
		{
			return song_history[song_history_count];
		}
	 };
	 
	 // change the song history
	 function updateSongHistory(id)
	 {
		song_history[song_history_count] = id;
		song_history_count++;
		
		if(song_history_count > 100)
			song_history_count = 0;
	 };
	 
	 // clear the song history
	 function clearSongHistory()
	 {
		 song_history.length = 0;
	 };
	 
	 // update the song count
	 function updateSongCount()
	 {
		 $.ajax({
			type: "GET",
			url: 'scripts/get_song_count.php',
			data:{playlist: playlist_id},
			success:function(response){
				song_count = response;
				}
			});
	 };
	 
	 // change download link
	 function updateDownloadLink(path)
	 {
		 $.ajax({
			type: "GET",
			url: 'scripts/create_download_link.php',
			data:{link: path},
			success:function(response){
				$("#download_link").attr("href", "download/download.php?file=" + response);
				}
			});
	 };
	 
	 // update playlist with new playlist
	 function updatePlaylist(id)
	 {
		 $.ajax({
			type: "GET",
			url: 'scripts/get_playlist.php',
			data:{playlist: id},
			success:function(response){
				$("#playlist_form").html(response);
				}
			});
	 };
	 
	updateSongCount();
	<?php
	echo 'getSongInfo("'.$song.'");';
	 if(get_shuffle() == 1)
	 {
		echo '$(".player_shuffle_off").trigger("click");';
	 }
	 
	 if(isset($_GET['song']))
	 {
		 echo "
	var container = $('.playlist');
	scrollTo = $('#song_".$_GET['song']."');

	container.scrollTop(
    scrollTo.offset().top - container.offset().top + container.scrollTop()
	);";
	 }
	?> 
	
});
//]]>
</script>
<style>
<!--
body {
	font-family: helvetica;
	font-size: 10pt;
	background-color: #ECEFF1;
	margin: 1px 0px 0px 0px;
	background-image: url('img/cat2.png');
	background-repeat: no-repeat;
	background-position: right bottom;
}
.aspectwrapper {
display: inline-block; /* shrink to fit */
width: 100%; /* whatever width you like */
min-width: 850px;
min-height: 600px;
position: relative; /* so .content can use position: absolute */
}
.aspectwrapper::after {
padding-top: 51%; /* percentage of containing block _width_ */
display: block;
content: '';
}
.content {
position: absolute;
top: 0; bottom: 0; right: 0; left: 0; /* follow the parent's edges */
overflow: hidden;
}
.left {
	width: 45%;
	height: 100%;
	float: left;
	overflow: auto;
}
.right {
	width: 55%;
	height: 100%;
	float: right;
	overflow: hidden;
}

.info {
	padding-top: 15px;
	text-align: center;
	height: 55%;
}

.menu {
	position: relative;
	height: 70px;
	width: 100%;
}

.playlist {
	position: relative;
	height: 96%;
	overflow-x: hidden;
	overflow-y: auto;
	border-left: 1px solid #546E7A;
}

.playlist_under_text {
	height: 3%;
	clear: both;
	text-align: center;
	color: #37474F;
	padding-top: 5px;
}

.playlist ul li{
	list-style: none;
	display: block;
	padding-top: 4px;
	padding-bottom: 4px;
	padding-left: 4px;
}

.playlist ul hr {
	margin: 0;
	border: 0;
    height: 1px;
    background: #333;
    background-image: linear-gradient(to right, #ccc, #333, #ccc);
}

.playlist ul a {
	text-decoration: none;
	color: black;
}
.playlist ul a:hover li {
	/*background-color: #33b5e5;*/
	background-color: #B0BEC5;
	color: white;
}
.playlist ul a:active li {
	/*background-color: #0099cc;*/
	background-color: #607D8B;
	color: white;
}
.playlist ul li:hover{
	background-color: #B0BEC5;
	color: white;
}
.playlist ul {
	padding: 0;
	margin: 0;
}

.info_title {
	font-size: 14pt;
}
.jp-gui {
	position:relative;
	padding:20px;
	width:628px;
}
.jp-gui.jp-no-volume {
	width:432px;
}
.jp-gui ul {
	margin:0;
	padding:0;
}
.jp-gui ul li {
	position:relative;
	float:left;
	list-style:none;
	margin:2px;
	padding:4px 0;
	cursor:pointer;
}
.jp-gui ul li a {
	margin:0 4px;
}
.jp-gui li.jp-repeat,
.jp-gui li.jp-repeat-off {
	margin-left:344px;
}
.jp-gui li.jp-mute,
.jp-gui li.jp-unmute {
	margin-left:80px;
	margin-top: 12px;
}
.jp-gui li.jp-volume-max {
	margin-left:120px;
	margin-top: 15px;
}
li.jp-pause,
li.jp-repeat-off,
li.jp-shuffle-off,
li.jp-unmute,
.jp-no-solution {
	display:none;
}
.jp-progress-slider {
	position:absolute;
	top:28px;
	left:100px;
	width:300px;
	height: 4px;
	background: #ECEFF1 !important;
}
.jp-progress-slider .ui-slider-handle {
	cursor:pointer;
	height: 1em !important;
	width: 1em !important;
	top: -.4em;
	border-radius: 50%;
	background: #546E7A !important;
	border: #546E7A !important;
}
.jp-progress-slider .ui-slider-range {
	background: #546E7A !important;
}
.jp-volume-slider {
	position:absolute;
	top:71px;
	left:134px;
	width:100px;
	height: 4px;
	background: #ECEFF1 !important;
}
.jp-volume-slider .ui-slider-handle {
	height:.9em;
	width:.9em;
	cursor:pointer;
	border-radius: 50%;
	background: #546E7A !important;
	top: -.3em;
	border: #546E7A !important;
}
.jp-volume-slider .ui-slider-range {
	background: #546E7A !important;
}
.jp-gui.jp-no-volume .jp-volume-slider {
	display:none;
}
.jp-current-time,
.jp-duration {
	position:absolute;
	top:24px;
	font-size:0.8em;
	cursor:default;
	color: #37474F;
}
.jp-current-time {
	left:50px;
}
.jp-duration {
	right:217px;
}
.jp-gui.jp-no-volume .jp-duration {
	right:70px;
}
.jp-clearboth {
	clear:both;
}

#jp_container_1 {
	width: 500px;
	overflow: hidden;
}


.jp-volume-max {
	display: none !important;
}
.download_link {
	clear: both;
	text-align: right;
	color: #37474F;

}
#controls {
padding-left: 50px;
padding-right: 50px;
margin-top: 50px;
text-align: justify;
}

#repeat-button-off, #prev-button, #play-button, #next-button, #shuffle-button-off{
display: inline-block;
width: 50px;
height: 50px;
zoom: 1;
}

#controls:after {
content: '';
width: 100%;
display: inline-block;
font-size: 0;
line-height: 0;
letter-spacing: 0;
}

#pause-button, #repeat-button-on, #shuffle-button-on {
display: none;
width: 50px;
height: 50px;
zoom: 1;
}

.playlist_changer {
	width: 100%;
	position: relative;
	overflow: hidden;
}

.playlist_changer ul {
	position: relative;
	width: 90%;
}

.playlist_changer ul li {
	width: 45%;
	float: left;
	padding: 2.5%;
	list-style: none;
}

.playlist_current_text {
	text-align: left;
	color: #37474F;
}

.playlist_dropdown {
	text-align: right;
}

.playlist_dropdown select {
	width: 150px;
	color: #37474F;
}

.hide_me {
display: none;
};
-->
</style>
</head>
<body>
<div class="aspectwrapper">
<div class="content">
<div class="left">
<div class="info">
</div>
<div id="controls">
<div id="repeat-button-off"><a href="javascript:;" class="player_repeat_off" tabindex="1"><img src="img/repeat-off-3.png" width="50" height="50"></a></div>
<div id="repeat-button-on"><a href="javascript:;" class="player_repeat_on" tabindex="1"><img src="img/repeat-on-2.png" width="50" height="50"></a></div>
<div id="prev-button"><a href="javascript:;" class="player_prev" tabindex="1"><img src="img/prev-3.png" width="50" height="50"></a></div>
<div id="play-button"><a href="javascript:;" class="player_play" tabindex="1"><img id="play-button-img" src="img/play-3.png" width="50" height="50"></a></div>
<div id="pause-button"><a href="javascript:;" class="player_pause" tabindex="1"><img id="pause-button-img" src="img/pause-3.png" width="50" height="50"></a></div>
<div id="next-button"><a href="javascript:;" class="player_next" tabindex="1"><img src="img/next-4.png" width="50" height="50"></a></div>
<div id="shuffle-button-off"><a href="javascript:;" class="player_shuffle_off" tabindex="1"><img src="img/shuffle-off-3.png" width="50" height="50"></a></div>
<div id="shuffle-button-on"><a href="javascript:;" class="player_shuffle_on" tabindex="1"><img src="img/shuffle-on-2.png" width="50" height="50"></a></div>
</div>
		<div id="jquery_jplayer_1" class="jp-jplayer"></div>

		<center>
		<div id="jp_container_1">
			<div class="jp-gui ui-widget">
				<ul>
					<div class="hide_me"><li class="jp-play ui-state-default ui-corner-all"><a href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">play</a></li>
					<li class="jp-pause ui-state-default ui-corner-all"><a href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">pause</a></li>
					<li class="jp-stop ui-state-default ui-corner-all"><a href="javascript:;" class="jp-stop ui-icon ui-icon-stop" tabindex="1" title="stop">stop</a></li>
					<li class="jp-repeat ui-state-default ui-corner-all"><a href="javascript:;" class="jp-repeat ui-icon ui-icon-refresh" tabindex="1" title="repeat">repeat</a></li>
					<li class="jp-repeat-off ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-repeat-off ui-icon ui-icon-refresh" tabindex="1" title="repeat off">repeat off</a></li>
					<li class="jp-shuffle ui-state-default ui-corner-all"><a href="javascript:;" class="jp-shuffle ui-icon ui-icon-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
					<li class="jp-shuffle-off ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-shuffle-off ui-icon ui-icon-shuffle" tabindex="1" title="shuffle off">shuffle off</a></li></div>
					
				</ul>
				<div class="jp-progress-slider"></div>
				<div class="jp-volume-slider"></div>
				<div class="jp-current-time"></div>
				<div class="jp-duration"></div>
				<div class="jp-clearboth"></div>
				<ul style="margin-top: 10px;">
				<div class="hide_me"><li class="jp-seek-prev ui-state-default ui-corner-all"><a href="javascript:;" class="jp-seek-prev ui-icon ui-icon-seek-prev" tabindex="1" title="seek-prev">seek-prev</a></li>
				<li class="jp-seek-next ui-state-default ui-corner-all"><a href="javascript:;" class="jp-seek-next ui-icon ui-icon-seek-next" tabindex="1" title="seek-next">seek-next</a></li></div>
				<br /><li class="jp-mute ui-state-default ui-corner-all"><a href="javascript:;" class="jp-mute ui-icon ui-icon-volume-off" tabindex="1" title="mute">mute</a></li>
				<li class="jp-unmute ui-state-default ui-state-active ui-corner-all"><a href="javascript:;" class="jp-unmute ui-icon ui-icon-volume-off" tabindex="1" title="unmute">unmute</a></li>
				<li class="jp-volume-max ui-state-default ui-corner-all"><a href="javascript:;" class="jp-volume-max ui-icon ui-icon-volume-on" tabindex="1" title="max volume">max volume</a></li>
				</ul>
				<br />
			</div>
			<!--<a href="javascript:;" class="change_playlist1">Favorites Playlist</a>
			<a href="javascript:;" class="change_playlist2">Vocaloids Playlist</a>-->
			<div class="jp-no-solution">
				<span>Update Required</span>
				To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
			</div>
		</center>
		<div class="playlist_changer">
			<ul>
				<li class="playlist_current_text">Current playlist: <span class="playlist_current_changetext">
			<?php
if(isset($_GET['playlist']))
	if(isset($playlist_names[$_GET['playlist']]))
		echo $playlist_names[$_GET['playlist']];
	else
		echo $playlist_names[1];
else
	echo $playlist_names[1];
?></span></li>
			<li class="playlist_dropdown"><select class="playlist_change_select"><?php 
			echo '<option value="0">Select Playlist:</option>';
			for($i = 1; $i <= count($playlist_names); $i++)
			{
			echo '<option value="'.$i.'">'.$playlist_names[$i].'</option>';
			}?></select>
			<br />
			<div class="download_link">
			Download song: <a id="download_link" href="download/download.php?file=" ><img src="img/download.png" width="10" height="10"/></a>
			</div>
			</li>
			</ul>
		</div>
</div><!-- left? -->
<div class="right">
<div class="playlist">
<ul id="playlist_form">
<?php // this is required for the scroll to work properly for the first song (until I fix it)
if(isset($_GET['playlist']))
	$playlist = $_GET['playlist'];
else
	$playlist = 1;
require('scripts/function_get_playlist.php');
get_playlist($playlist);
?>
</ul>
</div>
<div class="playlist_under_text">Viewing playlist: <span class="playlist_under_changetext">
<?php
if(isset($_GET['playlist']))
	if(isset($playlist_names[$_GET['playlist']]))
		echo $playlist_names[$_GET['playlist']];
	else
		echo $playlist_names[1];
else
	echo $playlist_names[1];
?></span>
</div>
</div>
</div>
</div>
</div>
</body>

</html>