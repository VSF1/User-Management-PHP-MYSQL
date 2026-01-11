<?php
session_start();
include('includes/config.php');
require_once 'includes/User.php';

checkAdmin();

$userObj = new User($dbh);
$msg = "";
$error = "";

if (isset($_POST['submit'])) {
    $file = $_FILES['image']['name'];
    $file_loc = $_FILES['image']['tmp_name'];
    $folder = "images/";
    $new_file_name = strtolower($file);
    $final_file = str_replace(' ', '-', $new_file_name);

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $mobileno = $_POST['mobile'];
    $designation = $_POST['designation'];
    $role = $_POST['role'];
    $image = "";

    if (!empty($file)) {
        if (move_uploaded_file($file_loc, $folder . $final_file)) {
            $image = $final_file;
        }
    }

    $lastInsertId = $userObj->register($name, $email, $password, $gender, $mobileno, $designation, $image, $role);

    if ($lastInsertId) {
        $msg = "User created successfully";
    } else {
        $error = "Something went wrong. Please try again (Email might already exist)";
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
    <title>Create User</title>
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
    <script type="text/javascript">
    function valid() {
        if(document.adduser.password.value.length < 6) {
            alert("Password must be at least 6 characters long.");
            document.adduser.password.focus();
            return false;
        }
        var mobile = document.adduser.mobile.value;
        if(isNaN(mobile) || mobile.length != 10) {
            alert("Mobile number must be 10 digits.");
            document.adduser.mobile.focus();
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
    <?php include('includes/header.php');?>
    <div class="ts-main-content">
        <?php include('includes/leftbar.php');?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="page-title">Create User</h2>
                        <div class="panel panel-default">
                            <div class="panel-heading">User Details</div>
                            <div class="panel-body">
                                <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
                                else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
                                <form method="post" name="adduser" class="form-horizontal" enctype="multipart/form-data" onSubmit="return valid();">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Name<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                        <label class="col-sm-2 control-label">Email<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Password<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                        <label class="col-sm-2 control-label">Gender<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <select name="gender" class="form-control" required>
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Mobile<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" name="mobile" class="form-control" required>
                                        </div>
                                        <label class="col-sm-2 control-label">Designation<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" name="designation" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Role<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <select name="role" class="form-control" required>
                                                <option value="user">User</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                        <label class="col-sm-2 control-label">Image</label>
                                        <div class="col-sm-4">
                                            <input type="file" name="image" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-8 col-sm-offset-2">
                                            <button class="btn btn-primary" name="submit" type="submit">Create User</button>
                                            <a href="user-list.php" class="btn btn-default">Cancel</a>
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