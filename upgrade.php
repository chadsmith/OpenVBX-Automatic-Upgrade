<?php
if(!OpenVBX::isAdmin())
	die();
if(!class_exists('ZipArchive') && !class_exists('Archive_Tar'))
  die('The <a href="http://www.php.net/manual/en/book.zip.php">ZipArchive</a> or <a href="http://pear.php.net/package/Archive_Tar/">Archive_Tar</a> class must be installed to use this plugin.');
$cwd = dirname(__FILE__);
if(!is_writable($cwd))
	die($cwd . ' must be writable by ' . get_current_user());
$root = dirname(dirname($cwd));
if(!is_writable($root))
	die($root . ' must be writable by ' . get_current_user());
$tmp = $cwd . '/tmp/';
mkdir($tmp);
if(class_exists('ZipArchive')) {
  $archive = $tmp . 'OpenVBX.zip';
  file_put_contents($archive, file_get_contents('https://github.com/twilio/OpenVBX/zipball/master'));
  $z = new ZipArchive;
  if(true === $z->open($archive)) {
    $z->extractTo($tmp);
    $z->close();
    unlink($archive);
  }
  else
    die('Upgrade failed.');
}
else {
  $archive = $tmp . 'OpenVBX.tar.gz';
  file_put_contents($archive, file_get_contents('https://github.com/twilio/OpenVBX/tarball/master'));
  $a = new Archive_Tar($archive);
  if(true === $a->extract($tmp))
    unlink($archive);
  else
    die('Upgrade failed.');
}
$d = dir($tmp);
while($file = $d->read())
	if(!in_array($file, array('.', '..'))) {
		mvdir($tmp . $file, $root);
		rmall($tmp);
	}
$d->close();
die('Success!');

function mvdir($source, $target) {
	if(is_dir($source)) {
		@mkdir($target);
		$d = dir($source);
		while($file = $d->read())
			if(!in_array($file, array('.', '..'))) {
				$path = $source . '/' . $file;
				if(is_dir($path))
					mvdir($path, $target . '/' . $file);
				else
					@rename($path, $target . '/' . $file);
			}
		$d->close();
	}
	else
		@rename($source, $target);
}

function rmall($dir) {
	$files = glob($dir . '*', GLOB_MARK);
	foreach($files as $file) {
		if(is_dir($file))
			rmall($file);
		else
			@unlink($file);
	}
	if(is_dir($dir))
		@rmdir($dir);
}
