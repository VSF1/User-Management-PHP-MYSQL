<?php
session_start();
include('includes/config.php');
require_once 'includes/Logger.php';

checkAdmin();

$logger = new Logger();
$msg = "";

if (isset($_POST['clear'])) {
    $logger->clearLogs();
    $msg = "Logs cleared successfully";
}

if (isset($_POST['download'])) {
    $file = $logger->getLogFilePath();
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

$level = isset($_GET['level']) ? $_GET['level'] : '';
$logs = $logger->getLogs($level);
$logs = array_reverse($logs); // Show newest logs first
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">
    <title>System Error Logs</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .succWrap{
            padding: 10px;
            margin: 0 0 20px 0;
            background: #5cb85c;
            color:#fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .log-container {
            background: #f5f5f5;
            border: 1px solid #e3e3e3;
            padding: 15px;
            height: 500px;
            overflow-y: scroll;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php');?>
    <div class="ts-main-content">
        <?php include('includes/leftbar.php');?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="page-title">System Error Logs</h2>
                        <div class="panel panel-default">
                            <div class="panel-heading">Error Logs</div>
                            <div class="panel-body">
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
                                
                                <form method="get" class="form-inline" style="margin-bottom: 20px;">
                                    <div class="form-group">
                                        <label for="level">Filter by Level: </label>
                                        <select name="level" id="level" class="form-control" onchange="this.form.submit()">
                                            <option value="">All</option>
                                            <option value="INFO" <?php if($level == 'INFO') echo 'selected'; ?>>INFO</option>
                                            <option value="ERROR" <?php if($level == 'ERROR') echo 'selected'; ?>>ERROR</option>
                                        </select>
                                    </div>
                                </form>

                                <form method="post" style="margin-bottom: 20px;">
                                    <button type="submit" name="clear" class="btn btn-danger" onclick="return confirm('Are you sure you want to clear all logs?');">Clear Logs</button>
                                    <button type="submit" name="download" class="btn btn-info">Download Logs</button>
                                    <button type="button" class="btn btn-default" onclick="location.reload();">Refresh</button>
                                </form>

                                <div class="log-container">
                                    <?php 
                                    if (empty($logs)) {
                                        echo "No logs found.";
                                    } else {
                                        foreach ($logs as $line) {
                                            echo htmlentities($line) . "\n";
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>