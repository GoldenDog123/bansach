<?php
require_once('ketnoi.php');

if (isset($_GET['id']) && isset($_GET['status'])) {
    $iddonhang = mysqli_real_escape_string($ketnoi, $_GET['id']);
    $status = mysqli_real_escape_string($ketnoi, $_GET['status']); 

    // Kiểm tra tính hợp lệ của trạng thái (Tùy thuộc vào ENUM của bạn)
    $valid_statuses = ['cho_xac_nhan', 'dang_chuan_bi', 'dang_giao', 'hoan_thanh', 'huy'];

    if (in_array($status, $valid_statuses)) {
        
        $sql = "UPDATE donhang SET trangthai = '$status' WHERE iddonhang = $iddonhang";
        
        if (mysqli_query($ketnoi, $sql)) {
            // Chuyển hướng về trang danh sách sau khi cập nhật
            header('Location: index.php?page_layout=danhsachdonhang');
            exit();
        } else {
            die("Lỗi cập nhật trạng thái đơn hàng: " . mysqli_error($ketnoi));
        }
    } else {
        die("Trạng thái không hợp lệ.");
    }
} else {
    // Chuyển hướng nếu thiếu tham số
    header('Location: index.php?page_layout=danhsachdonhang');
    exit();
}
?>