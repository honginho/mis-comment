function getFileName() {
    let filePath = $('#file').val(); // get the file name
    let fileName = filePath.split('\\'); // get the file name
    $('#file').next('.custom-file-label').html(fileName[fileName.length - 1]); // replace the "Choose a file" label
}

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


function semesterAction(action, id = 0) {
    if (action != '') {
        if (action == 'add') semesterAdd();
        else if (action == 'revoke' || action == 'recover') semesterRevokeRecover(id, action);
        else if (action == 'delete') semesterDelete(id);
    }

    return false;
}

function semesterAdd() {
    settings.details = $('form input[name="semesterName"]').val();
    settings.explain = '新增';

    if (settings.details.length > 10) { // foolproof
        Swal.fire(`${settings.explain}失敗`, '梯次名稱過長(限10個數字以內)，請重新輸入。', 'error')
            .then(function () {
                $('form input[name="semesterName"]').val('');
                setTimeout(function () { $('form input[name="semesterName"]').focus(); }, 500);
            });
    }
    else if (settings.details == '') { // foolproof
        Swal.fire(`${settings.explain}失敗`, '請輸入梯次名稱。', 'error')
            .then(function () {
                $('form input[name="semesterName"]').val('');
                setTimeout(function () { $('form input[name="semesterName"]').focus(); }, 500);
            });
    }
    else {
        $.ajax({
            type: 'POST',
            url: 'semesterAction.php',
            data: { action: 'add', details: settings.details },
            success: function (data) {
                if (data == 'success')
                    Swal.fire(`${settings.explain}成功`, '', 'success').then(function () { location.reload(); });
                else if (data == 'duplicate')
                    Swal.fire(`${settings.explain}失敗`, '梯次名稱重複，請更換。', 'error').then(function () { console.log(data); });
                else if (data == 'tolong')
                    Swal.fire(`${settings.explain}失敗`, '梯次名稱過長(限10個數字以內)，請重新輸入。', 'error').then(function () { console.log(data); });
                else if (data == 'null')
                    Swal.fire(`${settings.explain}失敗`, '請輸入梯次名稱。', 'error').then(function () { console.log(data); });
                else
                    Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
            },
            error: function () {
                Swal.fire(`${settings.explain}失敗`, '系統出錯，請聯絡系統管理員。', 'error');
            }
        });
    }
}

function semesterDelete(id) {
    settings.details = id;
    settings.explain = '刪除';

    Swal.fire({
        title: `確定要<u>${settings.explain}</u>這個梯次嗎？`,
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
                data: { action: 'delete', details: settings.details },
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

function semesterRevokeRecover(id, action) {
    settings.details = id;
    settings.explain = (action == 'revoke') ? '取消' : '復原';

    Swal.fire({
        title: `確定要<u>${settings.explain}</u>這個梯次嗎？`,
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

function uploadFile() {
    $.ajax({
        type: 'POST',
        url: 'upload.php',
        cache: false,
        data: new FormData($('#uploadForm')[0]),
        processData: false,
        contentType: false,
        success: function (data) {
            if (data == 'success')
                Swal.fire('上傳成功', '', 'success').then(function () { location.reload(); });
            else
                Swal.fire('上傳失敗', '系統出錯，請聯絡系統管理員。', 'error').then(function () { console.log(data); });
        },
        error: function () {
            Swal.fire('上傳失敗', '系統出錯，請聯絡系統管理員。', 'error');
        }
    });

    return false;
}

function userAction(action, id = 0) {
    if (action == 'add') userAdd();
    else if (action == 'update') userUpdate(id);
    else if (action == 'delete') userDelete(id);
}

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