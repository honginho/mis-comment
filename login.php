<?php
// session_start();
require_once('connect.php');
require_once('header.php');
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="card text-center" style="width: 100%; max-width: 400px;">
            <div class="card-header">
                國立中正大學資管所&醫管所論文提案書評論系統
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input type="text" id="account" class="form-control" name="account" placeholder="帳號" required autofocus>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <input type="password" id="password" class="form-control" name="password" placeholder="密碼" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        登入
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['account']) || trim($_POST['account']) == '' || !isset($_POST['password']) || trim($_POST['password']) == '') {
        echo '帳號密碼皆不能空白。';
    }
    else {
        $user = htmlspecialchars($_POST['account']); //將引號與雙引號轉為純顯示
        $pwd = htmlspecialchars($_POST['password']);
        $stmt = $conn->prepare("SELECT * FROM `prof` WHERE `account` = ? && `password` = ?");
        $stmt->bind_param('ss', $user, $pwd);
        $stmt->execute();
        $result = $stmt->get_result(); //$result???
        $stmt->close();
        $rows = mysqli_num_rows($result);
        $prof = mysqli_fetch_assoc($result); //從$result取得一行做關聯數據

        if ($rows == 1) {
            $_SESSION['prof_id'] = $prof['id'];
            $_SESSION['account'] = $prof['name'];
            $_SESSION['level'] = $prof['level'];
            // $_SESSION['login_user'] = $member['id'];
            // $_SESSION['account'] = $member['number'];
            // $_SESSION['auth'] = $member['authentication'];
            // $now_time = htmlspecialchars(date("Y-m-d H:i:s"));
            // $stmt = $conn->prepare("INSERT INTO login_time (name, auth, time) VALUES (?, ?, ?)");
            // $stmt->bind_param('sis', $_SESSION['account'], $_SESSION['auth'], $now_time);
            // $stmt->execute();
            // $result = $stmt->get_result();
            // $stmt->close();
        }
        else {
            echo '沒有這個人啊。';
            header("refresh: 0.5; url=./index.php", true, 301);
            exit();
        }

        mysqli_close($conn);

        header("refresh: 0; url=./index.php", true, 301);
        exit();
    }
}

require_once('footer.php');
