<?php
require_once('connect.php');
require_once('header.php');
?>
<?php
if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '') {
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else {
        $prof_id = $_SESSION['prof_id'];
?>
<?php
        $comment_id_to_print = $_POST['comment_id_to_print'];
        // var_dump($comment_id_to_print); //回傳string(21) "方楷文,332,331,330"
        $comment_id = explode(",",$comment_id_to_print);
        $stu_name = $comment_id[0];
        //var_dump($comment_id); //回傳 array(4) { [0]=> string(9) "方楷文" [1]=> string(3) "332" [2]=> string(3) "331" [3]=> string(3) "330" }
        // echo $comment_id[1]; //回傳 332
        echo "<div id='div_print_all'>";
        echo "<h3 class='modal-title' style='padding-left: 15px;'>學生：$stu_name</h3></div>";
        
        for($j = 1 ; $j<count($comment_id) ; $j++) {
            $sql_comments_content = "SELECT prof_id,stu_id,comment,other_comment FROM comments WHERE id = '$comment_id[$j]' AND status = 1;";
        
            $result = $conn->query($sql_comments_content);
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // echo '教授ID:'.$row['prof_id'].'<br>';
                    // echo '學生ID:'.$row['stu_id'].'<br>';
                    // echo '評論:'.$row['comment'].'<br>'; // 0-1,0-2,1-2,1-4,2-3
                    // echo '其他評論:'.$row['other_comment'].'<br>';
        
                    $other_comment = $row['other_comment'];
                    $prof_id = $row['prof_id'];
                    $all_comment = array();
                    $all_eg_comments = $row['comment']; //一個類標題
                    $arr_all_eg_comments = explode(",", $all_eg_comments); // [0-1],[0-2],[1-2],[1-4],[2-3]
                    //print_r($arr_all_eg_comments);
                }
            }
        
            /*取得教授名字*/
            $stmt = $conn->prepare('SELECT `id`, `name` FROM `prof` WHERE `id` = ?');  
            $stmt->bind_param('i', $prof_id);
            $stmt->execute();
            $result_name_prof = $stmt->get_result();
            $stmt->close();
            $rows_name_prof = mysqli_num_rows($result_name_prof);
            $prof = mysqli_fetch_assoc($result_name_prof);
            $prof_name = $prof['name'];
            // echo '教授name:'.$prof['name'];
            
            /*取得類標題名*/
            for($i = 0 ; $i < 6 ; $i++) {   
                $main = 0;
                $sub  = 0;      //sub要從0開始，因為每個都要取得類標題，再塞進all_comment裡
                $stmt = $conn->prepare('SELECT `name` FROM `comments_codes` WHERE `main` = ? && `sub` = ?');
                $stmt->bind_param('ii', $i, $sub);      //main=0,sub=0 取類標題
                $stmt->execute();
                $result_class_name = $stmt->get_result();
                $stmt->close();
                $class_name = mysqli_fetch_assoc($result_class_name);
                $all_comment[] = $class_name;
            }
        
            /*創建評論代碼陣列，藉此求得評論名稱，並加進$all_comment[]裡*/
            for ($i = 0 ; $i < count($arr_all_eg_comments) ; $i++) {
                $a_comment = explode("-", $arr_all_eg_comments[$i]);
                //   echo "<pre>";
                //   print_r ($a_comment); //一次回傳一組評論代碼Array[0] = 0 ,[1] = 1 ; Array[0] = 0 [1] = 2 
                //   echo "</pre>";
                $stmt = $conn->prepare('SELECT `name` FROM `comments_codes` WHERE `main` = ? && `sub` = ?');
                $stmt->bind_param('ii',$a_comment[0],$a_comment[1]); //main = a_comment[0], sub = $a_comment[1]   
                $stmt->execute();
                $result_sub_name = $stmt->get_result();
                $stmt->close();
                $sub_name = mysqli_fetch_assoc($result_sub_name);
                $comment_main = $a_comment[0];
                switch($comment_main) {
                    case 0:
                        $all_comment[0]['comment'] = array();
                        array_push($all_comment[0]['comment'],$sub_name['name']); break;
                    case 1:
                        $all_comment[1]['comment'] = array();
                        array_push($all_comment[1]['comment'],$sub_name['name']); break;
                    case 2:
                        $all_comment[2]['comment'] = array();
                        array_push($all_comment[2]['comment'],$sub_name['name']); break;
                    case 3:
                        $all_comment[3]['comment'] = array();
                        array_push($all_comment[3]['comment'],$sub_name['name']); break;
                    case 4:
                        $all_comment[4]['comment'] = array();
                        array_push($all_comment[4]['comment'],$sub_name['name']); break;
                    case 5:
                        $all_comment[5]['comment'] = array();
                        array_push($all_comment[5]['comment'],$sub_name['name']); break;
                }
            }
        
            /*新增其他評論*/
            $all_other_comment = explode("@,|,@",$other_comment);
            for($i = 0 ; $i < count($all_other_comment) ; $i++) {
                $a_other_comment = explode("@-|-@",$all_other_comment[$i]);
                // echo "<pre>";
                // print_r($a_other_comment); //回傳Array[0] = 3,[1] = 金聲測試;[0]是main,要push[1]$all_comment裡
                // echo "</pre>";
                $other_comment_main = $a_other_comment[0];
                switch($other_comment_main) {
                    case 0:
                        $all_comment[0]['other_comment'] = array();
                        array_push($all_comment[0]['other_comment'],$a_other_comment[1]); break;
                    case 1:
                        $all_comment[1]['other_comment'] = array();
                        array_push($all_comment[1]['other_comment'],$a_other_comment[1]); break;
                    case 2:
                        $all_comment[2]['other_comment'] = array();
                        array_push($all_comment[2]['other_comment'],$a_other_comment[1]); break;
                    case 3:
                        $all_comment[3]['other_comment'] = array();
                        array_push($all_comment[3]['other_comment'],$a_other_comment[1]); break;
                    case 4:
                        $all_comment[4]['other_comment'] = array();
                        array_push($all_comment[4]['other_comment'],$a_other_comment[1]); break;
                    case 5:
                        $all_comment[5]['other_comment'] = array();
                        array_push($all_comment[5]['other_comment'],$a_other_comment[1]); break;
                }
            }
        
            // $all_comment 處理好了！！！
            // $all_comment 處理好了！！！
            // $all_comment 處理好了！！！
        
            $print_content = "<div class='modal-content'>";
        
            $print_content .= "<div class='modal-header'><h5 class='modal-title'>$prof_name</h5></div>";
        
            for ($i = 0 ; $i < count($all_comment) ; $i++) {
                /*新增大項目name*/
                $name = $all_comment[$i]["name"];
                $print_content .= "<div class='modal-body'><div class='form-group col-md-12'>";
                $print_content .= "    <label style='display: block;'>$name</label>";
        
                /*新增子項目comment*/
                if (array_key_exists('comment', $all_comment[$i])) {
                    for($k = 0 ; $k < count($all_comment[$i]["comment"]) ; $k++) {
                        $comment = $all_comment[$i]["comment"][$k];
                        $print_content .= "    <div class='comments-eg'>";
                        $print_content .= "        <p class='btn text-left' style='display: inline-block; background-color: #dc3545; border: 1px solid #dc3545; color: white; font-weight: bold; -webkit-user-select: auto; -moz-user-select: auto; -ms-user-select: auto; user-select: auto;' for='comments-0-1'>$comment</p>";
                        $print_content .= "    </div>";
                    }
                }
                if (array_key_exists('other_comment', $all_comment[$i])) {
                    $other_comment = $all_comment[$i]["other_comment"][0];
                    $print_content .="          <div class='form-group'>";
                    $print_content .="              <textarea class='form-control' name='other_comment[]' id='other-comment-3' rows='3' readonly=''>$other_comment</textarea>";
                    $print_content .="          </div>";
                }
                $print_content .= "</div></div>";
            }
        
            $print_content .= "</div>";
        
            echo "<div class='modal-body'><form id='form-check-comments'><div class='form-row'>$print_content</div></form></div>";
            // echo "<pre>";
            // print_r($all_comment);
            // echo "</pre>";  
        }
        
        echo "</div>";
        
        echo "<script>
        
        function printdiv_all(printpage)
        {
            // var headstr = '<html><head><title></title></head><body>';
            // var footstr = '</body>';
            // var newstr = document.all.item(printpage).innerHTML;
            // document.body.innerHTML = headstr+newstr+footstr;
            window.print();
            console.log('print la');
        } //列印
        
        $(document).ready(function(printpage) {
            printdiv_all();    
        });
        
        </script>";
    }
}
?>
<?php
require_once('footer.php');
?>
