<?php
// session_start();
require_once('connect.php');
require_once('header.php');

if (isset($_SESSION['level']) && $_SESSION['level'] == 0) {
    // 取得所有梯次的學生
    $stmt = $conn->prepare('SELECT * FROM `comments`');
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $rows = mysqli_num_rows($result);
    if ($rows > 0) {
        for ($i = 0; $i < $rows; $i++) {
            echo '<div style="margin-bottom: 1em; border: 1px solid red;">';
            $comments = mysqli_fetch_assoc($result);
            $id = $comments['id'];
            $prof_id = $comments['prof_id'];
            $stu_id = $comments['stu_id'];
            $status = $comments['status'];
            // echo 'comments_id: ' . $id . '<br>';
            // echo 'prof_id: ' . $prof_id . '<br>';
            // echo 'stu_id: ' . $stu_id . '<br>';

            $stmt = $conn->prepare('SELECT * FROM `prof` WHERE `id` = ?');
            $stmt->bind_param('i', $prof_id);
            $stmt->execute();
            $result_data_prof = $stmt->get_result();
            $stmt->close();
            $rows_data_prof = mysqli_num_rows($result_data_prof);
            $prof = mysqli_fetch_assoc($result_data_prof);
            echo '評論教授: ' . $prof['name'] . '<br>';

            $stmt = $conn->prepare('SELECT * FROM `stu` WHERE `id` = ?');
            $stmt->bind_param('i', $stu_id);
            $stmt->execute();
            $result_data_stu = $stmt->get_result();
            $stmt->close();
            $rows_data_stu = mysqli_num_rows($result_data_stu);
            $stu = mysqli_fetch_assoc($result_data_stu);
            echo '學生: ' . $stu['name'] . '<br>';
            echo '學號: ' . $stu['stu_id'] . '<br>';
            echo '論文名稱: ' . $stu['project'] . '<br>';
            if ($status == 1) echo '<b>(已評論過)</b>';
            echo '</div>';
        }
    }
    else {
        echo '<br>沒有學生需要評論。';
    }
}
else {
    header("refresh: 0; url=./index.php", true, 301);
    exit();
}

require_once('footer.php');
