<?php
require_once('connect.php');
require_once('header.php');

if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] == 0) { //管理員
        header("refresh: 0; url=./list.php", true, 301); //跳轉頁面，會多停頓在此php頁面
        exit();
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
        $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `prof_id` = ? AND (`status` = 0 OR `status` = 1)');
        $stmt->bind_param('i', $prof_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        if ($rows > 0) {
?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">學生</th>
                            <th scope="col">學號</th>
                            <th scope="col">論文名稱</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
            for ($i = 0; $i < $rows; $i++) {
                $comments = mysqli_fetch_assoc($result);
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
?>
                        <tr>
                            <td><?php echo $stu['name']; ?></td>
                            <td><?php echo $stu['stu_id']; ?></td>
                            <td><?php echo $stu['project']; ?></td>
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
                <!-- <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">學生</th>
                            <th scope="col">學號</th>
                            <th scope="col">論文名稱</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">1</th>
                            <td>Mark</td>
                            <td>Otto</td>
                            <td>@mdo</td>
                        </tr>
                        <tr>
                            <th scope="row">2</th>
                            <td>Jacob</td>
                            <td>Thornton</td>
                            <td>@fat</td>
                        </tr>
                        <tr>
                            <th scope="row">3</th>
                            <td>Larry</td>
                            <td>the Bird</td>
                            <td>@twitter</td>
                        </tr>
                    </tbody>
                </table> -->
            </div>
        </div>
    </div>

<?php
        // if ($_SESSION['level'] == 0)
        //     echo '嗨！ <b>' . $prof_name . '</b>管理員～<br>';
        // else
        //     echo '嗨！ <b>' . $prof_name . '</b>教授～<br>';

        // // 取得這個教授可以去評論的學生
        // //   > `status` == 0: 沒有被評論過的學生
        // //   > `status` == 1: 已經被評論過的學生
        // //   > `status` == 2: 不能被評論的學生(因為不是這一梯次的)
        // $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `prof_id` = ? AND (`status` = 0 OR `status` = 1)');
        // $stmt->bind_param('i', $prof_id);
        // $stmt->execute();
        // $result = $stmt->get_result();
        // $stmt->close();
        // $rows = mysqli_num_rows($result);
        // if ($rows > 0) {
        //     for ($i = 0; $i < $rows; $i++) {
        //         echo '<div style="margin-bottom: 1em; border: 1px solid red;">';
        //         $comments = mysqli_fetch_assoc($result);
        //         // var_dump($comments); die();
        //         $id = $comments['id'];
        //         $prof_id = $comments['prof_id'];
        //         $stu_id = $comments['stu_id'];
        //         $status = $comments['status'];
        //         // echo 'comments_id: ' . $id . '<br>';
        //         // echo 'prof_id: ' . $prof_id . '<br>';
        //         // echo 'stu_id: ' . $stu_id . '<br>';
        //         $stmt = $conn->prepare('SELECT * FROM `stu` WHERE `id` = ?');
        //         $stmt->bind_param('i', $stu_id);
        //         $stmt->execute();
        //         $result_data_stu = $stmt->get_result();
        //         $stmt->close();
        //         $rows_data_stu = mysqli_num_rows($result_data_stu);
        //         $stu = mysqli_fetch_assoc($result_data_stu);
        //         echo '學生: ' . $stu['name'] . '<br>';
        //         echo '學號: ' . $stu['stu_id'] . '<br>';
        //         echo '論文名稱: ' . $stu['project'] . '<br>';
        //         echo '<form action="query.php">';
        //         echo '<input type="hidden" name="comments_id" value="' . $id . '">';
        //         echo '<input type="submit" value="去評論">';
        //         if ($status == 1) echo '<b>(已評論過)</b>';
        //         echo '</form>';
        //         echo '</div>';
        //     }
        // }
        // else {
        //     // $_SESSION['error'] = 1;
        //     echo '<br>沒有學生需要評論。';
        // }
    }
}
else {
    header("refresh: 0; url=./login.php", true, 301);
    exit();
}

require_once('footer.php');
