<?php
function cleanDir($path, $depth = -1){
	$depth++;
	$files = scandir($path);
	foreach ($files as $file) {
		if($file === '.' || $file === '..'): continue; endif;
		echo str_repeat("\t", $depth);
		if(is_dir($path.$file)){
			echo "/$file\n";
			cleanDir($path.$file.'/', $depth);
			rmdir($path.$file);
		} else {
			echo "$file\n";
			unlink($path.$file);
		}
	}
}
function chmodDir($path, $depth = -1){
	$depth++;
	$files = scandir($path);
	foreach ($files as $file) {
		if($file === '.' || $file === '..'): continue; endif;
		echo str_repeat("\t", $depth);
		if(is_dir($path.$file)){
			echo "/$file\n";
			cleanDir($path.$file.'/', $depth);
			chmod($path.$file, 0777);
		} else {
			echo "$file\n";
			chmod($path.$file, 0777);
		}
	}
}
echo "<pre>Files:\n";
$path = 'var/';
//cleanDir($path);
//rmdir($path);
//chmodDir($path);
//chmod('var/cache/dev/annotations', 0777);
//chmod('var/logs/dev.log', 0777);
