<?php
require_once('ketnoi.php');

if (isset($_GET['id']) && isset($_GET['status'])) {
    $idthanhtoan = mysqli_real_escape_string($ketnoi, $_GET['id']);
    $status = mysqli_real_escape_string($ketnoi, $_GET['status']); // thanh_cong hoặc that_bai

    // Kiểm tra tính hợp lệ của trạng thái
    if ($status === 'thanh_cong' || $status === 'that_bai') {
        
        $sql = "UPDATE thanhtoan SET trangthai = '$status' WHERE idthanhtoan = $idthanhtoan";
        
        if (mysqli_query($ketnoi, $sql)) {
            // Chuyển hướng về trang danh sách sau khi cập nhật
            // Bạn có thể thêm thông báo thành công ở đây nếu muốn
            header('Location: index.php?page_layout=danhsachthanhtoan');
            exit();
        } else {
            die("Lỗi cập nhật trạng thái thanh toán: " . mysqli_error($ketnoi));
        }
    } else {
        die("Trạng thái không hợp lệ.");
    }
} else {
    // Chuyển hướng nếu thiếu tham số
    header('Location: index.php?page_layout=danhsachthanhtoan');
    exit();
}
?>