<?php
session_start();
require_once('connect.php');
require_once('header.php');

if (isset($_POST['comments_id']) && trim($_POST['comments_id']) != '' && isset($_POST['comments_codes']) && !empty($_POST['comments_codes'])) {
    $id = htmlspecialchars($_POST['comments_id']);
    $comments = $_POST['comments_codes'];
    $conclusion = htmlspecialchars($comments[0]);
    for ($i = 1; $i < count($comments); $i++) {
        $conclusion .= ',' . htmlspecialchars($comments[$i]);
    }

    date_default_timezone_set("Asia/Taipei");
    $now = new DateTime();
    $datetime = $now->format("Y-m-d H:i:s");

    $stmt = $conn->prepare('UPDATE `comments` SET `comment` = ?, `status` = 1, `update_at` = ? WHERE `id` = ?');
    $stmt->bind_param('ssi', $conclusion, $datetime, $id);
    $stmt->execute();
    $stmt->close();

    if (isset($_POST['other_comment']) && trim($_POST['other_comment']) != '') {
        $other = trim(htmlspecialchars($_POST['other_comment'], ENT_QUOTES));

        $stmt = $conn->prepare('UPDATE `comments` SET `other_comment` = ? WHERE `id` = ?');
        $stmt->bind_param('si', $other, $id);
        $stmt->execute();
        $stmt->close();
    }

    echo '評論成功。';
    header("refresh: 0.5; url=./index.php", true, 301);
    exit();
}
else {
    echo '不要亂來喔。';
    header("refresh: 0.5; url=./index.php", true, 301);
    exit();
}

require_once('footer.php');
