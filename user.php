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
                    function userAdd() {
                        Swal.mixin({
                            input: 'text',
                            showCancelButton: true,
                            confirmButtonText: '下一步 &rarr;',
                            cancelButtonText: '取消',
                            progressSteps: ['1', '2', '3'],
                        }).queue([
                            {
                                title: '請輸入教授的姓名：',
                                text: '新增後將無法修改。',
                                inputPlaceholder: '必填',
                                inputValidator: value => {
                                    if (!value) return '這個欄位是必填！';
                                },
                            },
                            {
                                title: '請輸入教授的帳號：',
                                text: '新增後將無法修改。',
                                inputPlaceholder: '必填',
                                inputValidator: value => {
                                    if (!value) return '這個欄位是必填！';
                                },
                            },
                            {
                                title: '請輸入教授的密碼：',
                                text: '新增後可利用「修改密碼」功能進行修改。',
                                inputPlaceholder: '必填',
                                input: 'password',
                                inputValidator: value => {
                                    if (!value) return '這個欄位是必填！';
                                },
                            },
                        ]).then(result => {
                            if (result.value) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'userAction.php',
                                    data: { action: 'add', details: [result.value] },
                                    success: function (data) {
                                        if (data == 'success')
                                            Swal.fire('新增成功', '', 'success').then(function () { location.reload(); });
                                        else
                                            Swal.fire('新增失敗', '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
                                    },
                                    error: function () {
                                        Swal.fire('新增失敗', '系統出錯，請聯絡系統管理員。', 'error');
                                    }
                                });
                            }
                        });
                    }

                    function userUpdate(id) {
                        Swal.fire({
                            title: '請輸入要更新的密碼：',
                            input: 'password',
                            showCancelButton: true,
                            confirmButtonText: '確定',
                            cancelButtonText: '取消',
                            inputValidator: value => {
                                if (!value) return '這個欄位是必填！';
                            },
                        }).then(result => {
                            if (result.value) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'userAction.php',
                                    data: { action: 'update', details: [id, result.value] },
                                    success: function (data) {
                                        if (data == 'success')
                                            Swal.fire('更新成功', '', 'success').then(function () { location.reload(); });
                                        else
                                            Swal.fire('更新失敗', '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
                                    },
                                    error: function () {
                                        Swal.fire('更新失敗', '系統出錯，請聯絡系統管理員。', 'error');
                                    }
                                });
                            }
                        });
                    }

                    function userDelete(id) {
                        Swal.fire({
                            title: `確定要<u>刪除</u>這位教授嗎？`,
                            text: `請注意，確定刪除後將無法還原！`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: '確定',
                            cancelButtonText: '取消',
                        }).then(result => {
                            if (result.value) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'userAction.php',
                                    data: { action: 'delete', details: [id] },
                                    success: function (data) {
                                        if (data == 'success')
                                            Swal.fire('刪除成功', '', 'success').then(function () { location.reload(); });
                                        else
                                            Swal.fire('刪除失敗', '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
                                    },
                                    error: function () {
                                        Swal.fire('刪除失敗', '系統出錯，請聯絡系統管理員。', 'error');
                                    }
                                });
                            }
                        });
                    }

                    function userAction(action, id = 0) {
                        if (action == 'add') userAdd();
                        else if (action == 'update') userUpdate(id);
                        else if (action == 'delete') userDelete(id);
                    }
                </script>
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