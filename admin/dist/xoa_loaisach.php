<?php
require_once('ketnoi.php');

$idloaisach = $_GET['id'] ?? 0;
if (!is_numeric($idloaisach) || $idloaisach <= 0) {
    header('Location: index.php?page_layout=danhsachdanhmuc');
    exit();
}

$sql_delete = "DELETE FROM loaisach WHERE idloaisach = $idloaisach";

if (mysqli_query($ketnoi, $sql_delete)) {
    echo "<script>showToast('Xóa danh mục thành công!', 'success');</script>";
} else {
    // Trường hợp có ràng buộc nào đó không cho phép xóa (dù đã có SET NULL)
    echo "<script>showToast('Lỗi khi xóa danh mục: " . mysqli_error($ketnoi) . "', 'danger');</script>";
}

// Chuyển hướng về trang danh sách
header('Location: index.php?page_layout=danhsachdanhmuc');
exit();
?>