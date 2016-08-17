<?php
function base64url_decode($data) { 
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
} 

function _Download($f_location, $f_name){
$file=uniqid().'.tmp';
file_put_contents($file,file_get_contents($f_location));
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename=' . basename($f_name));
readfile($file);
unlink($file);
ignore_user_abort(true);
if (connection_aborted()) {
    unlink($f);
}
}

if(isset($_GET['file'])){
	$file = base64url_decode($_GET['file']);
	$filename = explode('/', $file);
	$file_ext_array = explode('.', $filename[count($filename)-1]);
	$file_ext = $file_ext_array[count($file_ext_array)-1];
	$valid_ext = array("m4a", "mp3");
	$valid_check = false;
	// check for valid extensions
	for($i = 0; $i < count($valid_ext); $i++)
	{
		if($valid_ext[$i] == $file_ext)
		{
			$valid_check = true;
		}
	}
	//$converted_res = ($valid_check) ? 'true' : 'false';
	echo $converted_res;
	if(file_exists('../'.$file) && $valid_check)
	{
		$servername = "";
		$username = "";
		$password = "";
		$db = "";
		$date = date("Y-m-d H:i:s");
		$ip = $_SERVER['REMOTE_ADDR'];

		// Create connection
		$conn = new mysqli($servername, $username, $password, $db);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 
		
		$stmt = $conn->prepare("INSERT INTO downloads VALUES (?, ?, ?)");
		$stmt->bind_param('sss', $file, $date, $ip);
		$stmt->execute();
		$stmt->close();
		$conn->close();
		
		_Download('../'.$file, $filename[count($filename)-1]);
	}
}
//_Download('../'.base64url_decode($_GET['file']), "file.mp3");

?>