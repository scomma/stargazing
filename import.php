#!/usr/bin/env php
<?php
$conn = pg_connect("dbname=stargazing");

function sanitize($raw) {
  return "'" . pg_escape_string(trim(preg_replace("/ +/", ' ', $raw))) . "'";
}

$input_file = file('SAC_DeepSky_Ver80_Fence.TXT', FILE_SKIP_EMPTY_LINES);
array_shift($input_file);
foreach ($input_file as $input_line) {
  $input          = explode('|', $input_line);
  $r              = array();
  $r['name']      = sanitize($input[1]);
  $r['name_alt']  = sanitize($input[2]);
  $r['type']      = sanitize($input[3]);
  $r['con']       = sanitize($input[4]);
  $r['ra']        = (int)substr($input[5], 0, 2) * 15 +
                    (float)substr($input[5], 3) / 60;
  $r['dec']       = (int)substr($input[6], 0, 3) +
                    (float)substr($input[6], 4) / 60;
  $r['mag']       = (float)$input[7];
  $r['subr']      = (float)$input[8];
  $r['size_max']  = (trim($input[11]) == '') ? 'NULL' :
                    (float)substr($input[11], 0, -1) /
                    ((substr($input[11], -1) == 's') ? 60 : 1);
  $r['size_min']  = (trim($input[12]) == '') ? 'NULL' :
                    (float)substr($input[12], 0, -1) /
                    ((substr($input[12], -1) == 's') ? 60 : 1);
  $r['pa']        = (trim($input[13]) == '') ? 'NULL' :
                    (int)$input[13];
  $r['class']     = (trim($input[14]) == '') ? 'NULL' :
                    sanitize($input[14]);
  $r['ngc_descr'] = (trim($input[18]) == '') ? 'NULL' :
                    sanitize($input[18]);
  $r['notes']     = (trim($input[19]) == '') ? 'NULL' :
                    sanitize($input[19]);
  $r['geom']      = "'POINT({$r['ra']} {$r['dec']})'";

  $keys = array_keys($r);
  foreach ($keys as $i => $k) $keys[$i] = "\"$k\"";
  $cols = implode(', ', $keys);
  $values = implode(', ', array_values($r));
  pg_exec($conn, "INSERT INTO sky ($cols) VALUES ($values);");
  print $r['name']."\n";
}
?>
