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
    <script>
        function getFileName() {
            let filePath = $('#file').val(); // get the file name
            let fileName = filePath.split('\\'); // get the file name
            $('#file').next('.custom-file-label').html(fileName[fileName.length-1]); // replace the "Choose a file" label
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
    </script>

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
                <nav>
                    <div class="nav nav-tabs" role="tablist">
                        <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">所有資料</a>
                        <a class="nav-item nav-link" id="nav-semester-tab" data-toggle="tab" href="#nav-semester" role="tab" aria-controls="nav-semester" aria-selected="false">場次列表</a>
                        <a class="nav-item nav-link" id="nav-upload-tab" data-toggle="tab" href="#nav-upload" role="tab" aria-controls="nav-upload" aria-selected="false">上傳提案</a>
                        <a class="nav-item nav-link" id="nav-user-tab" data-toggle="tab" href="#nav-user" role="tab" aria-controls="nav-user" aria-selected="false">管理用戶</a>
                    </div>
                </nav>
                <div class="tab-content mt-3">
                    <style>
                        .custom-file-label::after {
                            content: '瀏覽';
                        }
                    </style>
                    <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
                        <?php include_once('list.php'); ?>
                    </div>
                    <div class="tab-pane fade" id="nav-semester" role="tabpanel" aria-labelledby="nav-semester-tab">
                        <?php include_once('semester.php'); ?>
                    </div>
                    <div class="tab-pane fade" id="nav-upload" role="tabpanel" aria-labelledby="nav-upload-tab">
                        <form id="uploadForm" class="d-flex" enctype="multipart/form-data" onsubmit="return uploadFile();">
                            <select name="targetSemester" class="custom-select" style="max-width: 110px; min-width: 110px;" required>
                                <option value="">選擇學期</option>
<?php
        $stmt = $conn->prepare('SELECT * FROM `semester` WHERE `status` = 1');
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        if ($rows > 0) {
            for ($i = 0; $i < $rows; $i++) {
                $semester = mysqli_fetch_assoc($result);
                $id = $semester['id'];
                $name = $semester['name'];
                echo '<option value="' . $name . '">' . $name . '</option>';
            }
        }
        else {
            echo '<option value="">無</option>';
        }
?>
                            </select>

                            <div class="custom-file mx-4">
                                <input type="file" class="custom-file-input" name="file" id="file" accept=".xlsx" required onchange="getFileName()">
                                <label class="custom-file-label" for="file">選擇檔案</label>
                            </div>

                            <input class="btn btn-success" type="submit" name="submit" value="檔案上傳"/>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="nav-user" role="tabpanel" aria-labelledby="nav-user-tab">
                        <?php include_once('user.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
    }
}
else {
    header("refresh: 0; url=./login.php", true, 301);
    exit();
}

require_once('footer.php');

