<?php
require_once('ketnoi.php');
$id = (int)$_GET['idsach'];

if (mysqli_query($ketnoi, "DELETE FROM sach WHERE idsach=$id")) {
  echo "<script>showToast('✅ Xóa sách thành công!','success');setTimeout(()=>window.location='index.php?page_layout=danhsachsach',1500);</script>";
} else {
  echo "<script>showToast('❌ Không thể xóa do ràng buộc dữ liệu!','danger');setTimeout(()=>window.location='index.php?page_layout=danhsachsach',1500);</script>";
}
?>
