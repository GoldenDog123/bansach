<?php
require_once('ketnoi.php');
$id = $_GET['id'] ?? 0;
$res = mysqli_query($ketnoi, "SELECT * FROM tacgia WHERE idtacgia=$id");
$tacgia = mysqli_fetch_assoc($res);

if (!$tacgia) {
  echo "<p class='text-danger'>Không tìm thấy tác giả!</p>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tentacgia = trim($_POST['tentacgia']);
  $ghichu = trim($_POST['ghichu']);

  if ($tentacgia === '') {
    echo "<script>alert('Vui lòng nhập tên tác giả');</script>";
  } else {
    $sql = "UPDATE tacgia SET tentacgia='$tentacgia', ghichu='$ghichu' WHERE idtacgia=$id";
    if (mysqli_query($ketnoi, $sql)) {
      echo "<script>
        localStorage.setItem('toast', JSON.stringify({msg: '✅ Cập nhật tác giả thành công', type: 'success'}));
        window.location='index.php?page_layout=danhsachtacgia';
      </script>";
      exit;
    } else {
      echo "<script>alert('❌ Lỗi khi cập nhật');</script>";
    }
  }
}
?>

<div class="card shadow-sm p-4">
  <h4 class="mb-4 text-primary"><i class="mdi mdi-pencil"></i> Sửa thông tin tác giả</h4>
  <form method="post">
    <div class="mb-3">
      <label class="form-label fw-bold">Tên tác giả</label>
      <input type="text" name="tentacgia" class="form-control" value="<?= htmlspecialchars($tacgia['tentacgia']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label fw-bold">Ghi chú</label>
      <textarea name="ghichu" class="form-control" rows="3"><?= htmlspecialchars($tacgia['ghichu']) ?></textarea>
    </div>
    <div class="d-flex justify-content-between">
      <a href="index.php?page_layout=danhsachtacgia" class="btn btn-secondary px-4">⬅ Quay lại</a>
      <button type="submit" class="btn btn-success px-4"><i class="mdi mdi-content-save"></i> Cập nhật</button>
    </div>
  </form>
</div>
