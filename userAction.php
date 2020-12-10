<?php
session_start();
require_once('connect.php');

if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (!isset($_SESSION['level'])) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
        if (isset($_POST['action']) && isset($_POST['details'])) {
            $action = htmlspecialchars(trim($_POST['action']));

            // add professor
            if ($action == 'add' && $_SESSION['level'] == 0) {
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
            // update professor's password
            else if ($action == 'update') {
                $prof_id = htmlspecialchars(trim($_POST['details'][0]));
                $prof_password = htmlspecialchars(trim($_POST['details'][1]));
                if($_SESSION['level'] == 0){    //管理員可直接改
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
                else{                           
                    if($prof_id == $_SESSION['prof_id']){ //檢查前端傳回的ID是否等於教授ID
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
                    else{
                        echo '
                        <script type="text/javascript">
                        $(document).ready(function(){
                            Swal.fire("更新失敗", "系統出錯，請聯絡系統管理員。", "error");
                        });

                        </script>
                        ';
                        
                    }
                }
                
            }
            // delete account (delete professor)
            else if ($action == 'delete' && $_SESSION['level'] == 0) {
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