<!doctype html>
<html>
  <head>
    <title>Fund Performance</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/normalize/4.0.0/normalize.min.css" rel="stylesheet">
    <style>html, body, #chart { height: 100%; }</style>
  </head>
  <body>
    <div id="chart"></div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.6.1/lodash.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.2.3/highcharts.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.2.3/themes/grid.js"></script>
    <script>
      $('#chart').highcharts({
        chart: { zoomType: 'xy' },
        credits: { enabled: false },
        title: null,
        tooltip: { valueDecimals: 2 },
        xAxis: { type: 'datetime' },
        yAxis: { title: null },
        series: [
          <?php
            require __DIR__ . '/vendor/autoload.php';

            $redis = isset($_ENV['REDIS_URL']) ? new Predis\Client($_ENV['REDIS_URL']) : new Predis\Client;

            foreach ([
              'VFIDX' => 'Interm Term Investment Grade Bond Adm',
              'VTAPX' => 'Short Term Inflation Protection Securites Index Adml',
              'VFSUX' => 'Short Term Investment Grade Adml',
              'VBTLX' => 'Total Bond Market Index Adml',
              'VTABX' => 'Total International Bond Index Adml',
              'VFIAX' => '500 Index Adml',
              'VBIAX' => 'Balanced Index Adml',
              'VTMGX' => 'Developed Markets Index Adm',
              'VEMAX' => 'Emerging Markets Stock Index Adml',
              'VEXRX' => 'Explorer Adm',
              'VSCGX' => 'Lifestrategy Conservative Growth Inv',
              'VASGX' => 'Lifestrategy Growth Inv',
              'VASIX' => 'Lifestrategy Income Inv',
              'VSMGX' => 'Lifestrategy Moderate Growth Inv',
              'VIMAX' => 'Mid Cap Index Fund Adml',
              'VMRAX' => 'Morgan Growth Adm',
              'VSMAX' => 'Small Cap Index Adml',
              'VTIAX' => 'Total International Stock Index Adml',
              'VTSAX' => 'Total Stock Market Index Adml',
              'VWNFX' => 'Windsor Ii Inv'
            ] as $symbol => $name) {

              $data = $redis->get($symbol);

              if (!$data) {
                $data = json_decode(json_decode(explode("\n", file_get_contents('http://www.google.com/async/finance_chart_data?async=x:MUTF,p:40Y,i:86400,q:' . $symbol))[1])->tnv->value);
                $data = json_encode(array_map(null, array_map(function ($t) { return strtotime($t); }, $data->t), $data->v[0]));
                $redis->set($symbol, $data);
                $redis->expire($symbol, 86400);
              }

              echo "{ name: '$name', data: $data },";
            }
          ?>
        ]
      });
    </script>
  </body>
</html>
