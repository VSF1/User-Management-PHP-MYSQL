<?php
session_start();
include('includes/config.php');
require_once 'includes/Feedback.php';

checkAuth();

if (isset($_GET['read'])) {
    $feedbackObj = new Feedback($dbh);
    $feedbackObj->markAsRead(intval($_GET['read']));
    $msg = "Message marked as read";
}

if (isset($_GET['readall'])) {
    $feedbackObj = new Feedback($dbh);
    $feedbackObj->markAllAsRead($_SESSION['alogin']);
    $msg = "All messages marked as read";
}

if (isset($_GET['action']) && $_GET['action'] == 'history' && isset($_GET['sender'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    $feedbackObj = new Feedback($dbh);
    $history = $feedbackObj->getConversation($_SESSION['alogin'], $_GET['sender']);
    echo json_encode($history);
    exit;
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
    <title>Messages</title>
    <!-- Font awesome -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- Sandstone Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Bootstrap Datatables -->
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <!-- Bootstrap social button library -->
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <!-- Bootstrap select -->
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <!-- Bootstrap file input -->
    <link rel="stylesheet" href="css/fileinput.min.css">
    <!-- Awesome Bootstrap checkbox -->
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <!-- Admin Stye -->
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
        .message-preview {
            cursor: pointer;
            color: #337ab7;
        }
        .message-preview:hover {
            text-decoration: underline;
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
                        <h2 class="page-title">Messages</h2>
                        <!-- Zero Configuration Table -->
                        <div class="panel panel-default">
                            <div class="panel-heading">List Users</div>
                            <div class="panel-body">
                                <form method="GET" action="messages.php" class="form-inline" style="margin-bottom: 20px;">
                                    <div class="form-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search']) : ''; ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="messages.php" class="btn btn-default">Reset</a>
                                    <a href="messages.php?readall=1" class="btn btn-info" onclick="return confirm('Are you sure you want to mark all messages as read?');">Mark All as Read</a>
                                </form>
                                <?php if ($error) {?>
                                <div class="errorWrap" id="msgshow"><?php echo htmlentities($error); ?> </div>
                                <?php } else if($msg) {?>
                                <div class="succWrap" id="msgshow"><?php echo htmlentities($msg); ?> </div>
                                <?php }?>
                                <table id="zctb" class="display table table-striped table-bordered table-hover"
                                       cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><a href="?sort_by=id&sort_order=<?php echo ($sortBy == 'id' && $sortOrder == 'DESC') ? 'ASC' : 'DESC'; ?>&search=<?php echo htmlentities($search); ?>">#</a></th>
                                            <th><a href="?sort_by=sender&sort_order=<?php echo ($sortBy == 'sender' && $sortOrder == 'DESC') ? 'ASC' : 'DESC'; ?>&search=<?php echo htmlentities($search); ?>">User</a></th>
                                            <th><a href="?sort_by=feedbackdata&sort_order=<?php echo ($sortBy == 'feedbackdata' && $sortOrder == 'DESC') ? 'ASC' : 'DESC'; ?>&search=<?php echo htmlentities($search); ?>">Message</a></th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $reciver = $_SESSION['alogin'];
                                        $feedbackObj = new Feedback($dbh);
                                        
                                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                                        $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
                                        $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $limit = 10;
                                        $offset = ($page - 1) * $limit;
                                        
                                        $total_records = $feedbackObj->getTotalFeedbackCount($reciver, $search);
                                        $total_pages = ceil($total_records / $limit);
                                        
                                        $results = $feedbackObj->getFeedbackByReceiver($reciver, $limit, $offset, $search, $sortBy, $sortOrder);
                                        $cnt = $offset + 1;
                                        if (count($results) > 0) {
                                            foreach($results as $result) {?>
                                        <tr class="<?php echo ($result->is_read == 0) ? 'warning' : ''; ?>">
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($result->sender);?></td>
                                            <td>
                                                <?php 
                                                $cleanMsg = strip_tags($result->feedbackdata);
                                                $preview = strlen($cleanMsg) > 50 ? substr($cleanMsg, 0, 50) . "..." : $cleanMsg;
                                                echo htmlentities($preview);
                                                ?>
                                                <a href="#" class="message-preview" data-id="<?php echo $result->id;?>" data-sender="<?php echo htmlentities($result->sender);?>" onclick="viewMessage(this); return false;">(Read Full)</a>
                                            </td>
                                            <td>
                                                <?php if($result->is_read == 0) {?>
                                                <a href="messages.php?read=<?php echo $result->id;?>" onclick="return confirm('Mark this message as read?');"><i class="fa fa-check" title="Mark as Read"></i></a>
                                                <?php } else { echo "Read"; } ?>
                                                <a href="send-message.php?reply_to=<?php echo htmlentities($result->sender);?>&reply_title=<?php echo htmlentities($result->title);?>" title="Reply"><i class="fa fa-reply"></i></a>
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

    <!-- Message View Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="messageModalLabel">Conversation History</h4>
                </div>
                <div class="modal-body">
                    <div id="conversation-history" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
    <script type="text/javascript">
        $(document).ready(function () {
            setTimeout(function() {
                $('.succWrap').slideUp("slow");
            }, 3000);
        });

        function viewMessage(element) {
            var sender = $(element).data('sender');
            var messageId = $(element).data('id');
            
            $('#messageModal').modal('show');
            $('#conversation-history').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</p>');
            $('#messageModalLabel').text('Conversation with ' + sender);

            $.ajax({
                url: 'messages.php',
                type: 'GET',
                data: { action: 'history', sender: sender },
                dataType: 'json',
                success: function(data) {
                    var html = '';
                    if (data.length > 0) {
                        $.each(data, function(index, item) {
                            var isCurrentUser = item.sender === '<?php echo $_SESSION['alogin']; ?>';
                            var alignClass = isCurrentUser ? 'text-right' : 'text-left';
                            var bgClass = isCurrentUser ? 'bg-info' : 'bg-warning';
                            var highlight = (item.id == messageId) ? 'border: 2px solid #337ab7;' : '';
                            
                            html += '<div class="' + alignClass + '" style="margin-bottom: 15px;">';
                            html += '<small class="text-muted">' + item.sender + ' - ' + (item.title ? item.title : 'No Subject') + '</small>';
                            html += '<div class="well well-sm ' + bgClass + '" style="display:inline-block; max-width: 80%; text-align: left; ' + highlight + '">' + item.feedbackdata + '</div>';
                            html += '</div>';
                        });
                    } else {
                        html = '<p class="text-center">No messages found.</p>';
                    }
                    $('#conversation-history').html(html);
                    // Scroll to bottom or to specific message could be added here
                },
                error: function() {
                    $('#conversation-history').html('<p class="text-center text-danger">Error loading history.</p>');
                }
            });
        }
    </script>
</body>
</html>