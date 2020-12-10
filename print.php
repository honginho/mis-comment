<?php
require_once('connect.php');
require_once('mycss.php');
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
        
        //TODO: 過濾尚未評論的教授 get `status` by `id`,if (1 or 1 or 1){if(!empty($arr_all_eg_comments))} else $print_content = 尚無資料

        $comment_id_to_print = $_POST['comment_id_to_print'];
        // var_dump($comment_id_to_print); //回傳string(21) "方楷文,332,331,330"
        $comment_id = explode(",",$comment_id_to_print);
        $stu_name = $comment_id[0];
        // var_dump($comment_id); //回傳 array(4) { [0]=> string(9) "方楷文" [1]=> string(3) "332" [2]=> string(3) "331" [3]=> string(3) "330" }
        // echo $comment_id[1]; //回傳 332
        
        $all_comment = array();
        for($k = 1 ; $k<count($comment_id) ; $k++) {
            $stmt = $conn->prepare('SELECT `status`,`other_comment` FROM `comments` WHERE `id` = ?');  //抓是否評論的狀態
            $stmt->bind_param('i', $comment_id[$k]);
            $stmt->execute();
            $result_status = $stmt->get_result();
            $stmt->close();
            $rows_status = mysqli_num_rows($result_status);
            $status = mysqli_fetch_assoc($result_status);
            $commented = $status['status'];

            $stmt = $conn->prepare('SELECT `other_comment` FROM `comments` WHERE `id` = ?');  //抓是否有其他評論
            $stmt->bind_param('i', $comment_id[$k]);
            $stmt->execute();
            $result_other_comment = $stmt->get_result();
            $stmt->close();
            $rows_other_comment = mysqli_num_rows($result_status);
            $other_comment = mysqli_fetch_assoc($result_status);

            $arr_all_eg_comments = array();
            if($commented == 1){
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
                        $a_sub_name = explode(" ",$sub_name['name']);
                        array_push($all_comment,$a_sub_name[1]);
                    }
                    if(!empty($other_comment)){
                        /*新增其他評論*/
                        $all_other_comment = explode("@,|,@",$other_comment);
                        for($i = 0 ; $i < count($all_other_comment) ; $i++) {
                            $a_other_comment = explode("@-|-@",$all_other_comment[$i]);
                            // echo "<pre>";
                            // print_r($a_other_comment); //回傳Array[0] = 3,[1] = 金聲測試;[0]是main,要push[1]$all_comment裡
                            // echo "</pre>";
                            array_push($all_comment,$a_other_comment[1]);
                        }
                    }
                }
                // $all_comment 處理好了！！！
                $uniquearr = array_unique($all_comment);   //刪去重複的評論
                $new_uniquearr = array_values($uniquearr); //刷新index
                // echo "<pre>";
                // print_r($new_uniquearr);
                // echo "</pre>";
                // echo count($new_uniquearr);
            }
        }

        //檢查是否已經有老師評論過
        if(empty($new_uniquearr)){ 
            echo"<script>
            alert('尚無任何老師評論')
            window.location.replace('index.php')
            </script>";
        }

        $print_content = "<div>".$stu_name."同學您好："."<br>"."
        &nbsp&nbsp&nbsp本屆碩士論文提案發表會在大家的支持下己圓滿完成。資管所全體老師均對同學們在論文研究上的努力深感欣慰。
        "."<br>"."&nbsp&nbsp&nbsp經過評審委員討論後，認為您的研究題目是「有條件通過」，下列為評審委員針對您論文所提之建議事項，請於
        ____年____月____日(週____)____午____點前，將更正之書面資料繳至系辦公室，以利進行複試：";
        $print_content .= "<ol>";

        for($i = 0 ; $i < count($new_uniquearr) ; $i++) {
            $print_content .= "<li>$new_uniquearr[$i]</li>";
        }
        $print_content .= "</ol>
                        </div>";

        if(!empty($new_uniquearr)){ //若都沒有任何評論就不印
            $print_content .= "<span style='font-size: 30px; margin-left: 140px;' >資訊管理學系暨研究所主任 阮金聲</span>"."<br>";
            $print_content .= "<span style='font-size: 24px; text-align:center; display:block; margin-top: 20px; letter-spacing: 10px;' >中華民國&nbsp&nbsp&nbsp年&nbsp&nbsp&nbsp月&nbsp&nbsp&nbsp日</span>";
            echo $print_content;
        }
        
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
