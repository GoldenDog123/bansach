<?php
require_once('ketnoi.php');

$idcoupon = $_GET['id'] ?? 0;
if (!is_numeric($idcoupon) || $idcoupon <= 0) {
    header('Location: index.php?page_layout=danhsachkhuyenmai');
    exit();
}

// Xóa coupon
$sql_delete = "DELETE FROM coupon WHERE idcoupon = $idcoupon";

if (mysqli_query($ketnoi, $sql_delete)) {
    echo "<script>showToast('Xóa mã khuyến mãi thành công!', 'success');</script>";
} else {
    echo "<script>showToast('Lỗi khi xóa mã khuyến mãi: " . mysqli_error($ketnoi) . "', 'danger');</script>";
}

// Chuyển hướng về trang danh sách
header('Location: index.php?page_layout=danhsachkhuyenmai');
exit();
?>