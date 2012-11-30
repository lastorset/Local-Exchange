<?php
/** This will be a script that extract strings that use the gettext heredoc
 *  workaround, and put them in a separate file that can be read by gettext.
 */
//$dirs = array(".", "includes", "classes");
$dirs = array_slice($argv, 1);
$out = STDOUT;

fwrite($out, "<?php\n// Gettext strings extracted from heredocs\n");

foreach ($dirs as $dir) {
	foreach(php_files_in_dir(getcwd() ."/". $dir) as $filename) {
		heredocs($filename, $out);
	}
}
fwrite($out, "?>");
fclose($out);

function php_files_in_dir($dir) {
	$filenames = array_filter(scandir($dir), function($entry) {
		return ((strpos($entry, ".php") !== FALSE)
			&& (strpos($entry, ".sw") === FALSE));
		} );
	return array_map(function($filename) use ($dir) { return $dir ."/". $filename; }, $filenames);
}

function heredocs($filename, $out) {
	$file = file_get_contents($filename);
	if (preg_match_all("/<<<(\w+)(.*\n)*?\\1/", $file, $matches)) {
		fwrite($out, "\n// $filename:\n");
		$n = 0;
		foreach($matches[0] as $match)
			if (preg_match_all('/{\$(_\("(.|\n)*?"\))}/', $match, $string_matches))
				foreach($string_matches[1] as $string) {
					fwrite($out, "$string;\n");
					$n++;
				}
		if ($n == 0)
			fwrite($out, "// (heredocs found, but no gettext strings)\n");
	}
}

?>
