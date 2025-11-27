<?php
require_once('ketnoi.php');

if (isset($_GET['id']) && isset($_GET['action'])) {
    $iddanhgia = mysqli_real_escape_string($ketnoi, $_GET['id']);
    $action = $_GET['action'];

    $new_status = ($action == 'duyet') ? 'Duyệt' : 'Chờ duyệt';

    $sql = "UPDATE danhgia SET trangthai = '$new_status' WHERE iddanhgia = $iddanhgia";
    
    if (mysqli_query($ketnoi, $sql)) {
        // Chuyển hướng về trang danh sách sau khi cập nhật
        header('Location: index.php?page_layout=danhsachdanhgia');
        exit();
    } else {
        die("Lỗi cập nhật trạng thái: " . mysqli_error($ketnoi));
    }
} else {
    header('Location: index.php?page_layout=danhsachdanhgia');
    exit();
}
?>