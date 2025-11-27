<?php
require_once('ketnoi.php');

$idnguoidung = $_GET['id'] ?? 0;
if (!is_numeric($idnguoidung) || $idnguoidung <= 0) {
    header('Location: index.php?page_layout=danhsachnguoidung');
    exit();
}

// Lệnh xóa người dùng. ON DELETE CASCADE sẽ tự động xóa các địa chỉ và dữ liệu liên quan.
$sql_delete = "DELETE FROM nguoidung WHERE idnguoidung = $idnguoidung";

if (mysqli_query($ketnoi, $sql_delete)) {
    echo "<script>showToast('Xóa người dùng thành công! Dữ liệu liên quan đã được xóa.', 'success');</script>";
} else {
    echo "<script>showToast('Lỗi khi xóa người dùng: " . mysqli_error($ketnoi) . "', 'danger');</script>";
}

// Chuyển hướng về trang danh sách
header('Location: index.php?page_layout=danhsachnguoidung');
exit();
?>