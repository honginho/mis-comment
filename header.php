<?php
session_start();
?>

<!DOCTYPE html>
<html lang="zh-tw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>國立中正大學──論文提案書評論系統</title>
    <script src="jquery-3.4.1.js"></script>
</head>
<body>
    <div>
        <a href="./index.php" style="margin-bottom: 4px; padding: 2px 4px; border: 1px solid blue;">Index</a>

<?php
    if (isset($_SESSION['level']) && $_SESSION['level'] == 0) {
?>
        <a href="./list.php" style="margin-bottom: 4px; padding: 2px 4px; border: 1px solid blue;">List</a>
<?php
    }
?>
    </div>
