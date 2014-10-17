<?php
header('Content-Type: text/html; charset=utf-8');
if (isset($_GET['async'])) {
  $raw = file_get_contents("http://www.google.com/async/finance_chart_data?async={$_GET['async']}");
  echo json_decode(explode("\n", $raw)[1], true)['tnv']['value'];
  exit;
}
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Index Fund Performance</title>
  </head>
  <body>
    <div id="chart"></div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.0.3/highcharts.js"></script>
    <script>
    $(function () {
      $('#chart').highcharts({
        plotOptions: { series: { marker: { enabled: false }}},
        title: { text: 'Index Fund Performance' },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: '' } },
        chart: {
          zoomType: 'xy',
          events: {
            load: function () {
              var chart = this, cb = function (body, status, xhr) {
                var json = JSON.parse(body), data = [];
                for (var i = 0; i < json['t'].length; i++) {
                  data.push([Date.parse(json['t'][i]), json['v'][0][i]]);
                }
                chart.addSeries({ name: json['n'][0], data: data });
              };
              ['VTI', 'VEA', 'VWO', 'VIG', 'VNQ', 'LQD', 'EMB'].forEach(function (symbol) {
                $.get('?async=q:' + symbol + ',x:NYSEARCA,p:40Y,i:86400', cb);
              });
            }
          }
        }
      });
    });
    </script>
  </body>
</html>
