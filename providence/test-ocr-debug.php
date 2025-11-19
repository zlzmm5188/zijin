<?php
error_reporting(0);
ini_set('display_errors', 0);

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data || !isset($data['image'])) {
    echo json_encode(['error' => 'No image']);
    exit;
}

$imageData = $data['image'];
if (strpos($imageData, 'base64,') !== false) {
    $imageData = explode('base64,', $imageData)[1];
}

$tempFile = '/tmp/test_ocr.png';
file_put_contents($tempFile, base64_decode($imageData));

// 测试图片是否有效
$img = @imagecreatefromstring(file_get_contents($tempFile));
if (!$img) {
    echo json_encode(['error' => 'Invalid image', 'size' => filesize($tempFile)]);
    exit;
}

// 调用Tesseract，使用多种模式
$results = [];
$modes = [
    'psm6' => "tesseract " . escapeshellarg($tempFile) . " stdout -l eng --psm 6 --oem 1 digits 2>&1",
    'psm11' => "tesseract " . escapeshellarg($tempFile) . " stdout -l eng --psm 11 --oem 1 digits 2>&1",
    'chi' => "tesseract " . escapeshellarg($tempFile) . " stdout -l chi_sim+eng --psm 6 2>&1"
];

foreach ($modes as $key => $cmd) {
    $results[$key] = trim(shell_exec($cmd));
}

@unlink($tempFile);

echo json_encode([
    'code' => 200,
    'file_size' => filesize($tempFile),
    'results' => $results
]);
?>
