<?php
session_start();
require_once('connect.php');

if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
        if (isset($_POST['comment_ids_of_single_stu'])) {
            $comment_ids_of_single_stu = htmlspecialchars($_POST['comment_ids_of_single_stu']);

            $arr_comment_ids = explode(',', $comment_ids_of_single_stu);

            foreach ($arr_comment_ids as $comment_id) {
                $stmt = $conn->prepare('UPDATE `comments` SET `status` = -1 WHERE `id` = ?');
                $stmt->bind_param('i', $comment_id);
                $stmt->execute();
                $stmt->close();
            }
            echo 'success';
        }
        else {
            echo 'Error-1';
        }
    }
}
else {
    echo 'Error-2';
}