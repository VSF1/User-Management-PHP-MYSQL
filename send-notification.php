<?php
session_start();
include('includes/config.php');
require_once 'includes/User.php';
require_once 'includes/Notify.php';

checkAdmin();

$userObj = new User($dbh);
$users = $userObj->getUsers();

$msg = "";
$error = "";

if(isset($_POST['submit'])) {
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];
    $sender = $_SESSION['alogin'];

    $notifyObj = new Notify($dbh);
    if($notifyObj->sendNotification($sender, $receiver, $message)) {
        $msg = "Notification Sent Successfully";
    } else {
        $error = "Error sending notification";
    }
}
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
    <title>Send Notification</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .errorWrap { padding: 10px; margin: 0 0 20px 0; background: #dd3d36; color:#fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); }
        .succWrap { padding: 10px; margin: 0 0 20px 0; background: #5cb85c; color:#fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); }
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
                        <h2 class="page-title">Send Notification</h2>
                        <div class="panel panel-default">
                            <div class="panel-heading">Notification Details</div>
                            <div class="panel-body">
                                <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
                                else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Recipient<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <select name="receiver" class="form-control" required>
                                                <option value="">Select User</option>
                                                <?php foreach($users as $user) { ?>
                                                    <option value="<?php echo htmlentities($user->email);?>"><?php echo htmlentities($user->name);?> (<?php echo htmlentities($user->email);?>)</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Message<span style="color:red">*</span></label>
                                        <div class="col-sm-6">
                                            <textarea name="message" class="form-control" required rows="4"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-8 col-sm-offset-2">
                                            <button class="btn btn-primary" name="submit" type="submit">Send Notification</button>
                                        </div>
                                    </div>
                                </form>
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