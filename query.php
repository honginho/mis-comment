<?php
// session_start();
require_once('connect.php');
require_once('header.php');

if (isset($_GET['comments_id'])) {
    $id = htmlspecialchars($_GET['comments_id']);

    // 確認是不是這位教授可以去評論的學生
    $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `id` = ? && `prof_id` = ? AND (`status` = 0 OR `status` = 1)');
    $stmt->bind_param('ii', $id, $_SESSION['prof_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $rows = mysqli_num_rows($result);

    if ($rows == 1) {

        // 抓資料，如果有紀錄的話
        $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `id` = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result_data_comments = $stmt->get_result();
        $stmt->close();
        $rows_data_comments = mysqli_num_rows($result_data_comments);
        $comments_details['other_comment'] = '';
        if ($rows_data_comments == 1) {
            $comments_details = mysqli_fetch_assoc($result_data_comments);
        }

        // 產生範例評論
        $stmt = $conn->prepare('SELECT * FROM `comments_codes`');
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        if ($rows > 0) {
            $comments_eg_html = '<form action="comments.php" method="POST">';
            $comments_eg_html .= '<input type="text" name="comments_id" value="' . $id . '">';
            $comments_eg_html .= '<input type="submit" value="送出">';
            $comments_eg_html .= '<div><label for="other_comment">其他評論</label></div>';
            $comments_eg_html .= '<textarea name="other_comment" id="other_comment" cols="60" rows="3">' . $comments_details['other_comment'] . '</textarea>';
            $comments_eg_html .= '<div style="display: flex; justify-content: center;">';
            for ($i = 0; $i < $rows; $i++) {
                $comments_eg = mysqli_fetch_assoc($result);
                $comments_main = $comments_eg['main'];
                $comments_sub = $comments_eg['sub'];
                $comments_name = $comments_eg['name'];
                $comments_format = $comments_main . '-' . $comments_sub;
                if ($comments_sub == 0) {
                    if ($i != 0) $comments_eg_html .= '</div>';
                    $comments_eg_html .= '<div data-main="' . $comments_main . '">';
                    $comments_eg_html .= '<h4>' . $comments_name . '</h4>';
                }
                else {
                    $comments_eg_html .= '<label style="display: block; margin: 4px; border: 1px solid #d0d0d0;" for="comments-' . $comments_format . '">';
                    $comments_eg_html .= '<input type="checkbox" name="comments_codes[]" id="comments-' . $comments_format . '" value="' . $comments_format . '">';
                    $comments_eg_html .= $comments_name . '</label>';
                }
            }
            $comments_eg_html .= '</div></div></form>';
            echo $comments_eg_html;
        }
        else {
            echo '找不到評論範例。';
            die();
        }

        if ($rows_data_comments == 1) {
?>
            <script>
                let comments = '<?php echo $comments_details['comment']; ?>'.split(',');
                for (let i  = 0; i < comments.length; i++) {
                    $('#comments-' + comments[i]).prop("checked", true);
                }
            </script>
<?php
        }
    }
    else {
        echo '這不是教授可以評論的學生喔。';
        header("refresh: 0; url=./index.php", true, 301);
        exit();
    }
}
else {
    echo '請選擇評論學生。';
    header("refresh: 0; url=./index.php", true, 301);
    exit();
}

require_once('footer.php');
