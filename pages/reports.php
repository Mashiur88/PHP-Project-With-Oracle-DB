<?php include 'header.php';?>
<body class="g-sidenav-show  bg-gray-100">
  <?php include 'sidebar.php';?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <?php include 'navbar.php';?> 
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <?php isset($_REQUEST['channel_list']) ? $filter = trim($_REQUEST['channel_list'],32) : $filter = '';?>
    <form method="post" enctype="multipart/form-data">
      <div class="form-group">
          <label for="channelList" class="h5 m-3">Channels:</label>
          <select name="channel_list" class="from-control" style="min-width: 200px" id="channelList">
            <option value="" <?php echo ($filter == '') ? 'selected' : '';?>>select a channel</option>
            <option value="ALL CHANNEL HIT" <?php echo ($filter == 'ALL CHANNEL HIT') ? 'selected' : '';?>>All Channel Hit</option>
            <option value="BKASH CHANNEL HIT" <?php echo ($filter == 'BKASH CHANNEL HIT') ? 'selected' : '';?>>Bkash Channel Hit</option>
            <option value="RETAIL CHANNEL HIT" <?php echo ($filter == 'RETAIL CHANNEL HIT') ? 'selected' : '';?>>Retail Channel Hit</option>
          </select>
      </div> 
    </form>
    <div class="row pt-3 pb-3">
        <div id="piChart" class="col-md-6">
            
        </div>
        <div id="donutChart" class="col-md-6">
            
        </div>
    </div>
    <div class="row pt-3 pb-3">
        <div id="3dBubbleChart" class="col-md-6">
            
        </div>
        <div id="groupStacked" class="col-md-6">
            
        </div>
    </div>
    <div class="row pt-3 pb-3">
        <div id="columnLinePieChart" class="col-md-6">
            
        </div>
        <div id="" class="col-md-6">
            
        </div>
    </div>
    <?php include 'footer.php';?>
    </div>
    <?php 
    include '../db/DB.php';
    $sql = "SELECT * FROM MY_DB.Reports";
    if(isset($_REQUEST["channel_list"]) && $_REQUEST["channel_list"] != ''){
      $channel = $_REQUEST["channel_list"];
      echo $channel . "<br>";
      $sql = $sql . " where item ='" . $channel . "'"; 
    } 
    $sql1 = "SELECT * FROM MY_DB.Reports";
    echo $sql;
    $stid = oci_parse($conn, $sql);
    $stid1 = oci_parse($conn, $sql1);
    oci_execute($stid);
    $rows = oci_fetch_all($stid, $reports, null, null, OCI_FETCHSTATEMENT_BY_ROW); 
    $rows1 = oci_fetch_all($stid, $reports1, null, null, OCI_FETCHSTATEMENT_BY_ROW);
    $unique = [];
    $item = [];
    $channel = [];
    $deno = [];
    foreach($reports as $report){
      array_push($item,$report['VALIDITY']);
      array_push($channel,$report['ITEM']);
      array_push($deno,$report['DEMO']);
    }
    $unique = array_unique($item);
    $uniqueChannel = array_unique($channel);
    $uniqueDeno = array_unique($deno);

    // echo $rows;
  ?>
  </main>
  <!-- <?php //include 'plugins.php';?> -->

  <script>
  var item = [];
  var itemD = [];
  var percent = [];
  var Rdata = new Array();
  var GSdata = new Array();
  const gdata = <?php echo json_encode($reports); ?>;
  const it = <?php echo json_encode($unique); ?>;
  const channels = <?php echo json_encode($uniqueChannel); ?>;
  const denos = <?php echo json_encode($uniqueDeno); ?>;

  var udenos = [];
  for(var i in denos)
  {
    udenos.push(denos[i]);
  }  

  var ichannels = [];
  for(var i in channels)
  {
    ichannels.push(channels[i]);
  }  

  for(var i in it)
  {
    var x = it[i] + "ays";
    item.push(it[i]);
    itemD.push(x);
  }  

  //pie chart
  for(var i in item)
  {
    var cnt = 0;
    for(var j in gdata)
    if(item[i] == gdata[j]['VALIDITY']){
      cnt++;
    }
    percentage(cnt);
  }
  
  function percentage(cnt)
  {
    var length = gdata.length;
    temp = (cnt/length)*100;
    percent.push(temp);
  }

  for(var i in item)
  {
    // Rdata[i] = {};
    // Rdata[i].name = item[i];
    // Rdata[i].y = percent[i];
    Rdata.push({name: itemD[i], y: parseFloat(percent[i].toFixed(2))});
  }  
  //end pie
  // console.log(udenos);

  //columnstack chart
    for(var i in udenos){
      var values = [];
      var stack ="";
      for(var j in gdata){
        if(udenos[i] == gdata[j]['DEMO']){
          values.push(parseInt(gdata[j]['MTD']));
          stack = gdata[j]['VALIDITY'];
        }
      }  
      GSdata.push({name: udenos[i], data: values, stack: stack});
    }


    //bubble chart
    function hundredConverter(array){
        function arrayMax(arr) {
          return arr.reduce(function (p, v) {
            p = parseInt(p);
            v = parseInt(v);
            return ( p > v ? p : v );
          });
        }
        var max = arrayMax(array);
        // console.log(max);
        var temp = [];
        for(var i in array){
          temp.push((array[i]/max)*100);
        }
        return temp;
    }

    var bubbledata =[];

    for(var i in item){
      var bubblex = [];
      var bubbley = [];
      var size = [];
      for(var j in gdata){
        if(item[i] == gdata[j]['VALIDITY']){
          bubblex.push(gdata[j]['LMTD']);
          bubbley.push(gdata[j]['MTD']);
          size.push(gdata[j]['MTD_DELTA']);
        }
      }
      const bubblexaxis = hundredConverter(bubblex);
      const bubbleyaxis = hundredConverter(bubbley);
      const sizeaxis = hundredConverter(size);
      bubbledata.push(seriesData(bubblexaxis,bubbleyaxis,sizeaxis));
    }

    function seriesData(bubblexaxis,bubbleyaxis,sizeaxis){
      var result = [];
      for(var j=0;j<bubblexaxis.length;j++){
        var axis = [];
        // console.log(sizeaxis[j]);
        axis.push(Math.round(bubblexaxis[j]*100)/100);
        axis.push(Math.round(bubbleyaxis[j]*100)/100);
        axis.push(Math.round(sizeaxis[j]*100)/100);
        result.push(axis);
      }
      return result;
    }
    //end bubble

    //column pie chart
    var mlData = [];
    var avg = [];
    var indexfound = [];
    for(var i in udenos){
      var type = "column";
      var value = [];
      var noofindex = 0;
      for(var j in gdata){
        if(udenos[i] == gdata[j]['DEMO']){
          value.push(parseFloat(gdata[j]['MTD_PERCENTAGE']));
          noofindex++;
        }
      }
      indexfound.push(noofindex);
      avg.push(value);
      mlData.push({type: type,name: udenos[i],data: value});
    }
    var maxIndex = Math.max(...indexfound);
    console.log(maxIndex);
    function average(x){
      var aveg;
      var sum = 0;
        for(var i in x){
          x[i] = parseInt(x[i]);
          sum += x[i];
        }
        // console.log(sum);
        aveg = sum/x.length;
        return aveg;
    }
    // console.log(avg);
    var diff = [];
    for(var i=0;i<maxIndex;i++){
      var temp = [];
      for(var j in avg){
        for(var k in avg[j]){
        var c;
          if(k == i){
          if(avg[j][k] == null){
            c = 0;
          }
          else{ c = avg[j][k];}
          temp.push(c);
          }
        }
      }
      console.log(temp);
      var y = parseFloat(average(temp));
      diff.push(Math.round(y*100)/100);
    }
    // console.log(diff);
    mlData.push({ type: 'spline',name: 'Average',data: diff,marker: { lineWidth: 2,lineColor: Highcharts.getOptions().colors[3],
      fillColor: 'white'}});

    var column = []; 
    for(var i in udenos)
    {
      var total = 0;
      for(var j in gdata)
      if(udenos[i] == gdata[j]['DEMO']){
        total+= parseFloat(gdata[j]['MTD_PERCENTAGE']);
      }
      column.push({name: udenos[i],y: total,color: Highcharts.getOptions().colors[i],
      dataLabels: {
        enabled: true,
        distance: -50,
        format: '{point.total} M',
        style: {
          fontSize: '15px'
        }
      }});
    }
    
    mlData.push({type: 'pie', name: 'Total',data: column,center: [75, 65],size: 100,innerSize: '70%',showInLegend: false,dataLabels: {enabled: false}});
    console.log(mlData);
    //end column pir

    var colors = Highcharts.getOptions().colors,
    categories = [
        'Chrome',
        'Safari',
        'Edge',
        'Firefox',
        'Other'
    ],
    data = [
      {
        y: 61.04,
        color: colors[2],
        drilldown: {
            name: 'Chrome',
            categories: [
            'Chrome v97.0',
            'Chrome v96.0',
            'Chrome v95.0',
            'Chrome v94.0',
            'Chrome v93.0',
            'Chrome v92.0',
            'Chrome v91.0',
            'Chrome v90.0',
            'Chrome v89.0',
            'Chrome v88.0',
            'Chrome v87.0',
            'Chrome v86.0',
            'Chrome v85.0',
            'Chrome v84.0',
            'Chrome v83.0',
            'Chrome v81.0',
            'Chrome v89.0',
            'Chrome v79.0',
            'Chrome v78.0',
            'Chrome v76.0',
            'Chrome v75.0',
            'Chrome v72.0',
            'Chrome v70.0',
            'Chrome v69.0',
            'Chrome v56.0',
            'Chrome v49.0'
            ],
            data: [
            36.89,
            18.16,
            0.54,
            0.7,
            0.8,
            0.41,
            0.31,
            0.13,
            0.14,
            0.1,
            0.35,
            0.17,
            0.18,
            0.17,
            0.21,
            0.1,
            0.16,
            0.43,
            0.11,
            0.16,
            0.15,
            0.14,
            0.11,
            0.13,
            0.12
            ]
        }
      },
      {
        y: 9.47,
        color: colors[3],
        drilldown: {
            name: 'Safari',
            categories: [
            'Safari v15.3',
            'Safari v15.2',
            'Safari v15.1',
            'Safari v15.0',
            'Safari v14.1',
            'Safari v14.0',
            'Safari v13.1',
            'Safari v13.0',
            'Safari v12.1'
            ],
            data: [
            0.1,
            2.01,
            2.29,
            0.49,
            2.48,
            0.64,
            1.17,
            0.13,
            0.16
            ]
        }
      },
      {
        y: 9.32,
        color: colors[5],
        drilldown: {
            name: 'Edge',
            categories: [
            'Edge v97',
            'Edge v96',
            'Edge v95'
            ],
            data: [
            6.62,
            2.55,
            0.15
            ]
        }
      },
      {
        y: 8.15,
        color: colors[1],
        drilldown: {
            name: 'Firefox',
            categories: [
            'Firefox v96.0',
            'Firefox v95.0',
            'Firefox v94.0',
            'Firefox v91.0',
            'Firefox v78.0',
            'Firefox v52.0'
            ],
            data: [
            4.17,
            3.33,
            0.11,
            0.23,
            0.16,
            0.15
            ]
        }
      },
      {
        y: 11.02,
        color: colors[6],
        drilldown: {
            name: 'Other',
            categories: [
            'Other'
            ],
            data: [
            11.02
            ]
        }
      }
    ],
    browserData = [],
    versionsData = [],
    i,
    j,
    dataLen = data.length,
    drillDataLen,
    brightness;


    // Build the data arrays
    for (i = 0; i < dataLen; i += 1) {
    // add browser data
    browserData.push({
        name: categories[i],
        y: data[i].y,
        color: data[i].color
    });

    // add version data
    drillDataLen = data[i].drilldown.data.length;
    for (j = 0; j < drillDataLen; j += 1) {
        brightness = 0.2 - (j / drillDataLen) / 5;
        versionsData.push({
        name: data[i].drilldown.categories[j],
        y: data[i].drilldown.data[j],
        color: Highcharts.color(data[i].color).brighten(brightness).get()
        });
    }
  }
  
  

