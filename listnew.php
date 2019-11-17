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
                <form class="form-inline mb-3" action="listnew.php" method="GET"> <!-- 查詢全部的表單 -->
                    <div class="form-group mr-1 mr-sm-3">
                        <input type="text" class="form-control" name="condition_all" placeholder="請輸入關鍵字" autofocus>
                    </div>
                    <div class="form-group" style="display:inline;">
                        <input class="btn btn-success" type="submit" value="查詢全部">
<?php if (isset($_GET['condition_all']) && trim($_GET['condition_all']) != ''): ?>
                        <input type="button" class="btn btn-light" value="查詢結果：<?php echo $_GET['condition_all']; ?>" disabled>
<?php endif; ?>
                    </div>
                </form>
                
                <form action="upload.php" method="post" enctype="multipart/form-data" style="margin-bottom: 10px;"> <!-- 上傳檔案的表單 -->
                    檔案名稱:<input type="file" name="file" id="file"/>
                    <input class="btn btn-primary" type="submit" name="submit" value="上傳檔案"/>
                </form>
<?php
        if (isset($_GET['condition_all']) && trim($_GET['condition_all']) != '') { //若搜尋框內有接收到東西
            $all = htmlspecialchars($_GET['condition_all']); //將搜尋關鍵字化成單純可讀

            $stmt = $conn->prepare('SELECT `id`, `name` FROM `stu` WHERE `name` LIKE CONCAT("%", ?, "%")'); //模糊查詢
            $stmt->bind_param('s', $all);
            $stmt->execute();
            $result_stu = $stmt->get_result();
            // var_dump($result_stu);
            // echo '<br>';

            $stmt = $conn->prepare('SELECT `id`, `name` FROM `prof` WHERE `name` LIKE CONCAT("%", ?, "%")');
            $stmt->bind_param('s', $all);
            $stmt->execute();
            $result_prof = $stmt->get_result();
            // var_dump($result_prof);
            // $stmt->close();

            $rows_prof = mysqli_num_rows($result_prof);
            $rows_stu =  mysqli_num_rows($result_stu);
            if($rows_prof + $rows_stu > 0){
                if($rows_prof > 0){
                    $condition = "";
                    for($i = 0; $i < $rows_prof;$i++){
                        $data_prof = mysqli_fetch_assoc($result_prof);
                        $split = ($i == 0) ? '' : ' OR ';
                        $condition .= $split .'`prof_id` = '.$data_prof['id'];
                    }
                }
                if($rows_stu > 0){
                    $condition = "";
                    for($i = 0; $i < $rows_stu;$i++){
                        $data_stu = mysqli_fetch_assoc($result_stu);
                        $condition .='`stu_id` = '.$data_stu['id'];
                    }
                }
                $stmt = $conn->prepare('SELECT * FROM `comments` WHERE (`status` = 0 OR `status` = 1) AND ('.$condition.')');
                $stmt->execute();
            }
            else {
                echo '<b>查無結果。</b>';
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
                            <th scope="col">評論教授</th>
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

                // 抓教授姓名
                $stmt = $conn->prepare('SELECT `id`, `name` FROM `prof` WHERE `id` = ?');
                $stmt->bind_param('i', $prof_id);
                $stmt->execute();
                $result_name_prof = $stmt->get_result();
                $stmt->close();
                // $rows_name_prof = mysqli_num_rows($result_name_prof);
                $prof = mysqli_fetch_assoc($result_name_prof);

                // 抓學生資料
                $stmt = $conn->prepare('SELECT * FROM `stu` WHERE `id` = ?');
                $stmt->bind_param('i', $stu_id);
                $stmt->execute();
                $result_data_stu = $stmt->get_result();
                $stmt->close();
                // $rows_data_stu = mysqli_num_rows($result_data_stu);
                $stu = mysqli_fetch_assoc($result_data_stu);
?>
                        <tr>
                            <td><?php echo $stu['name']; ?></td>
                            <td><?php echo $prof['name']; ?></td>
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

