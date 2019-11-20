<?php
session_start();
?>

<!DOCTYPE html>
<html lang="zh-tw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>國立中正大學資管所&醫管所論文提案書評論系統</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <style>
        #form-check-comments > .form-row > .form-group {
            padding: 1rem 1rem .5rem;
            background-color: #f8f8f8;
            border: 1px solid #e9ecef;
            border-radius: 0.4rem;
        }

        .comments-eg {
            display: block;
        }

        .btn-comments-eg {
            margin-right: .5rem;
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn-comments-eg:hover {
            color: #dc3545;
            background-color: #f8d7da; /* #fbeaec */
        }

        .comments-eg input[name='comments_codes[]']:checked + .btn-comments-eg {
            background-color: #dc3545 !important;
            color: #fff;
            font-weight: bold;
        }

        .nav-item.nav-link {
            outline: none;
        }
    </style>
</head>
<body class="py-5" style="background-color: #fefefe; font-family: 'Microsoft JhengHei';">
