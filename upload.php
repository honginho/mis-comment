<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>UPLOAD</title>
</head>
<body>
    <?php
    require_once('connect.php');

    if ($_FILES["file"]["error"] > 0)
    {
    　die ("Error: " . $_FILES["file"]["error"]);
    }
    ?>

    <?php
    require_once "Classes/PHPExcel.php";
    $tmp_name = $_FILES["file"]["tmp_name"];
    $excelReader = PHPExcel_IOFactory::createReaderForFile($tmp_name); //讀取excel檔案
    $excelObj = $excelReader->load($tmp_name); //檔案名稱
    $sheet = $excelObj->getActiveSheet(); //取得第一個資料表；Active用在只有一個，若有多個改getSheet(index)
    $lastRow = $sheet->getHighestRow(); //取得總行數(橫)


    echo"<table>";
    for($row = 1;$row<=$lastRow;$row++){
        echo"<tr><td>";
        echo $sheet->getCell('A'.$row)->getValue();
        echo"</td><td>";
        echo $sheet->getCell('B'.$row)->getValue();
        echo"</td><td>";
        echo $sheet->getCell('C'.$row)->getValue();
        echo"</td><td>";
        echo $sheet->getCell('D'.$row)->getValue();
        echo"</td><td>";
        echo $sheet->getCell('E'.$row)->getValue();
        echo"</td></tr>";
    }
    echo"</table>";

    $lastRow = $sheet->getHighestRow(); //取得總行數(橫)
    for($row = 2; $row <= $lastRow; $row++){
        $SEMESTER = $sheet->getCell('A'.$row)->getValue();
        $NAME = $sheet->getCell('B'.$row)->getValue();
        $STU_ID = $sheet->getCell('C'.$row)->getValue();
        $DEPT = $sheet->getCell('D'.$row)->getValue();
        $PROJECT = $sheet->getCell('E'.$row)->getValue();
        $PROF = $sheet->getCell('F'.$row)->getValue(); //評論老師

        // TODO: prepare statement

        //新增新同學資料
        $add = "INSERT INTO `stu` (name, stu_id, dept, project)
                SELECT * FROM (SELECT '$NAME' , '$STU_ID', '$DEPT', '$PROJECT') AS tmp
                WHERE NOT EXISTS ( SELECT * FROM `stu` WHERE stu_id = '$STU_ID' and project = '$PROJECT') LIMIT 1"; //過濾掉ID論文名稱相同資料
                //https://stackoverflow.com/questions/3164505/mysql-insert-record-if-not-exists-in-table
        mysqli_query($conn,$add);
        // TODO: 印出有重複的給管理員看
        
        //新增評論資料 1.對比教授們的ID 2.對比學生ID
        
        $get_stu_id = mysqli_query($conn,"SELECT `id`,`stu_id` FROM `stu` WHERE `stu_id` = '$STU_ID'");
        $row_stu_id;
        while ($row3=mysqli_fetch_row($get_stu_id))
        {
            $row_stu_id = $row3[0];
            echo "學生ID:".$row3[0]."<br>";
            echo "學生學號:".$row3[1]."<br>";
            $get_prof= explode(",", $PROF); //分割評論老師 回傳陣列
            for($i=0;$i< count($get_prof);$i++){
                $get_prof_id = mysqli_query($conn,"SELECT `id`,`name` FROM `prof` WHERE `name` = '$get_prof[$i]'");
                while ($row2=mysqli_fetch_row($get_prof_id)){
                    echo "評論老師的ID:".$row2[0]."<br>"; //[0]:與會老師的ID
                    $addcomment = "INSERT INTO `comments` (semester,prof_id,stu_id) VALUES (10806,$row2[0],$row_stu_id)";
                    echo $addcomment."<br>";
                    mysqli_query($conn,$addcomment);
                }
            }
        }
    }
    ?>
</body>
</html>