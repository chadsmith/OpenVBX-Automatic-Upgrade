<?php
if(!OpenVBX::isAdmin())
	die();
$cwd=dirname(__FILE__);
$tmp=$cwd.'/tmp/';
$root=dirname(dirname($cwd));
if(!is_writable($root))
	die($root.' must be writable by '.get_current_user());
if(!is_writable($tmp))
	die($tmp.' must be writable by '.get_current_user());
if(!class_exists('ZipArchive'))
	die('The ZipArchive Class must be inststalled to use this plugin.');
$zip=$tmp.'zip';
file_put_contents($zip,file_get_contents('https://nodeload.github.com/twilio/OpenVBX/zip/master'));
$z=new ZipArchive;
if(true===$z->open($zip)){
    $z->extractTo($tmp);
    $z->close();
	@unlink($zip);
	$d=dir($tmp);
	while($file=$d->read())
		if(!in_array($file,array('.','..'))){
			mvdir($tmp.$file,$root);
			rmall($tmp.$file);
		}
	$d->close();
	die('Success!');
}
die('Upgrade failed.');
function mvdir($source,$target){
	if(is_dir($source)){
		@mkdir($target);
		$d=dir($source);
		while($file=$d->read())
			if(!in_array($file,array('.','..'))){
				$path=$source.'/'.$file;
				if(is_dir($path))
					mvdir($path,$target.'/'.$file);
				else
					@rename($path,$target.'/'.$file);
			}
		$d->close();
	}else
		@rename($source,$target);
}
function rmall($dir){
	$files=glob($dir.'*',GLOB_MARK);
	foreach($files as $file){
		if(is_dir($file))
			rmall($file);
		else
			@unlink($file);
	}
	if(is_dir($dir))
		@rmdir($dir);
}
