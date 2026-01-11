<?php
session_start();
include('includes/config.php');
require_once 'includes/User.php';

checkAdmin();

$userObj = new User($dbh);
$msg = "";
$error = "";

if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    // Prevent deleting self
    $currentUser = $userObj->getUser($_SESSION['alogin']);
    if ($currentUser && $currentUser->id == $id) {
        $error = "You cannot delete your own account!";
    } else {
        if ($userObj->delete($id)) {
            $msg = "User deleted successfully";
        } else {
            $error = "Error deleting user";
        }
    }
}

if (isset($_GET['enable'])) {
    $id = intval($_GET['enable']);
    $userObj->activate($id);
    $msg = "User account enabled";
}

if (isset($_GET['disable'])) {
    $id = intval($_GET['disable']);
    if ($id == $userObj->getUser($_SESSION['alogin'])->id) {
        $error = "You cannot disable your own account";
    } else {
        $userObj->deactivate($id);
        $msg = "User account disabled";
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
    <title>User List</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #dd3d36;
            color:#fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap{
            padding: 10px;
            margin: 0 0 20px 0;
            background: #5cb85c;
            color:#fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
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
                        <h2 class="page-title">User List</h2>
                        <div class="panel panel-default">
                            <div class="panel-heading">All Users</div>
                            <div class="panel-body">
                                <form method="GET" action="user-list.php" class="form-inline" style="margin-bottom: 20px;">
                                    <div class="form-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search']) : ''; ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="user-list.php" class="btn btn-default">Reset</a>
                                    <a href="create-user.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Add User</a>
                                </form>
                                <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
                                else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
                                <table class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><a href="?sort_by=id&sort_order=<?php echo ($sortBy == 'id' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo htmlentities($search); ?>">#</a></th>
                                            <th><a href="?sort_by=name&sort_order=<?php echo ($sortBy == 'name' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo htmlentities($search); ?>">Name</a></th>
                                            <th><a href="?sort_by=email&sort_order=<?php echo ($sortBy == 'email' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo htmlentities($search); ?>">Email</a></th>
                                            <th><a href="?sort_by=gender&sort_order=<?php echo ($sortBy == 'gender' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo htmlentities($search); ?>">Gender</a></th>
                                            <th><a href="?sort_by=mobile&sort_order=<?php echo ($sortBy == 'mobile' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo htmlentities($search); ?>">Mobile</a></th>
                                            <th><a href="?sort_by=designation&sort_order=<?php echo ($sortBy == 'designation' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo htmlentities($search); ?>">Designation</a></th>
                                            <th><a href="?sort_by=status&sort_order=<?php echo ($sortBy == 'status' && $sortOrder == 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo htmlentities($search); ?>">Status</a></th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                                        $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
                                        $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $limit = 10;
                                        $offset = ($page - 1) * $limit;
                                        
                                        $total_records = $userObj->getTotalUsers($search);
                                        $total_pages = ceil($total_records / $limit);
                                        
                                        $results = $userObj->getUsers($limit, $offset, $search, $sortBy, $sortOrder);
                                        $cnt = $offset + 1;
                                        if (count($results) > 0) {
                                            foreach($results as $result) {?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($result->name);?></td>
                                            <td><?php echo htmlentities($result->email);?></td>
                                            <td><?php echo htmlentities($result->gender);?></td>
                                            <td><?php echo htmlentities($result->mobile);?></td>
                                            <td><?php echo htmlentities($result->designation);?></td>
                                            <td><?php echo ($result->status == 1) ? 'Active' : 'Inactive';?></td>
                                            <td>
                                                <?php if($result->status == 1) {?>
                                                    <a href="user-list.php?disable=<?php echo $result->id;?>&search=<?php echo htmlentities($search);?>&page=<?php echo $page;?>&sort_by=<?php echo htmlentities($sortBy);?>&sort_order=<?php echo htmlentities($sortOrder);?>" onclick="return confirm('Do you want to disable this user?');" class="btn btn-danger btn-xs">Disable</a>
                                                <?php } else {?>
                                                    <a href="user-list.php?enable=<?php echo $result->id;?>&search=<?php echo htmlentities($search);?>&page=<?php echo $page;?>&sort_by=<?php echo htmlentities($sortBy);?>&sort_order=<?php echo htmlentities($sortOrder);?>" onclick="return confirm('Do you want to enable this user?');" class="btn btn-success btn-xs">Enable</a>
                                                <?php } ?>
                                                <a href="user-list.php?del=<?php echo $result->id;?>" onclick="return confirm('Do you want to delete this user?');"><i class="fa fa-trash" style="color:red"></i></a>
                                            </td>
                                        </tr>
                                        <?php 
                                            $cnt=$cnt+1; 
                                        }} 
                                        ?>
                                    </tbody>
                                </table>
                                
                                <div class="text-center">
                                    <ul class="pagination">
                                        <?php if($page > 1): ?>
                                            <li><a href="?page=<?php echo $page-1; ?>&search=<?php echo htmlentities($search); ?>&sort_by=<?php echo htmlentities($sortBy); ?>&sort_order=<?php echo htmlentities($sortOrder); ?>">Previous</a></li>
                                        <?php endif; ?>
                                        
                                        <?php for($i=1; $i<=$total_pages; $i++): ?>
                                            <li class="<?php echo ($page == $i) ? 'active' : ''; ?>"><a href="?page=<?php echo $i; ?>&search=<?php echo htmlentities($search); ?>&sort_by=<?php echo htmlentities($sortBy); ?>&sort_order=<?php echo htmlentities($sortOrder); ?>"><?php echo $i; ?></a></li>
                                        <?php endfor; ?>
                                        
                                        <?php if($page < $total_pages): ?>
                                            <li><a href="?page=<?php echo $page+1; ?>&search=<?php echo htmlentities($search); ?>&sort_by=<?php echo htmlentities($sortBy); ?>&sort_order=<?php echo htmlentities($sortOrder); ?>">Next</a></li>
                                        <?php endif; ?>
                                    </ul>
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