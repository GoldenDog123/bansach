<?php
require_once('ketnoi.php');
session_start();

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $sql = "DELETE FROM nguoidung WHERE idnguoidung=$id";

  if (mysqli_query($ketnoi, $sql)) {
    $_SESSION['toast'] = ['type' => 'success', 'msg' => '🗑️ Xóa người dùng thành công!'];
  } else {
    $_SESSION['toast'] = ['type' => 'error', 'msg' => '❌ Không thể xóa người dùng này!'];
  }
}

header("Location: index.php?page_layout=danhsachnguoidung");
exit();
