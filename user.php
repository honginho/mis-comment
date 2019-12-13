<?php
if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
?>
                <form class="form-inline mb-3">
                    <input class="btn btn-success" type="button" value="新增教授" onclick="userAction('add')">
                </form>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">姓名</th>
                            <th scope="col">帳號</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
        // list all professors' account
        $stmt = $conn->prepare('SELECT * FROM `prof` WHERE `level` = 1 ORDER BY `id` DESC');
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows_prof = mysqli_num_rows($result);
        if ($rows_prof > 0) {
            for ($i = 0; $i < $rows_prof; $i++) {
                $prof = mysqli_fetch_assoc($result);
                $id = $prof['id'];
                $name = $prof['name'];
                $account = $prof['account'];
?>
                        <tr>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $account; ?></td>
                            <td class="p-2">
                                <form class="p-1" onsubmit="return false;">
                                    <input class="btn btn-sm btn-warning" type="button" value="修改密碼" onclick="userAction('update', '<?php echo $id; ?>')">
                                    <input class="btn btn-sm btn-danger" type="button" value="刪除用戶" onclick="userAction('delete', '<?php echo $id; ?>')">
                                </form>
                            </td>
                        </tr>
<?php
            }
        }
        else {
            echo '<tr><td class="p-2"><form class="p-1"><input class="btn btn-sm" type="button" value="沒有資料" disabled></form></td><td></td><td></td></tr>';
        }
?>
                    </tbody>
                </table>
<?php
    }
}
else {
    header("refresh: 0; url=./index.php", true, 301);
    exit();
}