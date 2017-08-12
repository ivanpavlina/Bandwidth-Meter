<html lang="en">
    <head>
        <title>Bandwidth meter</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
    </head>
    <body style="padding-top: 64px; padding-bottom: 30px;">
        <div id="chartDownloadContainer" style="width: 80%; height:400px;" >Loading data...</div>
        <hr>
        <div id="chartUploadContainer" style="width: 80%; height:400px;">Loading data...</div>
        <br>
    </body>
</html>

<?php require('conf.php'); ?>

<script type="text/javascript">
var chartDownload;
var chartUpload;
var firstRunComplete = false;
var interval = <?php echo INTERVAL;?>; 
    
function debugMessage(text) {
    if (<?php echo DEBUG;?> == 1) {
        console.log(text);
    }
}

function getCurrentTime() {
      var d = new Date(),
      h = (d.getHours()<10?'0':'') + d.getHours(),
      m = (d.getMinutes()<10?'0':'') + d.getMinutes();
      s = (d.getSeconds()<10?'0':'') + d.getSeconds();
      return h + ':' + m + ':' + s;
    }

$(document).ready(function(){
    setupChart();
    getBandwidthData();
    setInterval(getBandwidthData, interval);
});


function setupChart(){
    $.post('ajax.php', {
        cmd: 'get_hosts'
    }, function (hosts_data, status) {
        if (status == "success") {
            var hosts = JSON.parse(hosts_data);

            var datad = [];
            var datau = [];
            for (var k in hosts) {
                if (hosts.hasOwnProperty(k)) {
                    // Exclude AP devices
                    if (JSON.stringify(hosts[k]).indexOf(" AP") > -1){
                        continue;
                    }
                    debugMessage("Adding host: " + hosts[k]);
                    datad.push({name: k,
                               type: 'spline',
                               showInLegend: true,
                               legendText: hosts[k][0],
                               dataPoints: []
                    });
                    datau.push({name: k,
                               type: 'spline',
                               showInLegend: true,
                               legendText: hosts[k][0],
                               dataPoints: []
                    });

                }
            }

            chartDownload = new CanvasJS.Chart("chartDownloadContainer", {
                theme: "theme2",
                animationEnabled: true,
                title: {
                    text: "Download x.x.x.x -> 192.168.xx.xx"
                },
                axisX: {
                    title: "Time"
                },
                axisY: {
                    title: "Bandwidth"
                },
                legend: {
                    cursor: "pointer",
                    itemclick: function (e) {
                        if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                            e.dataSeries.visible = false;
                        } else {
                            e.dataSeries.visible = true;
                        }
                        chartDownload.render();
                    }
                },
                data: datad
            });

            chartUpload = new CanvasJS.Chart("chartUploadContainer", {
                theme: "theme2",
                animationEnabled: true,
                title: {
                    text: "Upload 192.168.xx.xx -> x.x.x.x"
                },
                axisX: {
                    title: "Time"
                },
                axisY: {
                    title: "Bandwidth"
                },
                legend: {
                    cursor: "pointer",
                    itemclick: function (e) {
                        if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                            e.dataSeries.visible = false;
                        } else {
                            e.dataSeries.visible = true;
                        }
                        chartUpload.render();
                    }
                },
                data: datau
            });

            chartDownload.render();
            chartUpload.render();
        }
    });
}

function getBandwidthData(){
    if (chartDownload == null || chartUpload == null) {
        debugMessage("Chart undefined, skipping bandwidth check!");
        return;
    }

    $.post('ajax.php', {
        cmd: 'get_bandwidth_data'
    }, function (bandwidth_data, status) {
        if (status === "success") {
            // Discard first result, prevents spikes at beginning
            if (firstRunComplete === false) {
                firstRunComplete = true;
                return;
            }

            var data = JSON.parse(bandwidth_data);
            ctime = getCurrentTime();
            for (var k in chartDownload.options.data) {
                var name = chartDownload.options.data[k].name;

                if (data.hasOwnProperty(name)) {
                    if (data[name].hasOwnProperty('download')) {
                        chartDownload.options.data[k].dataPoints.push({label: ctime, y: (data[name].download / (interval/1000))});
                    } else {
                        chartDownload.options.data[k].dataPoints.push({label: ctime, y: 0});
                    }
                    if (data[name].hasOwnProperty('upload')) {
                        chartUpload.options.data[k].dataPoints.push({label: ctime, y: (data[name].upload / (interval/1000))});
                    } else {
                        chartUpload.options.data[k].dataPoints.push({label: ctime, y: 0});
                    }
                } else {
                    chartDownload.options.data[k].dataPoints.push({label: ctime, y: 0});
                    chartUpload.options.data[k].dataPoints.push({label: ctime, y: 0});
                }
            }

            chartDownload.render();
            chartUpload.render();
        }
    });
}
</script>

