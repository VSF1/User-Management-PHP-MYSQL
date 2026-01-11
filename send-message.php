<?php
session_start();
include('includes/config.php');
require_once 'includes/User.php';
require_once 'includes/Notify.php';
require_once 'includes/Feedback.php';

checkAuth();

$userObj = new User($dbh);
$users = $userObj->getUsers();

$msg = "";
$error = "";
$title = "";
$description = "";
$receiver = "";

if(isset($_GET['reply_to'])) {
    $receiver = $_GET['reply_to'];
}
if(isset($_GET['reply_title'])) {
    $title = "Re: " . $_GET['reply_title'];
}

if(isset($_POST['submit'])) {
    $file = $_FILES['attachment']['name'];
    $file_loc = $_FILES['attachment']['tmp_name'];
    $folder = "attachment/";
    $new_file_name = strtolower($file);
    $final_file = str_replace(' ', '-', $new_file_name);
    
    $title = $_POST['title'];
    $description = $_POST['description'];
    $receiver = $_POST['receiver'];
    $sender = $_SESSION['alogin'];
    $attachment = "";

    if(!empty($file)) {
        if (move_uploaded_file($file_loc, $folder . $final_file)) {
            $attachment = $final_file;
        }
    }

    $notitype = 'New Message';
    $notifyObj = new Notify($dbh);
    $notifyObj->sendNotification($sender, $receiver, $notitype);

    $feedbackObj = new Feedback($dbh);
    if($feedbackObj->sendFeedback($sender, $receiver, $title, $description, $attachment)){
        $msg = "Message Sent Successfully";
    } else {
        $error = "Error sending message";
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
    <title>Send Message</title>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.css">
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
                        <h2 class="page-title">Send Message</h2>
                        <div class="panel panel-default">
                            <div class="panel-heading">Message Details</div>
                            <div class="panel-body">
                                <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
                                else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
                                <form method="post" class="form-horizontal" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Recipient<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <select name="receiver" class="form-control" required>
                                                <option value="">Select Recipient</option>
                                                <?php foreach($users as $user) { 
                                                    if($user->email !== $_SESSION['alogin']) {
                                                ?>
                                                    <option value="<?php echo htmlentities($user->email);?>" <?php if($receiver == $user->email) echo 'selected';?>><?php echo htmlentities($user->name);?> (<?php echo htmlentities($user->email);?>)</option>
                                                <?php } } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Title<span style="color:red">*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" name="title" class="form-control" required value="<?php echo htmlentities($title);?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Message<span style="color:red">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea name="description" id="description" class="form-control" rows="5"><?php echo htmlentities($description);?></textarea>
                                            <div id="char-count" style="margin-top: 5px; color: #666;">Characters: 0</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Attachment</label>
                                        <div class="col-sm-4">
                                            <input type="file" name="attachment" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-8 col-sm-offset-2">
                                            <button class="btn btn-primary" name="submit" type="submit">Send Message</button>
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
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script>
        class MyUploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }
            upload() {
                return this.loader.file
                    .then(file => new Promise((resolve, reject) => {
                        const data = new FormData();
                        data.append('upload', file);
                        fetch('upload.php', {
                            method: 'POST',
                            body: data
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.error) {
                                reject(result.error.message);
                            } else {
                                resolve({ default: result.url });
                            }
                        })
                        .catch(error => {
                            reject('Upload failed');
                        });
                    }));
            }
        }

        function MyCustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new MyUploadAdapter(loader);
            };
        }

        ClassicEditor
            .create(document.querySelector('#description'), {
                extraPlugins: [MyCustomUploadAdapterPlugin]
            })
            .then(editor => {
                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    // Update original textarea for form submission
                    document.querySelector('#description').value = data;
                    // Simple character count (stripping HTML tags)
                    const text = data.replace(/<[^>]*>?/gm, '');
                    document.getElementById('char-count').innerText = 'Characters: ' + text.length;
                });
            })
            .catch(error => {
                console.error(error);
            });
    </script>
</body>
</html>