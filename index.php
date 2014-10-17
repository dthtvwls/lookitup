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
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highstock/2.0.4/highstock.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.0.4/themes/grid.js"></script>
    <script>
    $('#chart').highcharts('StockChart', {
      chart: {
        zoomType: 'xy',
        events: {
          load: function () {
            var chart = this;

            ['VTI', 'VEA', 'VWO', 'VIG', 'VNQ', 'LQD', 'EMB'].forEach(function (symbol) {
              $.get('?async=q:' + symbol + ',x:NYSEARCA,p:40Y,i:86400', function (body) {
                var json = JSON.parse(body), data = [];
              
                for (var i = 0; i < json['t'].length; i++) {
                  data.push([Date.parse(json['t'][i]), json['v'][0][i]]);
                }
                chart.addSeries({ name: json['n'][0], data: data });
              });
            });
          }
        }
      }
    });
    </script>
  </body>
</html>
