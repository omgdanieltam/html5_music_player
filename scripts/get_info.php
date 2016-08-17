<?php
// GET based song info return
if(isset($_GET['path'])) // change the current card
{
	// getID3 setup
	require_once('../getid3/getid3.php');
	$getID3 = new getID3;
	$getID3->encoding = 'UTF-8';
	
	$info = $getID3->analyze('../'.$_GET['path']);
	
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
	if(count($info['comments_html']['album']) > 1)
	{
		$album = $info['comments_html']['album'][1];
	}
	else
	{
		$album = $info['comments_html']['album'][0];
	}

	if(isset($info['comments']['picture'][0]))
	{
		$Image='data:'.$info['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($info['comments']['picture'][0]['data']);
	}
	
	

	
	echo '<span class="info_title">'.$title.'</span><br /><span clas="info_artist">'.$artist.'</span><br /><span class="info_album">'.$album.'</span><br />';
	?>
	<center><div id="album_art" style="max-height: 300px; max-width: 300px; overflow: hidden; margin-top: 10px">
	<img id="FileImage" src="<?php echo @$Image;?>" style="width: 100%; height: 100%;">
	</div></center>
	<?php
}
?>