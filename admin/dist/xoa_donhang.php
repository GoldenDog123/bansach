<?php
require_once 'ketnoi.php';

$iddonhang = isset($_GET['iddonhang']) ? intval($_GET['iddonhang']) : 0;
if ($iddonhang <= 0) {
  header("Location: index.php?page_layout=danhsachdonhang");
  exit;
}

// Xóa theo thứ tự để giữ ràng buộc
mysqli_query($ketnoi, "DELETE FROM donhang_chitiet WHERE iddonhang = $iddonhang");
mysqli_query($ketnoi, "DELETE FROM giaohang WHERE iddonhang = $iddonhang");
mysqli_query($ketnoi, "DELETE FROM thanhtoan WHERE iddonhang = $iddonhang");
mysqli_query($ketnoi, "DELETE FROM donhang WHERE iddonhang = $iddonhang");

header("Location: index.php?page_layout=danhsachdonhang");
exit;
