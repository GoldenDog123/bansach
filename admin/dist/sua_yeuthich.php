<?php
require_once('ketnoi.php');

$id = $_GET['id'] ?? 0;
$data = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT * FROM yeuthich WHERE id=$id"));
$nguoidung = mysqli_query($ketnoi, "SELECT idnguoidung, hoten FROM nguoidung");
$sach = mysqli_query($ketnoi, "SELECT idsach, tensach FROM sach");

$message = ''; $type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idnguoidung = $_POST['idnguoidung'];
  $idsach = $_POST['idsach'];

  $sql = "UPDATE yeuthich SET idnguoidung='$idnguoidung', idsach='$idsach' WHERE id=$id";
  if (mysqli_query($ketnoi, $sql)) {
    $message = '✅ Cập nhật thành công!';
    $type = 'success';
  } else {
    $message = '❌ Lỗi khi cập nhật!';
    $type = 'danger';
  }
}
?>

<div class="container mt-3">
  <div class="card shadow border-0">
    <div class="card-header bg-gradient text-white"
         style="background: linear-gradient(90deg, #1e3a8a, #3b82f6);">
      <h5 class="mb-0"><i class="bx bx-edit"></i> Cập nhật yêu thích</h5>
    </div>

    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Người dùng</label>
          <select name="idnguoidung" class="form-select" required>
            <?php while ($nd = mysqli_fetch_assoc($nguoidung)) { ?>
              <option value="<?= $nd['idnguoidung'] ?>" 
                <?= ($data['idnguoidung']==$nd['idnguoidung'])?'selected':'' ?>>
                <?= htmlspecialchars($nd['hoten']) ?>
              </option>
            <?php } ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Sách yêu thích</label>
          <select name="idsach" class="form-select" required>
            <?php while ($s = mysqli_fetch_assoc($sach)) { ?>
              <option value="<?= $s['idsach'] ?>" 
                <?= ($data['idsach']==$s['idsach'])?'selected':'' ?>>
                <?= htmlspecialchars($s['tensach']) ?>
              </option>
            <?php } ?>
          </select>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary px-4"><i class='bx bx-save'></i> Lưu</button>
          <a href="index.php?page_layout=danhsachyeuthich" class="btn btn-secondary ms-2">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
<script>
function showToast(message,type='info'){
  const color = type==='success'?'bg-success':(type==='danger'?'bg-danger':'bg-primary');
  const toast=document.createElement('div');
  toast.className=`toast align-items-center text-white border-0 ${color} show`;
  toast.role='alert';
  toast.innerHTML=`
    <div class="d-flex">
      <div class="toast-body fw-semibold">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  document.getElementById('toastContainer').appendChild(toast);
  setTimeout(()=>toast.remove(),3000);
}

<?php if ($message): ?>
document.addEventListener("DOMContentLoaded", ()=>{
  showToast("<?= $message ?>", "<?= $type ?>");
  <?php if ($type==='success'): ?>
  setTimeout(()=>window.location='index.php?page_layout=danhsachyeuthich',1500);
  <?php endif; ?>
});
<?php endif; ?>
</script>
