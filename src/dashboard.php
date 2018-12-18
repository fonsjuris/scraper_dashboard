<?php
// Simple password protection
if (!isset($_COOKIE['password']) || $_COOKIE['password'] !== getenv('SITE_PW')) {
    header('Location: login.php');
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<!--    <meta http-equiv="refresh" content="60" >-->
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>


    <script>
        (function(w,d,s,g,js,fs){
            g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
            js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
            js.src='https://apis.google.com/js/platform.js';
            fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
        }(window,document,'script'));
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

    <!-- This demo uses the Chart.js graphing library and Moment.js to do date
         formatting and manipulation. -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>

    <!-- Include the ViewSelector2 component script. -->
    <script src="public/js/view-selector2.js"></script>

    <!-- Include the ActiveUsers component script. -->
    <script src="public/js/active-users.js"></script>

    <!-- Include the CSS that styles the charts. -->
    <link rel="stylesheet" href="public/css/chartjs-visualizations.css">

    <title>Scraper dashboard</title>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div  class="text-center">
                <h1 id="top_header">Scraper dashboard</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                <tr>
                    <th>Process</th>
                    <th>Last run (# items)</th>
                    <th>Total # items</th>
                </tr>
                </thead>
                <tbody>
                <?php
                require_once 'dal.php';
                $scraper_results = get_status_of_all_processes();
                foreach ($scraper_results as $scraper_result) {?>
                    <tr>
                        <td>
                            <?php
                            if ($scraper_result['status_id'] == '1'){
                                echo '<i class="fas fa-circle" style="color:yellow"></i>';
                            } elseif ($scraper_result['status_id'] == '2'){
                                echo '<i class="fas fa-circle" style="color:green"></i>';
                            } elseif ($scraper_result['status_id'] == '3'){
                                echo '<i class="fas fa-circle" style="color:red"></i>';
                                echo $scraper_result['status_text'];
                            }
                            ?>
                            <?php echo $scraper_result['name']; ?> </td>
                        <td> <?php echo $scraper_result['timestamp']; ?> (<?php echo $scraper_result['num_processed_items']; ?>) </td>
                        <td> <?php echo $scraper_result['total_num_items']; ?>&nbsp;<?php echo $scraper_result['item_description']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>
        </div>
    </div>
<hr/>
    <div class="row">
        <div class="col-md-12">
            <div id="embed-api-auth-container"></div>
            <div id="view-selector-container"></div>
            <div id="view-name"></div>
            <div id="active-users-container"></div>

            <div id="week_vs_last_week" class="Chartjs" style="display: none">
                <h3>This Week vs Last Week (by sessions)</h3>
                <figure class="Chartjs-figure" id="chart-1-container"></figure>
                <ol class="Chartjs-legend" id="legend-1-container"></ol>
            </div>
            <div id="year_vs_last_year" class="Chartjs" style="display: none">
                <h3>This Year vs Last Year (by users)</h3>
                <figure class="Chartjs-figure" id="chart-2-container"></figure>
                <ol class="Chartjs-legend" id="legend-2-container"></ol>
            </div>
        </div>
    </div>

</div>

<script>

    gapi.analytics.ready(function() {

        /**
         * Authorize the user immediately if the user has already granted access.
         * If no access has been created, render an authorize button inside the
         * element with the ID "embed-api-auth-container".
         */
        gapi.analytics.auth.authorize({
            container: 'embed-api-auth-container',
            clientid: '317027045061-sjiod9jtmuh2vic99hc5cfnol85u49qe.apps.googleusercontent.com'
        });

        gapi.analytics.auth.on('success', function(response) {
            //hide the auth-buttonMY
            document.querySelector("#embed-api-auth-container").style.display='none';
            document.querySelector("#view-selector-container").style.display='none';
            document.querySelector("#view-name").style.display='none';
            document.querySelector("#year_vs_last_year").style.display='unset';
            document.querySelector("#week_vs_last_week").style.display='unset';
        });


        /**
         * Create a new ActiveUsers instance to be rendered inside of an
         * element with the id "active-users-container" and poll for changes every
         * five seconds.
         */
        var activeUsers = new gapi.analytics.ext.ActiveUsers({
            container: 'active-users-container',
            pollingInterval: 5
        });


        /**
         * Add CSS animation to visually show the when users come and go.
         */
        activeUsers.once('success', function() {
            var element = this.container.firstChild;
            var timeout;

            this.on('change', function(data) {
                var element = this.container.firstChild;
                var animationClass = data.delta > 0 ? 'is-increasing' : 'is-decreasing';
                element.className += (' ' + animationClass);

                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    element.className =
                        element.className.replace(/ is-(increasing|decreasing)/g, '');
                }, 3000);
            });
        });


        /**
         * Create a new ViewSelector2 instance to be rendered inside of an
         * element with the id "view-selector-container".
         */
        var viewSelector = new gapi.analytics.ext.ViewSelector2({
            container: 'view-selector-container',
        })
            .execute();


        /**
         * Update the activeUsers component, the Chartjs charts, and the dashboard
         * title whenever the user changes the view.
         */
        viewSelector.on('viewChange', function(data) {
            var title = document.getElementById('view-name');
            title.textContent = data.property.name + ' (' + data.view.name + ')';

            // Start tracking active users for this view.
            activeUsers.set(data).execute();

            // Render all the of charts for this view.
            renderWeekOverWeekChart(data.ids);
            renderYearOverYearChart(data.ids);
        });


        /**
         * Draw the a chart.js line chart with data from the specified view that
         * overlays session data for the current week over session data for the
         * previous week.
         */
        function renderWeekOverWeekChart(ids) {

            // Adjust `now` to experiment with different days, for testing only...
            var now = moment(); // .subtract(3, 'day');

            var thisWeek = query({
                'ids': ids,
                'dimensions': 'ga:date,ga:nthDay',
                'metrics': 'ga:sessions',
                'start-date': moment(now).subtract(1, 'day').day(0).format('YYYY-MM-DD'),
                'end-date': moment(now).format('YYYY-MM-DD')
            });

            var lastWeek = query({
                'ids': ids,
                'dimensions': 'ga:date,ga:nthDay',
                'metrics': 'ga:sessions',
                'start-date': moment(now).subtract(1, 'day').day(0).subtract(1, 'week')
                    .format('YYYY-MM-DD'),
                'end-date': moment(now).subtract(1, 'day').day(6).subtract(1, 'week')
                    .format('YYYY-MM-DD')
            });

            Promise.all([thisWeek, lastWeek]).then(function(results) {

                var data1 = results[0].rows.map(function(row) { return +row[2]; });
                var data2 = results[1].rows.map(function(row) { return +row[2]; });
                var labels = results[1].rows.map(function(row) { return +row[0]; });

                labels = labels.map(function(label) {
                    return moment(label, 'YYYYMMDD').format('ddd');
                });

                var data = {
                    labels : labels,
                    datasets : [
                        {
                            label: 'Last Week',
                            fillColor : 'rgba(220,220,220,0.5)',
                            strokeColor : 'rgba(220,220,220,1)',
                            pointColor : 'rgba(220,220,220,1)',
                            pointStrokeColor : '#fff',
                            data : data2
                        },
                        {
                            label: 'This Week',
                            fillColor : 'rgba(151,187,205,0.5)',
                            strokeColor : 'rgba(151,187,205,1)',
                            pointColor : 'rgba(151,187,205,1)',
                            pointStrokeColor : '#fff',
                            data : data1
                        }
                    ]
                };

                new Chart(makeCanvas('chart-1-container')).Line(data);
                generateLegend('legend-1-container', data.datasets);
            });
        }


        /**
         * Draw the a chart.js bar chart with data from the specified view that
         * overlays session data for the current year over session data for the
         * previous year, grouped by month.
         */
        function renderYearOverYearChart(ids) {

            // Adjust `now` to experiment with different days, for testing only...
            var now = moment(); // .subtract(3, 'day');

            var thisYear = query({
                'ids': ids,
                'dimensions': 'ga:month,ga:nthMonth',
                'metrics': 'ga:users',
                'start-date': moment(now).date(1).month(0).format('YYYY-MM-DD'),
                'end-date': moment(now).format('YYYY-MM-DD')
            });

            var lastYear = query({
                'ids': ids,
                'dimensions': 'ga:month,ga:nthMonth',
                'metrics': 'ga:users',
                'start-date': moment(now).subtract(1, 'year').date(1).month(0)
                    .format('YYYY-MM-DD'),
                'end-date': moment(now).date(1).month(0).subtract(1, 'day')
                    .format('YYYY-MM-DD')
            });

            Promise.all([thisYear, lastYear]).then(function(results) {
                var data1 = results[0].rows.map(function(row) { return +row[2]; });
                var data2 = results[1].rows.map(function(row) { return +row[2]; });
                var labels = ['Jan','Feb','Mar','Apr','May','Jun',
                    'Jul','Aug','Sep','Oct','Nov','Dec'];

                // Ensure the data arrays are at least as long as the labels array.
                // Chart.js bar charts don't (yet) accept sparse datasets.
                for (var i = 0, len = labels.length; i < len; i++) {
                    if (data1[i] === undefined) data1[i] = null;
                    if (data2[i] === undefined) data2[i] = null;
                }

                var data = {
                    labels : labels,
                    datasets : [
                        {
                            label: 'Last Year',
                            fillColor : 'rgba(220,220,220,0.5)',
                            strokeColor : 'rgba(220,220,220,1)',
                            data : data2
                        },
                        {
                            label: 'This Year',
                            fillColor : 'rgba(151,187,205,0.5)',
                            strokeColor : 'rgba(151,187,205,1)',
                            data : data1
                        }
                    ]
                };

                new Chart(makeCanvas('chart-2-container')).Bar(data);
                generateLegend('legend-2-container', data.datasets);
            })
                .catch(function(err) {
                    console.error(err.stack);
                });
        }

        /**
         * Extend the Embed APIs `gapi.analytics.report.Data` component to
         * return a promise the is fulfilled with the value returned by the API.
         * @param {Object} params The request parameters.
         * @return {Promise} A promise.
         */
        function query(params) {
            return new Promise(function(resolve, reject) {
                var data = new gapi.analytics.report.Data({query: params});
                data.once('success', function(response) { resolve(response); })
                    .once('error', function(response) { reject(response); })
                    .execute();
            });
        }


        /**
         * Create a new canvas inside the specified element. Set it to be the width
         * and height of its container.
         * @param {string} id The id attribute of the element to host the canvas.
         * @return {RenderingContext} The 2D canvas context.
         */
        function makeCanvas(id) {
            var container = document.getElementById(id);
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');

            container.innerHTML = '';
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            container.appendChild(canvas);

            return ctx;
        }


        /**
         * Create a visual legend inside the specified element based off of a
         * Chart.js dataset.
         * @param {string} id The id attribute of the element to host the legend.
         * @param {Array.<Object>} items A list of labels and colors for the legend.
         */
        function generateLegend(id, items) {
            var legend = document.getElementById(id);
            legend.innerHTML = items.map(function(item) {
                var color = item.color || item.fillColor;
                var label = item.label;
                return '<li><i style="background:' + color + '"></i>' +
                    escapeHtml(label) + '</li>';
            }).join('');
        }


        // Set some global Chart.js defaults.
        Chart.defaults.global.animationSteps = 60;
        Chart.defaults.global.animationEasing = 'easeInOutQuart';
        Chart.defaults.global.responsive = true;
        Chart.defaults.global.maintainAspectRatio = false;


        /**
         * Escapes a potentially unsafe HTML string.
         * @param {string} str An string that may contain HTML entities.
         * @return {string} The HTML-escaped string.
         */
        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

    });
</script>


<script type="text/javascript" src="public/js/app.js"></script>

</body>
</html>