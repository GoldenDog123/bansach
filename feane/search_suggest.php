<?php
require_once "ketnoi.php";

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword === '') {
    echo json_encode([]);
    exit;
}

$kw = mysqli_real_escape_string($ketnoi, $keyword);

$sql = "SELECT 
            sach.idsach, 
            sach.tensach, 
            tacgia.tentacgia 
        FROM sach
        LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia
        WHERE sach.tensach LIKE '%$kw%' 
           OR tacgia.tentacgia LIKE '%$kw%'
        ORDER BY sach.tensach ASC
        LIMIT 10";

$res = mysqli_query($ketnoi, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}

echo json_encode($data);
