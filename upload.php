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

        // TODO: prepare statement

        //新增新同學資料
        $add = "INSERT INTO `stu` (name, stu_id, dept, project)
                SELECT * FROM (SELECT '$NAME' , '$STU_ID', '$DEPT', '$PROJECT') AS tmp
                WHERE NOT EXISTS ( SELECT * FROM `stu` WHERE stu_id = '$STU_ID' and project = '$PROJECT') LIMIT 1";
                //https://stackoverflow.com/questions/3164505/mysql-insert-record-if-not-exists-in-table

        // TODO: 印出有重複的給管理員看
        
        //新增評論資料
        // $addcomment = "SELECT `id` FROM prof 

        mysqli_query($conn,$add);
    }
    ?>
</body>
</html>