# html5_music_player
HTML5 based music player in the browser using Javascript, PHP, and MySQL

# INTRODUCTION
I needed a music player that played music hosted on my personal webserver so that I wouldn't need to upload all my music to any music hosting services. I decided that a HTML5 based music player was the best choice and most ideal since any modern browser should play it without any issues. I used jPlayer as the backend music player since that it also has a flash fallback incase the browser doesn't support the HTML5 <music> tag. With then the combination of HTML, Javascript, PHP, CSS, and MySQL, I've build an ideal music player for myself.


#HOW IT WORKS
Since I decided to use the album cover for every song, it uses the PHP ID3 tag libarary to pull the album art straight from the music file itself. Then, using jQuery it will update the album section with other information also pulled from the ID3 tag such as Song Title, Artist, and Album. 

Since I wanted the playlist to have information such as the Song Title and Artist, having the GetID3 PHP library pull the information on demand caused too long load times (over 30 seconds for 300+ songs). I decided to create a MySQL database backend that will store all the song information so that we can update it in the playlist. The MySQL database holds little information since we pull the information about the song when it loads. The GetID3 libary is fast enough that a single song won't slow it down, but large amounts of information will.

#SOURCES
http://jplayer.org/ -- jPlayer; jQuery HTML5 Audio/Video library
http://getid3.sourceforge.net/ -- getID3; PHP based ID3 tag grabber
Android 4.x -- Icons
GMMP -- Theme; based the theme off of GoneMad Music Player for Android devices
