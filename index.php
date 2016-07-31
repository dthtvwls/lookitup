<?php
$symbol = $_SERVER['QUERY_STRING'];

if (preg_match('/^[A-Z]{1,5}$/', $symbol)) {
  require __DIR__ . '/vendor/autoload.php';

  $client = new Predis\Client($_ENV['REDIS_URL']);
  $data = $client->get($symbol);

  if ($data === null) {
    $matches = json_decode(file_get_contents("http://www.google.com/finance/match?q=$symbol"))->matches;
    if (count($matches)) {
      $data = json_decode(json_decode(explode("\n", file_get_contents(
        "http://www.google.com/async/finance_chart_data?async=x:{$matches[0]->e},p:40Y,i:86400,q:$symbol"
      ))[1])->tnv->value);

      // array_map with null first argument works like array zip in more normal languages
      $data = gzencode(json_encode(array_map(null,
        // Highcharts doesn't understand the time unless we give it unix timestamp with milliseconds
        array_map(function ($t) { return @strtotime($t) * 1000; }, $data->t),
        $data->v[0]
      )), 9);

      $client->set($symbol, $data);
      $client->expire($symbol, 86400);
    }
  }

  header('Content-Encoding: gzip');
  die($data);
}
?><!doctype html>
<html>
  <head>
    <title>data</title>
    <style>
      html, body { height: 100%; }
      body { margin: 0; }
    </style>
  </head>
  <body>
    <script src="//code.highcharts.com/4.2/highcharts.js"></script>
    <script src="//code.highcharts.com/4.2/themes/grid.js"></script>
    <script>
      new Highcharts.Chart({
        credits: { enabled: false },
        title: null,
        tooltip: { valueDecimals: 2 },
        xAxis: { type: 'datetime' },
        yAxis: { title: null },
        chart: {
          renderTo: document.body,
          zoomType: 'xy',
          events: {
            load: function () {
              location.search.slice(1).split(',').forEach(symbol => {
                const xhr = new XMLHttpRequest()
                xhr.addEventListener('load', () => this.addSeries({ name: symbol, data: JSON.parse(xhr.responseText) }))
                xhr.open('GET', '/?' + symbol)
                xhr.send()
              })
            }
          }
        }
      })
    </script>
  </body>
</html>
