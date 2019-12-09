<?php
require_once('connect.php');
require_once('header.php');

if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] == 0) { //管理員
        // header("refresh: 0; url=./admin.php", true, 301); //跳轉頁面，會多停頓在此php頁面
        // exit();
        require_once('admin.php');
    }
    else {
        $prof_id = $_SESSION['prof_id'];
?>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <b>國立中正大學資管所&醫管所論文提案書評論系統</b>
                <form action="logout.php" method="POST">
                    <input type="button" class="btn btn-sm btn-light" value="<?php echo $_SESSION['account']; ?>" disabled>
                    <input type="submit" class="btn btn-sm btn-danger" value="登出">
                </form>
            </div>
            <div class="card-body table-responsive">
<?php
        // select comments that their semesters are available
        $stmt = $conn->prepare('SELECT * FROM `semester` WHERE `status` = 1');
        $stmt->execute();
        $result_semester = $stmt->get_result();
        $stmt->close();
        $rows_semester = mysqli_num_rows($result_semester);
        $semester_lists_available = array();
        if ($rows_semester > 0) {
?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">學號</th>
                            <th scope="col">學生</th>
                            <th scope="col">論文名稱</th>
                            <th scope="col">指導老師</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
            for ($i = 0; $i < $rows_semester; $i++) {
                $semester = mysqli_fetch_assoc($result_semester);
                array_push($semester_lists_available, $semester['name']);
            }
        }
        else {
            die('<b>沒有學生需要評論。</b>');
        }
        $semester_condition = '';
        for ($i = 0; $i < count($semester_lists_available); $i++) {
            if ($i == 0) $semester_condition .= ' AND (';
            $split = ($i == 0) ? '' : ' OR ';
            $semester_condition .= $split . '`semester` = ' . $semester_lists_available[$i];
            if ($i == count($semester_lists_available)-1) $semester_condition .= ')';
        }

        $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `prof_id` = ?' . $semester_condition);
        $stmt->bind_param('i', $prof_id);
        $stmt->execute();
        $result_comments = $stmt->get_result();
        $stmt->close();
        $rows_comments = mysqli_num_rows($result_comments);
        if ($rows_comments > 0) {
            for ($i = 0; $i < $rows_comments; $i++) {
                $comments = mysqli_fetch_assoc($result_comments);
                $id = $comments['id'];
                $prof_id = $comments['prof_id'];
                $stu_id = $comments['stu_id'];
                $status = $comments['status'];
                $stmt = $conn->prepare('SELECT * FROM `stu` WHERE `id` = ?');
                $stmt->bind_param('i', $stu_id);
                $stmt->execute();
                $result_data_stu = $stmt->get_result();
                $stmt->close();
                $rows_data_stu = mysqli_num_rows($result_data_stu);
                $stu = mysqli_fetch_assoc($result_data_stu);

                // deal with `prof_id_teaching`
                $seperate_prof_id_teaching = explode("-",$stu['prof_id_teaching']); //回傳指導老師ID的陣列
                $tmp = [];
                for($j=0;$j<count($seperate_prof_id_teaching);$j++)
                {
                    $get_prof_name = mysqli_query($conn,"SELECT `id`, `name` FROM `prof` WHERE `id` = '$seperate_prof_id_teaching[$j]'");
                    while ($row2=mysqli_fetch_row($get_prof_name)){
                        array_push($tmp,$row2[1]);
                    }
                }
                $prof_name = implode("、",$tmp);

?>
                        <tr>
                            <td><?php echo $stu['stu_id']; ?></td>
                            <td><?php echo $stu['name']; ?></td>
                            <td><?php echo $stu['project']; ?></td>
                            <td><?php echo $prof_name; ?></td>
                            <td style="padding: 0.5rem;">
                                <form action="query.php">
                                    <input type="hidden" name="comments_id" value="<?php echo $id; ?>">
<?php
                if ($status == 1)
                    echo '<input class="btn btn-sm btn-secondary" type="submit" value="修改">';
                else
                    echo '<input class="btn btn-sm btn-success" type="submit" value="評論">';
?>
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
            echo '<b>沒有學生需要評論。</b>';
        }
?>
            </div>
        </div>
    </div>

<?php
    }
}
else {
    require_once('login.php');
    // header("refresh: 0; url=./login.php", true, 301);
    // exit();
}

require_once('footer.php');
