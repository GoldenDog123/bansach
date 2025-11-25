<?php
require_once('ketnoi.php');
if (!isset($_GET['idloaisach'])) {
  echo "<script>alert('❌ Không tìm thấy thể loại!'); window.location='index.php?page_layout=danhsachloaisach';</script>";
  exit;
}
$id = $_GET['idloaisach'];
$query = mysqli_query($ketnoi, "SELECT * FROM loaisach WHERE idloaisach='$id'");
$row = mysqli_fetch_assoc($query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tenloaisach = trim($_POST['tenloaisach']);
  if ($tenloaisach === '') {
    echo "<script>alert('⚠️ Vui lòng nhập tên thể loại!');</script>";
  } else {
    $sql = "UPDATE loaisach SET tenloaisach='$tenloaisach' WHERE idloaisach='$id'";
    if (mysqli_query($ketnoi, $sql)) {
      echo "<script>alert('✅ Cập nhật thành công!'); window.location='index.php?page_layout=danhsachloaisach';</script>";
    } else {
      echo "<script>alert('❌ Lỗi khi cập nhật!');</script>";
    }
  }
}
?>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header bg-warning text-dark">
      <h5 class="mb-0"><i class="bx bx-edit-alt"></i> Sửa thể loại</h5>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Tên thể loại</label>
          <input type="text" name="tenloaisach" value="<?= htmlspecialchars($row['tenloaisach']); ?>" class="form-control" required>
        </div>

        <div class="d-flex justify-content-between">
          <a href="index.php?page_layout=danhsachloaisach" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Quay lại
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save"></i> Lưu thay đổi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
