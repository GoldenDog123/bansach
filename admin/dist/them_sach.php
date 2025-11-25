<?php
require_once('ketnoi.php');

// Lấy danh sách tác giả và thể loại
$tacgia = mysqli_query($ketnoi, "SELECT * FROM tacgia ORDER BY tentacgia ASC");
$loaisach = mysqli_query($ketnoi, "SELECT * FROM loaisach ORDER BY tenloaisach ASC");

$msg = $type = "";

if (isset($_POST['add_sach'])) {
  $tensach = mysqli_real_escape_string($ketnoi, $_POST['tensach']);
  $soluong = (int)$_POST['soluong'];
  $dongia = (int)$_POST['dongia'];
  $mota = mysqli_real_escape_string($ketnoi, $_POST['mota']);
  $idtacgia = (int)$_POST['idtacgia'];
  $idloaisach = (int)$_POST['idloaisach'];

  // Upload ảnh
  $hinhanh = '';
  if (!empty($_FILES['hinhanhsach']['name'])) {
    $file = $_FILES['hinhanhsach'];
    $filename = time() . '_' . basename($file['name']);
    move_uploaded_file($file['tmp_name'], "../../feane/images/$filename");
    $hinhanh = $filename;
  }

  $sql = "INSERT INTO sach (tensach, soluong, dongia, hinhanhsach, mota, idtacgia, idloaisach, ngaynhap)
          VALUES ('$tensach', $soluong, $dongia, '$hinhanh', '$mota', $idtacgia, $idloaisach, NOW())";
  
  if (mysqli_query($ketnoi, $sql)) {
    $msg = "✅ Thêm sách thành công!";
    $type = "success";
  } else {
    $msg = "❌ Lỗi khi thêm sách!";
    $type = "danger";
  }
}
?>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header text-white" style="background: linear-gradient(90deg, #06b6d4, #67e8f9);">
      <h4 class="mb-0 fw-bold"><i class="bx bx-plus"></i> Thêm Sách Mới</h4>
    </div>
    <div class="card-body bg-light">
      <form method="POST" enctype="multipart/form-data">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Tên sách</label>
            <input type="text" name="tensach" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Số lượng</label>
            <input type="number" name="soluong" class="form-control" required min="1">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Đơn giá (₫)</label>
            <input type="number" name="dongia" class="form-control" required min="0">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Ảnh bìa</label>
          <input type="file" name="hinhanhsach" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Mô tả</label>
          <textarea name="mota" class="form-control" rows="3"></textarea>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Tác giả</label>
            <select name="idtacgia" class="form-select" required>
              <option value="">-- Chọn tác giả --</option>
              <?php while ($tg = mysqli_fetch_assoc($tacgia)) { ?>
                <option value="<?= $tg['idtacgia']; ?>"><?= htmlspecialchars($tg['tentacgia']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Thể loại</label>
            <select name="idloaisach" class="form-select" required>
              <option value="">-- Chọn thể loại --</option>
              <?php while ($ls = mysqli_fetch_assoc($loaisach)) { ?>
                <option value="<?= $ls['idloaisach']; ?>"><?= htmlspecialchars($ls['tenloaisach']); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-between">
          <button type="submit" name="add_sach" class="btn btn-success px-4 shadow-sm">
            <i class="bx bx-save"></i> Lưu
          </button>
          <a href="index.php?page_layout=danhsachsach" class="btn btn-secondary px-4 shadow-sm">
            <i class="bx bx-arrow-back"></i> Quay lại
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<script>
function showToast(message, type = 'info') {
  const color = type === 'success' ? 'bg-success' : (type === 'danger' ? 'bg-danger' : 'bg-primary');
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white border-0 ${color} show`;
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body fw-semibold">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  document.getElementById('toastContainer').appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// Nếu có thông báo từ PHP
<?php if (!empty($msg)) : ?>
  document.addEventListener("DOMContentLoaded", () => {
    showToast("<?= $msg ?>", "<?= $type ?>");
    <?php if ($type === 'success') : ?>
      setTimeout(() => window.location = "index.php?page_layout=danhsachsach", 1500);
    <?php endif; ?>
  });
<?php endif; ?>
</script>

<style>
  .card {
    border-radius: 15px;
  }
  .card-header {
    border-bottom: none;
  }
</style>
