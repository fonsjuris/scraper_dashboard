<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="refresh" content="10" >
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

<!--    <link rel="stylesheet" type="text/css" href="public/css/print.css" media="print" />-->

    <title>Scraper dashboard</title>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-8">
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
                    <th>Last run</th>
                    <th>Num items processed</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php
                require_once 'dal.php';
                $scraper_results = get_status_of_all_processes();
                foreach ($scraper_results as $scraper_result) {?>
                    <tr>
                        <td> <?php echo $scraper_result['name']; ?> </td>
                        <td> <?php echo $scraper_result['timestamp']; ?> </td>
                        <td> <?php echo $scraper_result['num_processed_items']; ?> </td>
                        <td> <?php
                            if ($scraper_result['status_id'] == '1'){
                                echo '<i class="fas fa-circle" style="color:yellow"></i>';
                            } elseif ($scraper_result['status_id'] == '2'){
                                echo '<i class="fas fa-circle" style="color:green"></i>';
                            } elseif ($scraper_result['status_id'] == '3'){
                                echo '<i class="fas fa-circle" style="color:red"></i>';
                                echo $scraper_result['status_text'];
                            }
                             ?>

                        </td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>


        </div>
    </div>
</div>

<script type="text/javascript" src="public/js/app.js"></script>

</body>
</html>