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
                $prof_details = $_POST['details'][0];

                if (is_array($prof_details)) {
                    $name = htmlspecialchars(trim($prof_details[0]));
                    $account = htmlspecialchars(trim($prof_details[1]));
                    $password = htmlspecialchars(trim($prof_details[2]));

                    if ($name != '' && $account != '' && $password != '') {
                        $stmt = $conn->prepare('INSERT INTO `prof` (`name`, `account`, `password`) VALUES (?, ?, ?)');
                        $stmt->bind_param('sss', $name, $account, $password);
                        $stmt->execute();
                        $stmt->close();

                        echo 'success';
                    }
                    else {
                        echo 'some/all null';
                    }
                }
                else {
                    echo '!array';
                }
            }
            else if ($action == 'update') {
                $prof_id = htmlspecialchars(trim($_POST['details'][0]));
                $prof_password = htmlspecialchars(trim($_POST['details'][1]));

                if ($prof_id != '' && $prof_password != '') {
                    $stmt = $conn->prepare('UPDATE `prof` SET `password` = ? WHERE `id` = ?');
                    $stmt->bind_param('si', $prof_password, $prof_id);
                    $stmt->execute();
                    $stmt->close();

                    echo 'success';
                }
                else {
                    echo 'some/all null';
                }
            }
            else if ($action == 'delete') {
                $prof_id = htmlspecialchars(trim($_POST['details'][0]));

                if ($prof_id != '') {
                    $stmt = $conn->prepare('DELETE FROM `prof` WHERE `id` = ?');
                    $stmt->bind_param('i', $prof_id);
                    $stmt->execute();
                    $stmt->close();

                    echo 'success';
                }
                else {
                    echo 'some/all null';
                }
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