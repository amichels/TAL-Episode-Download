<?php

if(isset($_POST['episode'])) $ep=$_POST['episode'];

//get url from input
$url = 'http://stream.thisamericanlife.org/'.$ep.'/stream/'.$ep.'_64k.m3u8';
//get stream with highest bandwith
$streamUrl = getHighBandwidthStream($url);
getFiles($streamUrl,$ep);

//input: string, output: string
function getHighBandwidthStream($masterUrl) {
	//get content of master.m3u8
	$ch = curl_init($masterUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);

	$result = split("\n", $result);
	foreach($result as $key => $one) {
    if(strpos($one, '.ts') === false)
        unset($result[$key]);
	}
	return $result;
}

function getFiles($array,$ep){
	if (!file_exists('files')) {
	    mkdir('files', 0777, true);
	}
	foreach($array as $key => $one) {
		$url = 'http://stream.thisamericanlife.org/'.$ep.'/stream/'.$one;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		file_put_contents('files/'.$one, $result);
	}
	mergeFiles('files',$ep);
}

function mergeFiles($dirName,$ep) {
	//get all *.ts files in directory
	if ($handle = opendir($dirName)) {
	while (false !== ($file = readdir($handle))) {
		if (strpos($file, ".ts") !== false) {
			$fileList = $fileList." files/".$file;
		}
	}
	closedir($handle);
	}
	
	//join and remove parts
	$shellScript = 'cat '.substr($fileList, 1).' >> ep-'.$ep.'.mpeg';
	shell_exec($shellScript);
	shell_exec("rm -r files");
}
?>