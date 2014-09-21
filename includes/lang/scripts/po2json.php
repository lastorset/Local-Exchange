<?php
/**
 * Convert a PO file into an AMD module for loading by JED.
 *
 * Usage: php includes/lang/scripts/po2json.php <infile.po> > <outfile.js>
 */

# TODO: Actually employ Composer for this project instead of this dirty workaround
require_once 'lib/php-po2json/vendor/autoload.php';
require_once 'lib/php-po2json/Po2Json.php';

$outfile = fopen('php://stdout', 'w');

$result = \neam\po2json\Po2Json::toJSON($argv[1], null, "jed");

fwrite($outfile, "define(". $result .");");
fclose($outfile);
