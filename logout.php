<?php
session_start();

if (session_destroy()) {
    echo "已登出。";
    header("refresh: 1; url=./index.php", true, 301);
    exit();
}
