<?php
// session_start();
require_once('connect.php');
require_once('header.php');
?>

    <form method="POST">
        <label for="account">帳號</label>
        <input type="text" name="account" id="account">

        <label for="password">密碼</label>
        <input type="password" name="password" id="password">

        <input type="submit" value="送出">
    </form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['account']) || trim($_POST['account']) == '' || !isset($_POST['password']) || trim($_POST['password']) == '') {
        echo '帳號密碼皆不能空白。';
    }
    else {
        $user = htmlspecialchars($_POST['account']);
        $pwd = htmlspecialchars($_POST['password']);
        $stmt = $conn->prepare("SELECT * FROM `prof` WHERE `account` = ? && `password` = ?");
        $stmt->bind_param('ss', $user, $pwd);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        $prof = mysqli_fetch_assoc($result);

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
