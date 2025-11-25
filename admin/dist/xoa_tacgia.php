<?php
require_once('ketnoi.php');
$id = $_GET['id'] ?? 0;

if ($id > 0) {
  $check = mysqli_query($ketnoi, "SELECT * FROM sach WHERE idtacgia = $id");
  if (mysqli_num_rows($check) > 0) {
    echo "<script>
      localStorage.setItem('toast', JSON.stringify({msg: '⚠️ Không thể xóa vì còn sách liên kết', type: 'error'}));
      window.location='index.php?page_layout=danhsachtacgia';
    </script>";
    exit;
  }

  if (mysqli_query($ketnoi, "DELETE FROM tacgia WHERE idtacgia=$id")) {
    echo "<script>
      localStorage.setItem('toast', JSON.stringify({msg: '✅ Xóa tác giả thành công', type: 'success'}));
      window.location='index.php?page_layout=danhsachtacgia';
    </script>";
  } else {
    echo "<script>
      localStorage.setItem('toast', JSON.stringify({msg: '❌ Lỗi khi xóa tác giả', type: 'error'}));
      window.location='index.php?page_layout=danhsachtacgia';
    </script>";
  }
}
?>
