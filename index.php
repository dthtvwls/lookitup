<?php
if (isset($_GET['q'])) {
  echo json_encode(json_decode(file_get_contents("https://www.google.com/finance/match?q=${_GET['q']}"))->matches);

} else if (isset($_SERVER['QUERY_STRING']) && preg_match('/^[A-Z]{1,5}$/', $_SERVER['QUERY_STRING'])) {
  require __DIR__ . '/vendor/autoload.php';

  date_default_timezone_set('UTC');

  $client = new Predis\Client(array_key_exists('REDIS_URL', $_ENV) ? $_ENV['REDIS_URL'] : null);
  $symbol = $_SERVER['QUERY_STRING'];
  $data = $client->get($symbol);

  if ($data === null) {
    $i = 604800;
    $file = file_get_contents("https://www.google.com/finance/getprices?i=$i&p=40Y&f=d,c&q=${_SERVER['QUERY_STRING']}");
    $output = [];

    foreach (explode("\n", $file) as $line) {
      if ($line[0] === 'a') {
        $pair = explode(',', $line);
        $anchor = ltrim($pair[0], 'a');
        array_push($output, [$anchor * 1000, intval($pair[1])]);
      } else if (is_numeric($line[0])) {
        $pair = explode(',', $line);
        array_push($output, [($anchor + $pair[0] * $i) * 1000, intval($pair[1])]);
      }
    }

    $data = gzencode(json_encode($output), 9);
    $client->set($symbol, $data);
    $client->expire($symbol, 86400);
  }

  header('Content-Encoding: gzip');
  echo $data;

} else {
  echo file_get_contents('index.html');
}
