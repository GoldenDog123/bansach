<?php
require_once('ketnoi.php');

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM nguoidung WHERE idnguoidung=$id";
$res = mysqli_query($ketnoi, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
  $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Không tìm thấy người dùng!'];
  header("Location: index.php?page_layout=danhsachnguoidung");
  exit();
}
$user = mysqli_fetch_assoc($res);

if (isset($_POST['update_user'])) {
  $hoten = trim($_POST['hoten']);
  $email = trim($_POST['email']);
  $sdt = trim($_POST['sdt']);
  $vaitro = trim($_POST['vaitro']);

  $sql_update = "UPDATE nguoidung SET hoten='$hoten', email='$email', sdt='$sdt', vaitro='$vaitro' WHERE idnguoidung=$id";
  if (mysqli_query($ketnoi, $sql_update)) {
    $_SESSION['toast'] = ['type' => 'success', 'msg' => '✅ Cập nhật người dùng thành công!'];
  } else {
    $_SESSION['toast'] = ['type' => 'error', 'msg' => '❌ Lỗi khi cập nhật người dùng!'];
  }
  header("Location: index.php?page_layout=danhsachnguoidung");
  exit();
}
?>

<div class="container mt-4">
  <div class="card shadow-lg border-0" style="border-radius:16px;">
    <div class="card-header bg-warning d-flex align-items-center text-dark">
      <i class="bx bx-edit fs-4 me-2"></i>
      <h5 class="mb-0">Chỉnh sửa người dùng</h5>
    </div>

    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Họ tên</label>
          <input type="text" name="hoten" class="form-control" value="<?= htmlspecialchars($user['hoten']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Số điện thoại</label>
          <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($user['sdt']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Vai trò</label>
          <select name="vaitro" class="form-select">
            <option value="hoc_sinh" <?= $user['vaitro']=='hoc_sinh'?'selected':'' ?>>Học sinh</option>
            <option value="thuthu" <?= $user['vaitro']=='thuthu'?'selected':'' ?>>Thủ thư</option>
            <option value="admin" <?= $user['vaitro']=='admin'?'selected':'' ?>>Admin</option>
          </select>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <button type="submit" name="update_user" class="btn btn-warning px-4 text-dark fw-bold">
            <i class="bx bx-save"></i> Cập nhật
          </button>
          <a href="index.php?page_layout=danhsachnguoidung" class="btn btn-secondary px-4">
            <i class="bx bx-arrow-back"></i> Quay lại
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
