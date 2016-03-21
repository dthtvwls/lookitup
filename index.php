<?php
if (isset($_GET['symbol'])) {
  $url = 'http://www.google.com/async/finance_chart_data?async=x:MUTF,p:40Y,i:86400,q:' . $_GET['symbol'];
  die(json_decode(explode("\n", file_get_contents($url))[1], true)['tnv']['value']);
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
              chart = @

              ['VEXRX'].forEach (symbol) ->
                $.getJSON '?symbol=' + symbol, (json) ->
                  chart.addSeries
                    name: json['n'][0]
                    data: _.zip json['t'].map((t) -> Date.parse t), json['v'][0]
    </script>
  </body>
</html>
