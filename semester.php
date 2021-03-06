<?php
if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
?>
                <div class="alert alert-primary" role="alert">
                    狀態開放：教授可以評論。
                    <br>
                    狀態關閉：教授無法評論，只有管理員可看見資料。
                </div>
                <form class="form-inline mb-3" onsubmit="return semesterAction('add');">
                    <div class="form-group mr-1 mr-sm-3">
                        <input type="text" class="form-control" name="semesterName" placeholder="限10個數字以內" required>
                    </div>
                    <div class="form-group d-inline">
                        <input class="btn btn-success" type="submit" value="新增梯次">
                    </div>
                </form>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">梯次</th>
                            <th scope="col">狀態</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
        // list all semester
        $stmt = $conn->prepare('SELECT * FROM `semester`');
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows_semester = mysqli_num_rows($result);
        if ($rows_semester > 0) {
            for ($i = 0; $i < $rows_semester; $i++) {
                $semester = mysqli_fetch_assoc($result);
                $id = $semester['id'];
                $name = $semester['name'];
                $status = $semester['status'];
?>
                        <tr>
                            <td><?php echo $name; ?></td>
                            <td><?php echo ($status) ? '開放' : '關閉'; ?></td>
                            <td class="p-2">
                                <form class="p-1" onsubmit="return false;">
<?php if ($status): ?>
                                    <input class="btn btn-sm btn-warning" type="button" value="取消" onclick="semesterAction('revoke', '<?php echo "$id,$name"; ?>')">
<?php else: ?>
                                    <input class="btn btn-sm btn-primary" type="button" value="復原" onclick="semesterAction('recover', '<?php echo "$id,$name"; ?>')">
                                    <input class="btn btn-sm btn-danger" type="button" value="刪除" onclick="semesterAction('delete', '<?php echo $id; ?>')">
<?php endif; ?>
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