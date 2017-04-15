<?php
date_default_timezone_set('UTC');

$symbol = $_SERVER['QUERY_STRING'];

function matches($q) {
  return json_decode(file_get_contents("https://www.google.com/finance/match?q=$q"))->matches;
}

if (isset($_GET['q'])) {
  echo json_encode(matches($_GET['q']));

} else if (preg_match('/^[A-Z]{1,5}$/', $symbol)) {
  require __DIR__ . '/vendor/autoload.php';

  $client = new Predis\Client($_ENV['REDIS_URL']);

  $data = $client->get($symbol);

  if ($data === null) {
    $matches = matches($symbol);

    if (count($matches) > 0) {
      $data = json_decode(json_decode(explode("\n", file_get_contents(
        "https://www.google.com/async/finance_chart_data?async=x:{$matches[0]->e},p:40Y,i:86400,q:$symbol"
      ))[1])->tnv->value);

      // array_map with null first argument works like array zip in more normal languages
      $data = gzencode(json_encode(array_map(null,
        // Highcharts doesn't understand the time unless we give it unix timestamp with milliseconds
        array_map(function ($t) { return strtotime($t) * 1000; }, $data->t),
        $data->v[0]
      )), 9);

      $client->set($symbol, $data);
      $client->expire($symbol, 86400);
    }
  }

  header('Content-Encoding: gzip');
  echo $data;

} else {
  echo file_get_contents('index.html');
}
