<?php
if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
?>
                <form class="form-inline mb-3" action="list.php" method="GET">
                    <div class="form-group mr-1 mr-sm-3">
                        <input type="text" class="form-control" name="condition_stu" placeholder="請輸入關鍵字">
                    </div>
                    <div class="form-group" style="display:inline;">
                        <input class="btn btn-success" type="submit" value="查詢學生">
<?php if (isset($_GET['condition_stu']) && trim($_GET['condition_stu']) != ''): ?>
                        <input type="button" class="btn btn-light" value="查詢結果：<?php echo $_GET['condition_stu']; ?>" disabled>
<?php endif; ?>
                    </div>
                </form>
<?php
        if (isset($_GET['condition_stu']) && trim($_GET['condition_stu']) != '') {
            $stu = htmlspecialchars($_GET['condition_stu']);

            $stmt = $conn->prepare('SELECT `id`, `name` FROM `stu` WHERE `name` LIKE CONCAT("%", ?, "%")');
            // 會出錯：$stmt->bind_param('s', '%'.$stu.'%');
            $stmt->bind_param('s', $stu);
            $stmt->execute();
            $result_stu = $stmt->get_result();
            $stmt->close();
            $rows_stu = mysqli_num_rows($result_stu);
            if ($rows_stu > 0) {
                $condition = '';
                for ($i = 0; $i < $rows_stu; $i++) {
                    $data_stu = mysqli_fetch_assoc($result_stu);
                    $split = ($i == 0) ? '' : ' OR ';
                    $condition .= $split . '`stu_id` = ' . $data_stu['id'];
                }
                $stmt = $conn->prepare('SELECT * FROM `comments` WHERE (`status` = 0 OR `status` = 1) AND (' . $condition . ')');
                $stmt->execute();
            }
            else {
                echo '<b>沒有學生。</b>';
                die();
            }
        }
        else {
            $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `status` = 0 OR `status` = 1');
            $stmt->execute();
        }
        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        if ($rows > 0) {
?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">學生</th>
                            <th scope="col">論文名稱</th>
                            <th scope="col">評論教授</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
            $comments_formatted_data = array();

            for ($i = 0; $i < $rows; $i++) {
                $comments = mysqli_fetch_assoc($result);
                $id = $comments['id'];
                $prof_id = $comments['prof_id'];
                $stu_id = $comments['stu_id'];
                $status = $comments['status'];

                // prof: get `name` by `id`
                $stmt = $conn->prepare('SELECT `id`, `name` FROM `prof` WHERE `id` = ?');
                $stmt->bind_param('i', $prof_id);
                $stmt->execute();
                $result_name_prof = $stmt->get_result();
                $stmt->close();
                $rows_name_prof = mysqli_num_rows($result_name_prof);

                // stu: get `name` by `id`
                $stmt = $conn->prepare('SELECT * FROM `stu` WHERE `id` = ?');
                $stmt->bind_param('i', $stu_id);
                $stmt->execute();
                $result_data_stu = $stmt->get_result();
                $stmt->close();
                $rows_data_stu = mysqli_num_rows($result_data_stu);

                if ($rows_name_prof == 1 && $rows_data_stu == 1) { // stu/prof 資料必需都要剛好只有一筆
                    $prof = mysqli_fetch_assoc($result_name_prof);
                    $stu = mysqli_fetch_assoc($result_data_stu);

                    /* format comments data for frontend render:
                     *   array(
                     *     array(
                     *       stu => [student_name],
                     *       prof => [professor_name]-[comment_id]-[comment_status], [professor_name]-[comment_id]-[comment_status], ...... ,
                     *       project => [project_name]
                     *     ),
                     *     array(
                     *       stu => [student_name],
                     *       prof => [professor_name]-[comment_id]-[comment_status], [professor_name]-[comment_id]-[comment_status], ...... ,
                     *       project => [project_name]
                     *     ),
                     *     ......
                     *   )
                     */
                    $tmp = array();
                    $length_formatted_data = count($comments_formatted_data);
                    if ($length_formatted_data > 0) {
                        $count = 0;
                        for ($j = 0; $j < $length_formatted_data; $j++) {
                            // need not push new formatted data while student name are duplicate
                            if ($comments_formatted_data[$j]['stu'] == $stu['name']) {
                                $comments_formatted_data[$j]['prof'] .= "," . $prof['name'] . "-$id-$status";
                                break;
                            }
                            $count++;
                        }

                        // push new formatted data while student name aren't duplicate
                        if ($count == $length_formatted_data) {
                            $tmp['stu'] = $stu['name'];
                            $tmp['prof'] = $prof['name'] . "-$id-$status";
                            $tmp['project'] = $stu['project'];
                            array_push($comments_formatted_data, $tmp);
                        }
                    }
                    else {
                        $tmp['stu'] = $stu['name'];
                        $tmp['prof'] = $prof['name'] . "-$id-$status";
                        $tmp['project'] = $stu['project'];
                        array_push($comments_formatted_data, $tmp);
                    }
                }
                else {
                    // unable to get stu/prof data, or data duplication
                    echo '<b>資料出錯，請聯絡管理員。</b>';
                }
            }

            // render formatted data to frontend
            foreach ($comments_formatted_data as $single_data) {
?>
                        <tr>
                            <td><?php echo $single_data['stu']; ?></td>
                            <td style="min-width: 150px;"><?php echo $single_data['project']; ?></td>
                            <td class="p-2">
                                <div class="d-flex flex-wrap">
<?php
                $comment_ids_of_single_stu = "";
                $prof_lists = explode(',', $single_data['prof']);
                foreach ($prof_lists as $single_prof) {
                    $professor_name = explode('-', $single_prof)[0];
                    $comment_id = explode('-', $single_prof)[1];
                    $comment_status = explode('-', $single_prof)[2];

                    // collect every comments' id of single student for better use
                    $comment_ids_of_single_stu .= ($comment_ids_of_single_stu == "") ? $comment_id : ",$comment_id";

                    // ($comment_status == 1): already commented
                    $btn_css = ($comment_status == 1) ? 'btn-danger': 'btn-outline-secondary';
                    $btn_title = ($comment_status == 1) ? '查看評論': '尚未評論';
                    $btn_disabled = ($comment_status == 1) ? '': 'disabled';
?>
                                    <form action="query.php" class="p-1">
                                        <input type="hidden" name="comments_id" value="<?php echo $comment_id; ?>">
                                        <input class="btn btn-sm <?php echo $btn_css; ?>" title="<?php echo $btn_title; ?>" <?php echo $btn_disabled; ?> type="submit" value="<?php echo $professor_name; ?>">
                                    </form>
<?php
                }
?>
                                </div>
                            </td>
                            <script>
                                function proposalRevoke(commentIds) {
                                    Swal.fire({
                                        title: '確定要撤銷這位學生的提案嗎？',
                                        text: "請注意，確定撤銷後將無法還原！",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: '確定',
                                        cancelButtonText: '取消',
                                    }).then(function (result) {
                                        if (result.value) {
                                            $.ajax({
                                                type: 'POST',
                                                url: 'proposalRevoke.php',
                                                data: { comment_ids_of_single_stu: commentIds },
                                                success: function (data) {
                                                    if (data == 'success')
                                                        Swal.fire('撤銷成功', '', 'success').then(function () { location.reload(); });
                                                    else
                                                        Swal.fire('撤銷失敗', '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
                                                },
                                                error: function () {
                                                    Swal.fire('撤銷失敗', '系統出錯，請聯絡系統管理員。', 'error');
                                                }
                                            });
                                        }
                                    });
                                    return false;
                                }
                            </script>
                            <td class="p-2">
                                <form class="p-1" onsubmit="return proposalRevoke('<?php echo $comment_ids_of_single_stu; ?>');">
                                    <input class="btn btn-sm btn-warning" type="submit" value="撤銷">
                                </form>
                            </td>
                        </tr>
<?php
            }
?>
                    </tbody>
                </table>
<?php
        }
        else {
            echo '<b>沒有評論資料。</b>';
        }
    }
}
else {
    header("refresh: 0; url=./index.php", true, 301);
    exit();
}