Highcharts.chart('piChart', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'Day wise items',
        align: 'left'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
        valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
            enabled: true,
            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
        }
        }
    },
    series: [
    {
        name: 'Items',
        colorByPoint: true,
        data: Rdata
    }
  ]
});

// Create the chart
Highcharts.chart('donutChart', {
  chart: {
    type: 'pie'
  },
  title: {
    text: 'Browser market share, January, 2022',
    align: 'left'
  },
  subtitle: {
    text: 'Source: <a href="http://statcounter.com" target="_blank">statcounter.com</a>',
    align: 'left'
  },
  plotOptions: {
    pie: {
      shadow: false,
      center: ['50%', '50%']
    }
  },
  tooltip: {
    valueSuffix: '%'
  },
  series: [{
    name: 'Browsers',
    data: browserData,
    size: '60%',
    dataLabels: {
      formatter: function () {
        return this.y > 5 ? this.point.name : null;
      },
      color: '#ffffff',
      distance: -30
    }
  }, {
    name: 'Versions',
    data: versionsData,
    size: '80%',
    innerSize: '60%',
    dataLabels: {
      formatter: function () {
        // display only if larger than 1
        return this.y > 1 ? '<b>' + this.point.name + ':</b> ' +
          this.y + '%' : null;
      }
    },
    id: 'versions'
  }],
  responsive: {
    rules: [{
      condition: {
        maxWidth: 400
      },
      chartOptions: {
        series: [{
        }, {
          id: 'versions',
          dataLabels: {
            enabled: false
          }
        }]
      }
    }]
  }
});

