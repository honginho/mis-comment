<?php
// session_start();
require_once('connect.php');
require_once('header.php');

if (isset($_POST['comments_id']) && trim($_POST['comments_id']) != '' && isset($_POST['comments_codes']) && !empty($_POST['comments_codes'])) {
    $id = htmlspecialchars($_POST['comments_id']);

    // 確認是不是這位教授可以去評論的學生
    $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `id` = ? && `prof_id` = ? AND (`status` = 0 OR `status` = 1)');
    $stmt->bind_param('ii', $id, $_SESSION['prof_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $rows = mysqli_num_rows($result);
    if ($rows == 1) {
        // 如果有`other_comment`要存進資料庫的話
        if (isset($_POST['other_comment']) && isset($_POST['other_comment_index'])) {
            $arr_other_comment_index = explode(',', htmlspecialchars($_POST['other_comment_index']));
            // 判斷`index`和`other_comment[]`長度是否一樣
            if (count($_POST['other_comment']) == count($arr_other_comment_index)) {
                $result_other_comment = '';
                for ($i = 0; $i < count($_POST['other_comment']); $i++) {
                    $otehr_comment_index = $arr_other_comment_index[$i];
                    $other_comment = htmlspecialchars($_POST['other_comment'][$i], ENT_QUOTES);
                    $split_symbol = ($i == 0) ? '' : '@,|,@';
                    $result_other_comment .= $split_symbol . $otehr_comment_index . '@-|-@' . $other_comment;
                }
                // $other = trim(htmlspecialchars($_POST['other_comment'], ENT_QUOTES));

                $stmt = $conn->prepare('UPDATE `comments` SET `other_comment` = ? WHERE `id` = ?');
                $stmt->bind_param('si', $result_other_comment, $id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                echo '評論資料有誤。';
                header("refresh: 0.5; url=./index.php", true, 301);
                exit();
            }
        }

        // 如果有`comments_id`要存進資料庫的話
        if (isset($_POST['comments_codes'])) {
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
        }

        echo '評論成功。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
        echo '這不是教授可以評論的學生喔。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
}
else {
    echo '不要亂來喔。';
    header("refresh: 0.5; url=./index.php", true, 301);
    exit();
}

require_once('footer.php');
