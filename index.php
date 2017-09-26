<?php
function format_pair($timestamp, $value) {
  return [$timestamp * 1000, floatval($value)];
}

if (isset($_GET['q'])) {
  echo json_encode(json_decode(file_get_contents("https://finance.google.com/finance/match?q=${_GET['q']}"))->matches);

} else if (isset($_SERVER['QUERY_STRING']) && preg_match('/^[A-Z]{1,5}$/', $_SERVER['QUERY_STRING'])) {
  require __DIR__ . '/vendor/autoload.php';

  date_default_timezone_set('UTC');

  $client = new Predis\Client(array_key_exists('REDIS_URL', $_ENV) ? $_ENV['REDIS_URL'] : null);
  $symbol = $_SERVER['QUERY_STRING'];
  $data = $client->get($symbol);

  if ($data === null) {
    $interval = 604800;
    $file = file_get_contents("https://finance.google.com/finance/getprices?i=$interval&p=40Y&f=d,c&q=${_SERVER['QUERY_STRING']}");
    $output = [];

    foreach (explode("\n", $file) as $line) {
      [$day, $close] = explode(',', $line);
      if ($day[0] === 'a') {
        $anchor = intval(ltrim($day, 'a'));
        $output[] = format_pair($anchor, $close);
      } else if (is_numeric($day[0])) {
        $output[] = format_pair($anchor + intval($day) * $interval, $close);
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
