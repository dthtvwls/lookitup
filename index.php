<?php
if (isset($_GET['async'])) {
  $url = 'http://www.google.com/async/finance_chart_data?async=' . $_GET['async'];
  die(json_decode(explode("\n", file_get_contents($url))[1], true)['tnv']['value']);
}
?>
<!doctype html>
<html>
  <head>
    <title>Index Fund Performance</title>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.1/normalize.min.css">
    <style>html, body, #chart { height: 100%; }</style>
  </head>
  <body>
    <div id="chart"></div>
    <script src="http://coffeescript.org/extras/coffee-script.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/lodash.underscore.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highstock/2.0.4/highstock.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.0.4/themes/grid.js"></script>
    <script type="text/coffeescript">
    $('#chart').highcharts 'StockChart',
      chart:
        zoomType: 'xy'
        events:
          load: ->
            chart = this

            ['VTI', 'VEA', 'VWO', 'VIG', 'VNQ', 'LQD', 'EMB'].forEach (symbol)->
              $.getJSON '?async=x:NYSEARCA,p:40Y,i:86400,q:' + symbol, (json)->
                chart.addSeries
                  name: json['n'][0]
                  data: _.zip _.map(json['t'], (t)-> Date.parse t), json['v'][0]
    </script>
  </body>
</html>
