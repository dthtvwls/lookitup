<?php
if (isset($_GET['symbol'])) {
  require __DIR__ . '/vendor/autoload.php';
  $redis = isset($_ENV['REDIS_URL']) ? new Predis\Client($_ENV['REDIS_URL']) : new Predis\Client;
  if (!($data = $redis->get($_GET['symbol']))) {
    $data = json_decode(explode("\n", file_get_contents('http://www.google.com/async/finance_chart_data?async=x:MUTF,p:40Y,i:86400,q:' . $_GET['symbol']))[1], true)['tnv']['value'];
    $redis->set($_GET['symbol'], $data);
    $redis->expire($_GET['symbol'], 86400);
  }
  die($data);
}
?><!doctype html>
<html>
  <head>
    <title>Fund Performance</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/normalize/4.0.0/normalize.min.css" rel="stylesheet">
    <style>html, body, #chart { height: 100%; }</style>
  </head>
  <body>
    <div id="chart"></div>
    <script src="http://coffeescript.org/extras/coffee-script.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.6.1/lodash.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.2.3/highcharts.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.2.3/themes/grid.js"></script>
    <script type="text/coffeescript">
      $('#chart').highcharts
        credits: enabled: false
        title: null
        tooltip: valueDecimals: 2
        xAxis: type: 'datetime'
        yAxis: title: null
        chart:
          zoomType: 'xy'
          events:
            load: ->
              funds =
                VFIDX: 'Interm Term Investment Grade Bond Adm'
                VTAPX: 'Short Term Inflation Protection Securites Index Adml'
                VFSUX: 'Short Term Investment Grade Adml'
                VBTLX: 'Total Bond Market Index Adml'
                VTABX: 'Total International Bond Index Adml'
                VFIAX: '500 Index Adml'
                VBIAX: 'Balanced Index Adml'
                VTMGX: 'Developed Markets Index Adm'
                VEMAX: 'Emerging Markets Stock Index Adml'
                VEXRX: 'Explorer Adm'
                VSCGX: 'Lifestrategy Conservative Growth Inv'
                VASGX: 'Lifestrategy Growth Inv'
                VASIX: 'Lifestrategy Income Inv'
                VSMGX: 'Lifestrategy Moderate Growth Inv'
                VIMAX: 'Mid Cap Index Fund Adml'
                VMRAX: 'Morgan Growth Adm'
                VSMAX: 'Small Cap Index Adml'
                VTIAX: 'Total International Stock Index Adml'
                VTSAX: 'Total Stock Market Index Adml'
                VWNFX: 'Windsor Ii Inv'

              chart = @

              Object.keys(funds).forEach (symbol) -> $.getJSON '?symbol=' + symbol, (json) ->
                chart.addSeries name: funds[json['n'][0]], data: _.zip json['t'].map((t) -> Date.parse t), json['v'][0]
    </script>
  </body>
</html>
