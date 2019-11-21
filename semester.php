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
                    function semesterAction(action, id = 0) {
                        if (action != '') {
                            settings = {};
                            if (action == 'add') {
                                settings.details = $('form input[name="semesterName"]').val();
                                settings.explain = '新增';

                                if (settings.details.length > 10) {
                                    Swal.fire(`${settings.explain}失敗`, '場次名稱過長(限10個數字以內)，請重新輸入。', 'error')
                                        .then(function () {
                                            $('form input[name="semesterName"]').val('');
                                            setTimeout(function () { $('form input[name="semesterName"]').focus(); }, 500);
                                        });
                                }
                                else if (settings.details == '') {
                                    Swal.fire(`${settings.explain}失敗`, '請輸入場次名稱。', 'error')
                                        .then(function () {
                                            $('form input[name="semesterName"]').val('');
                                            setTimeout(function () { $('form input[name="semesterName"]').focus(); }, 500);
                                        });
                                }
                                else {
                                    $.ajax({
                                        type: 'POST',
                                        url: 'semesterAction.php',
                                        data: { action: action, details: settings.details },
                                        success: function (data) {
                                            if (data == 'success')
                                                Swal.fire(`${settings.explain}成功`, '', 'success').then(function () { location.reload(); });
                                            else if (data == 'duplicate')
                                                Swal.fire(`${settings.explain}失敗`, '場次名稱重複，請更換。', 'error').then(function () { console.log(data); });
                                            else if (data == 'tolong')
                                                Swal.fire(`${settings.explain}失敗`, '場次名稱過長(限10個數字以內)，請重新輸入。', 'error').then(function () { console.log(data); });
                                            else if (data == 'null')
                                                Swal.fire(`${settings.explain}失敗`, '請輸入場次名稱。', 'error').then(function () { console.log(data); });
                                            else
                                                Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
                                        },
                                        error: function () {
                                            Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error');
                                        }
                                    });
                                }
                            }
                            else if (action == 'revoke' || action == 'recover') {
                                settings.details = id;
                                settings.explain = (action == 'revoke') ? '撤銷' : '復原';

                                Swal.fire({
                                    title: `確定要<u>${settings.explain}</u>這個場次嗎？`,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: '確定',
                                    cancelButtonText: '取消',
                                }).then(function (result) {
                                    if (result.value) {
                                        $.ajax({
                                            type: 'POST',
                                            url: 'semesterAction.php',
                                            data: { action: action, details: settings.details },
                                            success: function (data) {
                                                if (data == 'success')
                                                    Swal.fire(`${settings.explain}成功`, '', 'success').then(function () { location.reload(); });
                                                else
                                                    Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
                                            },
                                            error: function () {
                                                Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error');
                                            }
                                        });
                                    }
                                });
                            }
                            else if (action == 'delete') {
                                settings.details = id;
                                settings.explain = '刪除';

                                Swal.fire({
                                    title: `確定要<u>${settings.explain}</u>這個場次嗎？`,
                                    text: `請注意，確定${settings.explain}後將無法還原！`,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: '確定',
                                    cancelButtonText: '取消',
                                }).then(function (result) {
                                    if (result.value) {
                                        $.ajax({
                                            type: 'POST',
                                            url: 'semesterAction.php',
                                            data: { action: action, details: settings.details },
                                            success: function (data) {
                                                if (data == 'success')
                                                    Swal.fire(`${settings.explain}成功`, '', 'success').then(function () { location.reload(); });
                                                else
                                                    Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
                                            },
                                            error: function () {
                                                Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error');
                                            }
                                        });
                                    }
                                });
                            }
                        }
                        return false;
                    }
                </script>
                <form class="form-inline mb-3" onsubmit="return semesterAction('add');">
                    <div class="form-group mr-1 mr-sm-3">
                        <input type="text" class="form-control" name="semesterName" placeholder="限10個數字以內" required>
                    </div>
                    <div class="form-group d-inline">
                        <input class="btn btn-success" type="submit" value="新增場次">
                    </div>
                </form>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">場次</th>
                            <th scope="col">狀態</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
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
                                    <input class="btn btn-sm btn-warning" type="button" value="撤銷" onclick="semesterAction('revoke', '<?php echo "$id,$name"; ?>')">
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