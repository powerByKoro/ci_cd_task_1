<?php
require(__DIR__ . '/vendor/autoload.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: '. '/app/upload');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = (new \app\upload\UploadExcelController())->upload($_FILES);;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
}