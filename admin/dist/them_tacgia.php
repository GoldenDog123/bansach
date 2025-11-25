<?php
require_once('ketnoi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tentacgia = trim($_POST['tentacgia']);
  $ghichu = trim($_POST['ghichu']);

  if ($tentacgia === '') {
    echo "<script>alert('Vui lòng nhập tên tác giả');</script>";
  } else {
    $sql = "INSERT INTO tacgia (tentacgia, ghichu) VALUES ('$tentacgia', '$ghichu')";
    if (mysqli_query($ketnoi, $sql)) {
      echo "<script>
        localStorage.setItem('toast', JSON.stringify({msg: '✅ Thêm tác giả thành công', type: 'success'}));
        window.location='index.php?page_layout=danhsachtacgia';
      </script>";
      exit;
    } else {
      echo "<script>alert('❌ Lỗi khi thêm tác giả');</script>";
    }
  }
}
?>

<div class="card shadow-sm p-4">
  <h4 class="mb-4 text-primary"><i class="mdi mdi-plus-circle"></i> Thêm tác giả mới</h4>
  <form method="post">
    <div class="mb-3">
      <label class="form-label fw-bold">Tên tác giả</label>
      <input type="text" name="tentacgia" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label fw-bold">Ghi chú</label>
      <textarea name="ghichu" class="form-control" rows="3"></textarea>
    </div>
    <div class="d-flex justify-content-between">
      <a href="index.php?page_layout=danhsachtacgia" class="btn btn-secondary px-4">⬅ Quay lại</a>
      <button type="submit" class="btn btn-success px-4"><i class="mdi mdi-content-save"></i> Lưu</button>
    </div>
  </form>
</div>
