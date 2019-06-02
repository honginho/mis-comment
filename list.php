<?php
// session_start();
require_once('connect.php');
require_once('header.php');

if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
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
        $stmt = $conn->prepare('SELECT * FROM `comments` WHERE `status` = 0 OR `status` = 1');
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
                    echo '<input class="btn btn-sm btn-success" type="submit" value="查看評論">';
                else
                    echo '<input class="btn btn-sm btn-outline-danger" type="submit" value="尚未評論" disabled>';
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
    }
}
else {
    header("refresh: 0; url=./login.php", true, 301);
    exit();
}

require_once('footer.php');

