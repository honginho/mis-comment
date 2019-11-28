<?php
session_start();
require_once('connect.php');

if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
        if (isset($_POST['action']) && isset($_POST['details'])) {
            $action = htmlspecialchars(trim($_POST['action']));
            if ($action == 'add') {
                $semester_name = htmlspecialchars(trim($_POST['details']));

                if ($semester_name != '') {
                    if (strlen($semester_name) <= 10) {
                        $stmt = $conn->prepare('SELECT * FROM `semester` WHERE `name` = ?');
                        $stmt->bind_param('i', $semester_name);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $stmt->close();

                        if (mysqli_num_rows($result) == 0) {
                            $stmt = $conn->prepare('INSERT INTO `semester` (`name`) VALUES (?)');
                            $stmt->bind_param('i', $semester_name);
                            $stmt->execute();
                            $stmt->close();

                            echo 'success';
                        }
                        else {
                            echo 'duplicate';
                        }
                    }
                    else {
                        echo 'tolong';
                    }
                }
                else {
                    echo 'null';
                }
            }
            else if ($action == 'revoke') {
                $semester_id = explode(',', htmlspecialchars(trim($_POST['details'])))[0];
                $semester_name = explode(',', htmlspecialchars(trim($_POST['details'])))[1];

                $stmt = $conn->prepare('UPDATE `semester` SET `status` = 0 WHERE `id` = ?');
                $stmt->bind_param('i', $semester_id);
                $stmt->execute();
                $stmt->close();

                // $stmt = $conn->prepare('UPDATE `comments` SET `status` = `status` - 2 WHERE `semester` = ?');
                // $stmt->bind_param('i', $semester_name);
                // $stmt->execute();
                // $stmt->close();

                echo 'success';
            }
            else if ($action == 'recover') {
                $semester_id = explode(',', htmlspecialchars(trim($_POST['details'])))[0];
                $semester_name = explode(',', htmlspecialchars(trim($_POST['details'])))[1];

                $stmt = $conn->prepare('UPDATE `semester` SET `status` = 1 WHERE `id` = ?');
                $stmt->bind_param('i', $semester_id);
                $stmt->execute();
                $stmt->close();

                // $stmt = $conn->prepare('UPDATE `comments` SET `status` = `status` + 2 WHERE `semester` = ?');
                // $stmt->bind_param('i', $semester_name);
                // $stmt->execute();
                // $stmt->close();

                echo 'success';
            }
            else if ($action == 'delete') {
                $semester_id = htmlspecialchars(trim($_POST['details']));

                $stmt = $conn->prepare('DELETE FROM `semester` WHERE `id` = ?');
                $stmt->bind_param('i', $semester_id);
                $stmt->execute();
                $stmt->close();

                echo 'success';
            }
        }
        else {
            echo 'Error-1';
        }
    }
}
else {
    echo 'Error-2';
}