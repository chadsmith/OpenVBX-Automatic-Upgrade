<?php
if(!OpenVBX::isAdmin())
	die();
if(!class_exists('ZipArchive') && !class_exists('Phar'))
  die('The <a href="http://www.php.net/manual/en/book.zip.php">ZipArchive</a> or <a href="http://www.php.net/manual/en/book.phar.php">Phar</a> class must be installed to use this plugin.');
$cwd = dirname(__FILE__);
$tmp = $cwd . '/tmp/';
mkdir($tmp, 2775);
$root = dirname(dirname($cwd));
if(!is_writable($root))
	die($root . ' must be writable by ' . get_current_user());
if(!is_writable($tmp))
	die($tmp . ' must be writable by ' . get_current_user());
$archive = $tmp . 'archive';
if(class_exists('ZipArchive')) {
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
  file_put_contents($archive, file_get_contents('https://github.com/twilio/OpenVBX/tarball/master'));
  $p = new Phar($archive);
  if(true === $p->extractTo($tmp))
    unlink($archive);
  else
    die('Upgrade failed.');
}
$d = dir($tmp);
while($file = $d->read())
	if(!in_array($file, array('.', '..'))) {
		mvdir($tmp . $file, $root);
		rmall($tmp . $file);
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
