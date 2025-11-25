<?php
require_once('ketnoi.php');
if (!isset($_GET['id'])) {
  echo "<script>alert('Không xác định được mục cần xóa!'); window.location='index.php?page_layout=danhsachyeuthich';</script>";
  exit;
}
$id = $_GET['id'];
$sql = "DELETE FROM yeuthich WHERE id='$id'";
if (mysqli_query($ketnoi, $sql)) {
  echo "<script>alert('✅ Đã xóa khỏi danh sách yêu thích!'); window.location='index.php?page_layout=danhsachyeuthich';</script>";
} else {
  echo "<script>alert('❌ Lỗi khi xóa!'); window.location='index.php?page_layout=danhsachyeuthich';</script>";
}
?>
