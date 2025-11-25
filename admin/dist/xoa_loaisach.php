<?php
require_once('ketnoi.php');
if (!isset($_GET['idloaisach'])) {
  echo "<script>alert('❌ Không xác định được thể loại cần xóa!'); window.location='index.php?page_layout=danhsachloaisach';</script>";
  exit;
}

$id = $_GET['idloaisach'];
$sql = "DELETE FROM loaisach WHERE idloaisach='$id'";
if (mysqli_query($ketnoi, $sql)) {
  echo "<script>alert('✅ Đã xóa thể loại thành công!'); window.location='index.php?page_layout=danhsachloaisach';</script>";
} else {
  echo "<script>alert('❌ Không thể xóa thể loại (có thể đang được sử dụng trong bảng Sách)!'); window.location='index.php?page_layout=danhsachloaisach';</script>";
}
?>
