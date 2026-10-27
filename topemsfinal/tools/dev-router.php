<?php
// Use with: php -S localhost:8080 tools/dev-router.php
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
if (preg_match('#(?:^|/)(?:includes|database|tools|tests)(?:/|$)|(?:^|/)\.|\.(?:sql|md|txt|ini|log)$#i',$path)) {http_response_code(404);exit;}
if ($path==='/') {require dirname(__DIR__).'/login.php';return true;}
return false;
