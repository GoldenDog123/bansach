<?php
require_once('ketnoi.php');

$id = (int)$_GET['idsach'];
$sach = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT * FROM sach WHERE idsach=$id"));
$tacgia = mysqli_query($ketnoi, "SELECT * FROM tacgia ORDER BY tentacgia ASC");
$loaisach = mysqli_query($ketnoi, "SELECT * FROM loaisach ORDER BY tenloaisach ASC");

$msg = $type = "";

if (isset($_POST['update_sach'])) {
  $tensach = mysqli_real_escape_string($ketnoi, $_POST['tensach']);
  $soluong = (int)$_POST['soluong'];
  $dongia = (int)$_POST['dongia'];
  $mota = mysqli_real_escape_string($ketnoi, $_POST['mota']);
  $idtacgia = (int)$_POST['idtacgia'];
  $idloaisach = (int)$_POST['idloaisach'];

  $hinhanh = $sach['hinhanhsach'];
  if (!empty($_FILES['hinhanhsach']['name'])) {
    $filename = time() . '_' . basename($_FILES['hinhanhsach']['name']);
    move_uploaded_file($_FILES['hinhanhsach']['tmp_name'], "../../feane/images/$filename");
    $hinhanh = $filename;
  }

  $sql = "UPDATE sach 
          SET tensach='$tensach', soluong=$soluong, dongia=$dongia, mota='$mota',
              idtacgia=$idtacgia, idloaisach=$idloaisach, hinhanhsach='$hinhanh'
          WHERE idsach=$id";

  if (mysqli_query($ketnoi, $sql)) {
    $msg = "✅ Cập nhật sách thành công!";
    $type = "success";
  } else {
    $msg = "❌ Lỗi khi cập nhật!";
    $type = "danger";
  }
}
?>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header text-dark fw-bold" 
         style="background: linear-gradient(90deg, #facc15, #f59e0b);">
      <h4 class="mb-0"><i class="bx bx-edit-alt"></i> Cập nhật thông tin sách</h4>
    </div>

    <div class="card-body bg-light">
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label fw-bold">Tên sách</label>
          <input type="text" name="tensach" class="form-control" 
                 value="<?= htmlspecialchars($sach['tensach']); ?>" required>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Số lượng</label>
            <input type="number" name="soluong" class="form-control" 
                   value="<?= $sach['soluong']; ?>" required min="1">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Đơn giá (₫)</label>
            <input type="number" name="dongia" class="form-control" 
                   value="<?= $sach['dongia']; ?>" required min="0">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Mô tả</label>
          <textarea name="mota" class="form-control" rows="3"><?= htmlspecialchars($sach['mota']); ?></textarea>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Tác giả</label>
            <select name="idtacgia" class="form-select" required>
              <?php while ($tg = mysqli_fetch_assoc($tacgia)) { ?>
                <option value="<?= $tg['idtacgia']; ?>" 
                        <?= $tg['idtacgia'] == $sach['idtacgia'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($tg['tentacgia']); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Thể loại</label>
            <select name="idloaisach" class="form-select" required>
              <?php while ($ls = mysqli_fetch_assoc($loaisach)) { ?>
                <option value="<?= $ls['idloaisach']; ?>" 
                        <?= $ls['idloaisach'] == $sach['idloaisach'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($ls['tenloaisach']); ?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Ảnh bìa hiện tại</label><br>
          <?php if (!empty($sach['hinhanhsach'])) { ?>
            <img src="../../feane/images/<?= htmlspecialchars($sach['hinhanhsach']); ?>" 
                 width="100" height="130" class="rounded shadow-sm border mb-2" style="object-fit:cover;">
          <?php } ?>
          <input type="file" name="hinhanhsach" class="form-control">
        </div>

        <div class="d-flex justify-content-between">
          <button type="submit" name="update_sach" class="btn btn-warning text-dark px-4 shadow-sm">
            <i class="bx bx-save"></i> Lưu thay đổi
          </button>
          <a href="index.php?page_layout=danhsachsach" class="btn btn-secondary px-4 shadow-sm">
            <i class="bx bx-arrow-back"></i> Quay lại
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<!-- JS Toast -->
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
    border: none;
  }
  .form-label {
    color: #0f172a;
  }
</style>
