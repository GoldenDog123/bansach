<?php
require_once('ketnoi.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tenloaisach = trim($_POST['tenloaisach']);
  if ($tenloaisach === '') {
    echo "<script>alert('⚠️ Tên thể loại không được để trống!');</script>";
  } else {
    $check = mysqli_query($ketnoi, "SELECT * FROM loaisach WHERE tenloaisach='$tenloaisach'");
    if (mysqli_num_rows($check) > 0) {
      echo "<script>alert('⚠️ Thể loại này đã tồn tại!');</script>";
    } else {
      $sql = "INSERT INTO loaisach (tenloaisach, created_at) VALUES ('$tenloaisach', NOW())";
      if (mysqli_query($ketnoi, $sql)) {
        echo "<script>alert('✅ Thêm thể loại thành công!'); window.location='index.php?page_layout=danhsachloaisach';</script>";
      } else {
        echo "<script>alert('❌ Thêm thất bại!');</script>";
      }
    }
  }
}
?>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0"><i class="bx bx-plus-circle"></i> Thêm thể loại mới</h5>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Tên thể loại</label>
          <input type="text" name="tenloaisach" class="form-control" required>
        </div>

        <div class="d-flex justify-content-between">
          <a href="index.php?page_layout=danhsachloaisach" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Quay lại
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save"></i> Lưu
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
