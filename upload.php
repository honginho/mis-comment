<?php
session_start();
require_once('connect.php');

if (isset($_SESSION['prof_id']) && trim($_SESSION['prof_id'] ) != '')
{
    if (isset($_SESSION['level']) && $_SESSION['level'] != 0) {
        echo '你不是管理員。';
        header("refresh: 0.5; url=./index.php", true, 301);
        exit();
    }
    else 
    {
        if ($_FILES["file"]["error"] > 0)
        {
        　die ("Error: " . $_FILES["file"]["error"]);
        }

        require_once "Classes/PHPExcel.php";
        $tmp_name = $_FILES["file"]["tmp_name"];
        $excelReader = PHPExcel_IOFactory::createReaderForFile($tmp_name);  //讀取excel檔案
        $excelObj = $excelReader->load($tmp_name);                          //檔案名稱
        $sheet = $excelObj->getActiveSheet();                               //取得第一個資料表；Active用在只有一個，若有多個改getSheet(index)
        $lastRow = $sheet->getHighestRow();                                 //取得總行數(橫)

        $target_semester = htmlspecialchars($_POST['targetSemester']);

        $stmt = $conn->prepare('SELECT * FROM `semester` WHERE `status` = 1 AND `name` = ?');
        $stmt->bind_param('s', $target_semester);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rows = mysqli_num_rows($result);
        if ($rows == 0)
            die('請確認檔案內的學期是否相同且與上傳前選擇的學期相同。');

        // echo"<table>";
        // for($row = 2; $row <= $lastRow; $row++){                //從第3列開始顯示
        //     if(trim($sheet->getCellByColumnAndRow(4, $row)->getValue())!=""){                    
        //     echo"<tr><td>";
        //     echo $sheet->getCell('C'.$row)->getValue();         //AB時間日期不用印
        //     echo"</td><td>";
        //     echo $sheet->getCell('D'.$row)->getValue();
        //     echo"</td><td>";
        //     echo $sheet->getCell('E'.$row)->getValue();
        //     echo"</td><td>";
        //     echo $sheet->getCell('F'.$row)->getValue();
        //     echo"</td><td>";
        //     echo $sheet->getCell('G'.$row)->getValue();
        //     echo"</td><td>";
        //     echo $sheet->getCell('H'.$row)->getValue();
        //     echo"</td><td>";
        //     echo $sheet->getCell('I'.$row)->getValue();
        //     echo"</td><td>";
        //     echo $sheet->getCell('J'.$row)->getValue();
        //     echo"</td><td>";
        //     echo $sheet->getCell('K'.$row)->getValue();
        //     echo"</td></tr>";
        //     }
        // }
        // echo"</table>";

        $lastRow = $sheet->getHighestRow(); //取得總行數(橫)

        // TODO: check if `prof` exist and `stu` not exist

        for($row = 3; $row <= $lastRow; $row++)
        {
            if(trim($sheet->getCellByColumnAndRow(4, $row)->getValue())!="" && trim($sheet->getCellByColumnAndRow(4, $row)->getValue())!="撤銷場次") //忽略論文為空白值
            {  
                $STU_ID = $sheet->getCell('C'.$row)->getValue();           //學號
                $NAME = $sheet->getCell('D'.$row)->getValue();             //學生名字
                $PROJECT = $sheet->getCell('E'.$row)->getValue();          //論文名稱
                $PROF = $sheet->getCell('F'.$row)->getValue();             //指導老師
                $PROF1 = trim($sheet->getCell('G'.$row)->getValue(),"*");
                $PROF2 = trim($sheet->getCell('H'.$row)->getValue(),"*");
                $PROF3 = trim($sheet->getCell('I'.$row)->getValue(),"*");
                $PROF4 = trim($sheet->getCell('J'.$row)->getValue(),"*");
                $PROF5 = trim($sheet->getCell('K'.$row)->getValue(),"*");  //與會老師們
                
                // TODO: prepare statement
                
                $get_prof_teaching= explode("、", $PROF); //分割指導老師 回傳陣列
                $arr = [];                                //ID存進陣列
                foreach($get_prof_teaching as $prof_name)
                {
                    $get_prof_teaching_id =mysqli_query($conn,"SELECT `id`,`name` FROM `prof` WHERE `name` = '$prof_name'");
                    $row2=mysqli_fetch_array($get_prof_teaching_id,MYSQLI_NUM);
                    
                    //echo '<br>';
                    //var_dump($row2);                     //回傳array [0]:指導老師ID [1]:指導老師名字
                    array_push($arr,$row2[0]);
                    //echo '<br>';
                    //var_dump($a);
                    $prof_id_teaching = implode("-",$arr);
                    echo $prof_id_teaching."<br>";
                }
                
                //新增新同學資料
                $add = "INSERT INTO `stu` (name, stu_id, dept, project,prof_id_teaching)
                        SELECT * FROM (SELECT '$NAME' , '$STU_ID', 0, '$PROJECT','$prof_id_teaching') AS tmp
                        WHERE NOT EXISTS ( SELECT * FROM `stu` WHERE stu_id = '$STU_ID' OR project = '$PROJECT') LIMIT 1";
                        //過濾掉學號、論文名稱相同的資料以及論文是撤銷場次或空白
                        //https://stackoverflow.com/questions/3164505/mysql-insert-record-if-n-exists-in-table
                //echo $add."<br>";
                mysqli_query($conn,$add);
                // TODO: 印出有重複的給管理員看

                //新增進comments資料表 (下面這一坨還沒好Q)
                $get_stu_id = mysqli_query($conn,"SELECT `id`,`stu_id` FROM `stu` WHERE `stu_id` = '$STU_ID'");
                $row_stu_id;
                $row_prof_id = [];                                  //存放所有可評論老師
                while ($row3=mysqli_fetch_row($get_stu_id))         //當有抓到學生資料時
                {
                    foreach($get_prof_teaching as $prof_name)   
                    {
                        echo "教授名字:".$prof_name."<br>";
                        array_push($row_prof_id,$prof_name);        //將指導老師放進評論老師列中
                    }
                    array_push($row_prof_id,$PROF1);
                    array_push($row_prof_id,$PROF2);
                    array_push($row_prof_id,$PROF3);
                    array_push($row_prof_id,$PROF4);
                    array_push($row_prof_id,$PROF5);

                    $row_stu_id = $row3[0];
                    echo "學生ID:".$row3[0]."<br>";
                    echo "學生學號:".$row3[1]."<br>";
                    for($i=0;$i< count($row_prof_id);$i++){
                        $row_allprof_id = mysqli_query($conn,"SELECT `id`,`name` FROM `prof` WHERE `name` = '$row_prof_id[$i]'");
                        while ($row4=mysqli_fetch_row($row_allprof_id)){
                            $addcomment =
                            "INSERT INTO `comments`(semester,prof_id,stu_id) VALUES(10806,$row4[0],$row_stu_id)
                            -- // ON DUPLICATE KEY 
                            -- // UPDATE `stu_id`=$row3[0]";

                            // "INSERT IGNORE INTO `comments`(semester,prof_id,stu_id) VALUES(10806,$row4[0],$row_stu_id)";
                            
                            // "INSERT INTO `comments` (semester,prof_id,stu_id)
                            // SELECT * FROM (SELECT 10806,$row4[0],$row_stu_id) AS tmp
                            // WHERE NOT EXISTS ( SELECT * FROM `stu` WHERE stu_id = '$row3[0]') LIMIT 1";
                                
                            // "INSERT INTO `comments`(semester,prof_id,stu_id)
                            // SELECT 10806,$row4[0],$row_stu_id FROM DUAL WHERE $row_stu_id
                            // NOT IN (SELECT stu_id FROM `stu`)";
                                
                            //echo $addcomment."<br>";
                            mysqli_query($conn,$addcomment);
                        }
                    }
                }
            }
        }
        echo 'success';
    }
}

else
{
    header("refresh: 0; url=./index.php", true, 301);
    exit();
}
