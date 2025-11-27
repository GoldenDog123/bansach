<?php
require_once('ketnoi.php');

if (isset($_GET['iddanhgia'])) {
    $iddanhgia = mysqli_real_escape_string($ketnoi, $_GET['iddanhgia']);

    $sql = "DELETE FROM danhgia WHERE iddanhgia = $iddanhgia";
    
    if (mysqli_query($ketnoi, $sql)) {
        echo "Xóa đánh giá thành công"; // Phản hồi cho Fetch API
    } else {
        http_response_code(500);
        echo "Lỗi xóa đánh giá: " . mysqli_error($ketnoi);
    }
} else {
    echo "Thiếu ID đánh giá";
}
?>