Highcharts.chart('3dBubbleChart', {
    chart: {
    type: 'bubble',
    plotBorderWidth: 1,
    zoomType: 'xy'
    },
    title: {
    text: 'LMTD and MTD wise MTD DELTA',
    align: 'left'
    },
    xAxis: {
    gridLineWidth: 1,
    accessibility: {
        rangeDescription: 'Range: 0 to 100.'
    }
    },
    yAxis: {
    startOnTick: false,
    endOnTick: false,
    accessibility: {
        rangeDescription: 'Range: 0 to 100.'
    }
    },
    series: [{
    data: bubbledata[0],
    // [
    //     [44, 81, 63],
    //     [98, 5, 0],
    //     [51, 50, 0],
    //     [41, 22, 14],
    //     [58, 24, 20],
    //     [78, 37, 34],
    //     [55, 56, 53],
    //     [18, 45, 0],
    //     [42, 44, 28],
    //     [3, 52, 59],
    //     [31, 18, 0],
    //     [79, 91, 63],
    //     [93, 23, 23],
    //     [44, 83, 22]
    // ],
    marker: {
        fillColor: {
        radialGradient: { cx: 0.4, cy: 0.3, r: 0.7 },
        stops: [
            [0, 'rgba(255,255,255,0.5)'],
            [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0.5).get('rgba')]
        ]
        }
    }
    }, {
    data: bubbledata[1],
    // [
    //     [42, 38, 20],
    //     [6, 18, 1],
    //     [1, 93, 55],
    //     [57, 2, 90],
    //     [99, 99, 402],
    //     [11, 74, 96],
    //     [88, 56, 10],
    //     [30, 47, 49],
    //     [57, 62, 98],
    //     [4, 16, 16],
    //     [46, 10, 11],
    //     [22, 87, 89],
    //     [57, 91, 82],
    //     [45, 15, 98]
    // ],
    marker: {
        fillColor: {
        radialGradient: { cx: 0.4, cy: 0.3, r: 0.7 },
        stops: [
            [0, 'rgba(255,255,255,0.5)'],
            [1, Highcharts.color(Highcharts.getOptions().colors[1]).setOpacity(0.5).get('rgba')]
        ]
        }
    }
    }, {
    data: bubbledata[2],
    // [
    //     [42, 38, 20],
    //     [6, 18, 1],
    //     [1, 93, 55],
    //     [57, 2, 90],
    //     [99, 99, 402],
    //     [11, 74, 96],
    //     [88, 56, 10],
    //     [30, 47, 49],
    //     [57, 62, 98],
    //     [4, 16, 16],
    //     [46, 10, 11],
    //     [22, 87, 89],
    //     [57, 91, 82],
    //     [45, 15, 98]
    // ],
    marker: {
        fillColor: {
        radialGradient: { cx: 0.4, cy: 0.3, r: 0.7 },
        stops: [
            [0, 'rgba(255,255,255,0.5)'],
            [1, Highcharts.color(Highcharts.getOptions().colors[2]).setOpacity(0.5).get('rgba')]
        ]
        }
    }
    }]

});


