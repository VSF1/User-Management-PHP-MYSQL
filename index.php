<?php
session_start();
include('includes/config.php');
require_once 'includes/User.php';

if(isset($_POST['login'])) {
    $email = $_POST['username'];
    $password = $_POST['password'];
    $user = new User($dbh);
    $loggedInUser = $user->login($email, $password);
    if($loggedInUser) {
        $_SESSION['alogin'] = $_POST['username'];
        $_SESSION['role'] = $loggedInUser->role;
        // Check if the password matches the default hash for 'admin'
        if ($loggedInUser->password === '$2y$10$L4kM9ZMFq0pgtgZbFe0Bcu0FabVSNTrP1L6FVvL0mk7.9BuHJSR8G') {
            $_SESSION['force_pwd_change'] = true;
            echo "<script type='text/javascript'> document.location = 'change-password.php'; </script>";
        } else {
            echo "<script type='text/javascript'> document.location = 'profile.php'; </script>";
        }
    } else {
        echo "<script>alert('Invalid Details Or Account Not Confirmed');</script>";
    }
}?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-page bk-img">
        <div class="form-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <h1 class="text-center text-bold mt-4x">Login</h1>
                        <div class="well row pt-2x pb-3x bk-light">
                            <div class="col-md-8 col-md-offset-2">
                                <form method="post">
                                    <label for="" class="text-uppercase text-sm">Your Email</label>
                                    <input type="text" placeholder="Username" name="username" class="form-control mb" 
                                        autoComplete="off" required>
                                    <label for="" class="text-uppercase text-sm">Password</label>
                                    <input type="password" placeholder="Password" name="password" 
                                        autoComplete="off" class="form-control mb" required>
                                    <button class="btn btn-primary btn-block" name="login" type="submit">LOGIN</button>
                                </form>
                                <br>
                                <p>Don't Have an Account? <a href="register.php" >Signup</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Loading Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/fileinput.js"></script>
    <script src="js/chartData.js"></script>
    <script src="js/main.js"></script>
</body>
</html>