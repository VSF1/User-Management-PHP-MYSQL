<?php
require_once 'includes/Feedback.php';
$feedbackObj = new Feedback($dbh);
$feedbackCount = $feedbackObj->getUnreadFeedbackCount($_SESSION['alogin']);
?>
    <nav class="ts-sidebar">
        <ul class="ts-sidebar-menu">
            <li class="ts-label">Main</li>
            <li>
                <a href="profile.php"><i class="fa fa-user"></i> &nbsp;Profile</a>
            </li>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>
            <li>
                <a href="user-list.php"><i class="fa fa-users"></i> &nbsp;User List</a>
            </li>
            <li>
                <a href="create-user.php"><i class="fa fa-user-plus"></i> &nbsp;Create User</a>
            </li>
            <li>
                <a href="send-notification.php"><i class="fa fa-bell"></i> &nbsp;Send Notification</a>
            </li>
            <?php } ?>
            <li>
                <a href="feedback.php"><i class="fa fa-envelope"></i> &nbsp;Feedback</a>
            </li>
            <li>
                <a href="notification.php"><i class="fa fa-bell"></i> &nbsp;Notification<sup style="color:red">*</sup></a>
            </li>
            <li>
                <a href="messages.php"><i class="fa fa-envelope"></i> &nbsp;Messages
                    <?php if($feedbackCount > 0){?>
                        <span class="label label-primary pull-right"><?php echo htmlentities($feedbackCount); ?></span>
                    <?php } ?>
                </a>
            </li>
            <li>
                <a href="send-message.php"><i class="fa fa-paper-plane"></i> &nbsp;Send Message</a>
            </li>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>
            <li>
                <a href="admin-logs.php"><i class="fa fa-file-text"></i> &nbsp;Logs</a>
            </li>
            <?php } ?>
        </ul>
        <p class="text-center" style="color:#ffffff; margin-top: 100px;">© Ajay</p>
    </nav>