Highcharts.chart('groupStacked', {
    chart: {
      type: 'column'
    },
    title: {
      text: 'MTD data grouped by channel and validity',
      align: 'left'
    },
    xAxis: {
      categories: ichannels
    },
    yAxis: {
      allowDecimals: false,
      min: 0,
      title: {
        text: 'Counts'
      }
    },
    tooltip: {
      formatter: function () {
        return '<b>' + this.x + '</b><br/>' +
          this.series.name + ': ' + this.y + '<br/>' +
          'Validity: ' + this.series.userOptions.stack + '<br/>' +
          'Total: ' + this.point.stackTotal;
      }
    },
    plotOptions: {
      column: {
        stacking: 'normal'
      }
    },
    series: GSdata
});

// Data retrieved from https://www.ssb.no/energi-og-industri/olje-og-gass/statistikk/sal-av-petroleumsprodukt/artikler/auka-sal-av-petroleumsprodukt-til-vegtrafikk
Highcharts.chart('columnLinePieChart', {
  title: {
    text: 'Validity wise MTD Percentage',
    align: 'left'
  },
  xAxis: {
    categories: ichannels
  },
  yAxis: {
    title: {
      text: 'MTD Percentage'
    }
  },
  tooltip: {
    valueSuffix: 'count'
  },
  series: mlData
  // [{
  //   type: 'column',
  //   name: '2020',
  //   data: [59, 83, 65, 228, 184]
  // }, {
  //   type: 'column',
  //   name: '2021',
  //   data: [24, 79, 72, 240, 167]
  // }, {
  //   type: 'column',
  //   name: '2022',
  //   data: [58, 88, 75, 250, 176]
  // }, {
  //   type: 'spline',
  //   name: 'Average',
  //   data: [47, 83.33, 50.66, 299.33, 175.66],
  //   marker: {
  //     lineWidth: 2,
  //     lineColor: Highcharts.getOptions().colors[3],
  //     fillColor: 'white'
  //   }
  // }, {
  //   type: 'pie',
  //   name: 'Total',
  //   data: [{
  //     name: '2020',
  //     y: 619,
  //     color: Highcharts.getOptions().colors[0], // 2020 color
  //     dataLabels: {
  //       enabled: true,
  //       distance: -50,
  //       format: '{point.total} M',
  //       style: {
  //         fontSize: '15px'
  //       }
  //     }
  //   }, {
  //     name: '2021',
  //     y: 586,
  //     color: Highcharts.getOptions().colors[1] // 2021 color
  //   }, {
  //     name: '2022',
  //     y: 647,
  //     color: Highcharts.getOptions().colors[2] // 2022 color
  //   }],
  //   center: [75, 65],
  //   size: 100,
  //   innerSize: '70%',
  //   showInLegend: false,
  //   dataLabels: {
  //     enabled: false
  //   }
  // }]
});

$(document).on("change","#channelList",function(){
    var value = $(this).val();
    var temp = window.location.href;
    var base = temp.split("?");
    console.log(base);
    var url = base[0] + "?channel_list=" + value;
    // window.location.replace(url);
    window.location = url;
    // $(window).redirectTo(url);
});

  </script>
<?php include 'commonFooter.php';?>
<?php oci_close($conn); ?>