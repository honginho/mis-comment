<?php
if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
?>
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
                <form class="form-inline mb-3" action="admin.php" method="GET">
                    <div class="form-group mr-1 mr-sm-3">
                        <input type="text" class="form-control" name="condition_all" placeholder="請輸入關鍵字">
                    </div>
                    <div class="form-group" style="display:inline;">
                        <input class="btn btn-success" type="submit" value="查詢學生或教授">
<?php if (isset($_GET['condition_all']) && trim($_GET['condition_all']) != ''): ?>
                        <input type="button" class="btn btn-light" value="關鍵字：<?php echo $_GET['condition_all']; ?>" disabled>
<?php endif; ?>
                    </div>
                </form>
<?php
        if (isset($_GET['condition_all']) && trim($_GET['condition_all']) != '') { //若搜尋框內有接收到東西
            $all = htmlspecialchars($_GET['condition_all']); //將搜尋關鍵字化成單純可讀

            // $stmt = $conn->prepare('SELECT `id`, `name` FROM `stu` WHERE `name` LIKE CONCAT("%", ?, "%")');
            // // 會出錯：$stmt->bind_param('s', '%'.$stu.'%');
            // $stmt->bind_param('s', $stu);
            // $stmt->execute();
            // $result_stu = $stmt->get_result();
            // $stmt->close();
            // $rows_stu = mysqli_num_rows($result_stu);
            // if ($rows_stu > 0) {
            //     $condition = '';
            //     for ($i = 0; $i < $rows_stu; $i++) {
            //         $data_stu = mysqli_fetch_assoc($result_stu);
            //         $split = ($i == 0) ? '' : ' OR ';
            //         $condition .= $split . '`stu_id` = ' . $data_stu['id'];
            //     }
            //     $stmt = $conn->prepare('SELECT * FROM `comments` WHERE (`status` = 0 OR `status` = 1) AND (' . $condition . ')');
            //     $stmt->execute();
            // }
            // else {
            //     echo '<b>沒有學生。</b>';
            //     die();
            // }

            $stmt = $conn->prepare('SELECT `id`, `name` FROM `stu` WHERE `name` LIKE CONCAT("%", ?, "%")'); //模糊查詢
            $stmt->bind_param('s', $all);
            $stmt->execute();
            $result_stu = $stmt->get_result();
            $stmt->close();
            // var_dump($result_stu);
            // echo '<br>';

            $stmt = $conn->prepare('SELECT `id`, `name` FROM `prof` WHERE `name` LIKE CONCAT("%", ?, "%")');
            $stmt->bind_param('s', $all);
            $stmt->execute();
            $result_prof = $stmt->get_result();
            $stmt->close();
            // var_dump($result_prof);
            // $stmt->close();

            $rows_prof = mysqli_num_rows($result_prof);
            $rows_stu =  mysqli_num_rows($result_stu);
            if ($rows_prof + $rows_stu > 0){
                $condition = "";
                if ($rows_prof > 0){
                    for ($i = 0; $i < $rows_prof; $i++){
                        $data_prof = mysqli_fetch_assoc($result_prof);
                        $split = ($i == 0) ? '' : ' OR ';
                        $condition .= $split . '`prof_id` = ' . $data_prof['id'];
                    }
                }
                if ($rows_stu > 0){
                    for ($i = 0; $i < $rows_stu; $i++){
                        $data_stu = mysqli_fetch_assoc($result_stu);
                        $split = ($i == 0 && $rows_prof == 0) ? '' : ' OR ';
                        $condition .= $split . '`stu_id` = ' . $data_stu['id'];
                    }
                }
                $stmt = $conn->prepare('SELECT * FROM `comments` WHERE  (' . $condition . ') ORDER BY `id` DESC');
                $stmt->execute();
            }
            else {
                echo '<b>查無結果。</b>';
                die();
            }
        }
        else {
            $stmt = $conn->prepare('SELECT * FROM `comments` ORDER BY `id` DESC');
            $stmt->execute();
        }

        // TODO: Debug 會出現資料顯示不完整的問題 => 學生、論文名稱、教授(沒有全列)

        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        if ($rows > 0) {
?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">梯次</th>
                            <th scope="col">學號</th>
                            <th scope="col">學生</th>
                            <th scope="col">論文名稱</th>
                            <th scope="col">評論教授</th>
                            <th scope="col">指導教授</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
            $comments_formatted_data = array();

            for ($i = 0; $i < $rows; $i++) {
                $comments = mysqli_fetch_assoc($result);
                $id = $comments['id'];
                $semester = $comments['semester'];
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
                     *
                     *   array(
                     *     [semester] => array(
                     *       array(
                     *         stu => [student_name]-[student_id],
                     *         prof => [professor_name]-[comment_id]-[comment_status], [professor_name]-[comment_id]-[comment_status], ...... ,
                     *         project => [project_name]
                     *       ),
                     *       array(
                     *         stu => [student_name]-[student_id],
                     *         prof => [professor_name]-[comment_id]-[comment_status], [professor_name]-[comment_id]-[comment_status], ...... ,
                     *         project => [project_name]
                     *       ),
                     *     ),
                     *     [semester] => array(
                     *       array(
                     *         stu => [student_name]-[student_id],
                     *         prof => [professor_name]-[comment_id]-[comment_status], [professor_name]-[comment_id]-[comment_status], ...... ,
                     *         project => [project_name]
                     *       ),
                     *       array(
                     *         stu => [student_name]-[student_id],
                     *         prof => [professor_name]-[comment_id]-[comment_status], [professor_name]-[comment_id]-[comment_status], ...... ,
                     *         project => [project_name]
                     *       ),
                     *     ),
                     *     ......
                     *   )
                     */
                    $tmp = array();
                    if (!isset($comments_formatted_data[$semester]))
                        $comments_formatted_data[$semester] = array();

                    $length_formatted_data = count($comments_formatted_data[$semester]);
                    if ($comments_formatted_data[$semester] > 0) {
                        $count = 0;
                        for ($j = 0; $j < $length_formatted_data; $j++) {
                            // need not push new formatted data while student name are duplicate
                            if ($comments_formatted_data[$semester][$j]['stu'] == $stu['name'] . '-' . $stu_id) {
                                $comments_formatted_data[$semester][$j]['prof'] .= "," . $prof['name'] . "-$id-$status";
                                break;
                            }
                            $count++;
                        }

                        // push new formatted data while student name aren't duplicate
                        if ($count == $length_formatted_data) {
                            $tmp['semester'] = $semester;
                            $tmp['stu'] = $stu['name'] . '-' . $stu_id;
                            $tmp['prof'] = $prof['name'] . "-$id-$status";
                            $tmp['project'] = $stu['project'];
                            array_push($comments_formatted_data[$semester], $tmp);
                        }
                    }
                    else {
                        $tmp['semester'] = $semester;
                        $tmp['stu'] = $stu['name'] . '-' . $stu_id;
                        $tmp['prof'] = $prof['name'] . "-$id-$status";
                        $tmp['project'] = $stu['project'];
                        array_push($comments_formatted_data[$semester], $tmp);
                    }
                }
                else {
                    // unable to get stu/prof data, or data duplication
                    echo '<b>資料出錯，請聯絡管理員。</b>';
                }
            }

            // var_dump($comments_formatted_data); die();

            // render formatted data to frontend
            $semester_lists_array = array_keys($comments_formatted_data);
            $length_semester_lists_array = count($semester_lists_array);
            for ($i = 0; $i < $length_semester_lists_array; $i++) {
                // semester: get `status` to determine if it is available
                $stmt = $conn->prepare('SELECT * FROM `semester` WHERE `name` = ?');
                $stmt->bind_param('i', $semester_lists_array[$i]);
                $stmt->execute();
                $result_semester = $stmt->get_result();
                $stmt->close();
                $rows_semester = mysqli_num_rows($result_semester);
                $single_semester = mysqli_fetch_assoc($result_semester);
                $semester_is_available = $single_semester['status'];
                $semester_is_available_css = ($semester_is_available) ? 'opacity: 1' : 'opacity: .5';
                foreach ($comments_formatted_data[$semester_lists_array[$i]] as $single_data) {
                    $student_name = explode('-', $single_data['stu'])[0];
                    $student_id = explode('-', $single_data['stu'])[1];

                    $stmt = $conn->prepare('SELECT * FROM `stu` WHERE `id` = ?');
                    $stmt->bind_param('i', $student_id);
                    $stmt->execute();
                    $result_data_student = $stmt->get_result();
                    $stmt->close();
                    $rows_data_student = mysqli_num_rows($result_data_student);
                    $student = mysqli_fetch_assoc($result_data_student);

                    if ($rows_data_student != 1) {
                        die('系統出錯 - 272');
                    }
                    else {
                        $student_stu_id = $student['stu_id'];
                        $student_prof_id_teaching = $student['prof_id_teaching'];
                    }
?>
                        <tr style="<?php echo $semester_is_available_css; ?>">
                            <td><?php echo $single_data['semester']; ?></td>
                            <td><?php echo $student_stu_id; ?></td>
                            <td><?php echo $student_name; ?></td>
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
                            <td style="min-width: 90px; max-width: 90px;">
<?php
                    $seperate_prof_id_teaching = explode("-",$student_prof_id_teaching); //回傳指導老師ID的陣列
                    $tmp = [];
                    for ($j = 0; $j < count($seperate_prof_id_teaching); $j++) {
                        $get_prof_name = mysqli_query($conn,"SELECT `id`, `name` FROM `prof` WHERE `id` = '$seperate_prof_id_teaching[$j]'");
                        while ($row2 = mysqli_fetch_row($get_prof_name)) {
                            array_push($tmp, $row2[1]);
                        }
                    }                      
                    echo implode("、", $tmp);
?>
                            </td>
                            <td class="p-2">
<?php if ($semester_is_available): ?>
                                <form class="p-1" onsubmit="return proposalRevoke('<?php echo $comment_ids_of_single_stu; ?>');">
                                    <input class="btn btn-sm btn-warning" type="submit" value="撤銷">
                                </form>
<?php else: ?>
                                <b>(梯次已關閉)</b>
<?php endif; ?>
                            </td>
                        </tr>
<?php
                }
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