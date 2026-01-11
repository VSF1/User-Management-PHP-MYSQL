<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['alogin']) || strlen($_SESSION['alogin']) == 0) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

if(isset($_FILES['upload'])){
    $file_name = $_FILES['upload']['name'];
    $file_tmp = $_FILES['upload']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");

    $funcNum = isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : 0;
    $url = '';
    $message = '';

    if (in_array($file_ext, $allowed_extensions)) {
        if (!file_exists('images')) {
            mkdir('images', 0777, true);
        }
        
        $new_file_name = time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $destination = 'images/' . $new_file_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            $url = $destination;
            $message = 'Image uploaded successfully';
        } else {
            $message = 'Error uploading file';
        }
    } else {
        $message = 'Invalid file extension. Only JPG, JPEG, PNG, and GIF are allowed.';
    }

    if ($funcNum > 0) {
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'url' => $url,
            'error' => $url ? null : ['message' => $message]
        ]);
    }
}
?>