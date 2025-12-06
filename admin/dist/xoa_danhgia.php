<?php
require_once('ketnoi.php');

// Xóa bình luận (từ bảng binh_luan)
if (isset($_GET['id_binh_luan'])) {
    $id_binh_luan = intval($_GET['id_binh_luan']);
    $sql = "DELETE FROM binh_luan WHERE id_binh_luan = $id_binh_luan";
    
    if (mysqli_query($ketnoi, $sql)) {
        echo "Xóa đánh giá thành công";
    } else {
        echo "Lỗi: " . mysqli_error($ketnoi);
    }
}
// Xóa đánh giá (từ bảng danh_gia)
elseif (isset($_GET['id_danh_gia'])) {
    $id_danh_gia = intval($_GET['id_danh_gia']);
    $sql = "DELETE FROM danh_gia WHERE id_danh_gia = $id_danh_gia";
    
    if (mysqli_query($ketnoi, $sql)) {
        echo "Xóa đánh giá thành công";
    } else {
        echo "Lỗi: " . mysqli_error($ketnoi);
    }
} else {
    echo "Thiếu thông tin đánh giá";
}
?>
