<?php
require_once('ketnoi.php');


if (isset($_POST['add_user'])) {
  $hoten = trim($_POST['hoten']);
  $email = trim($_POST['email']);
  $sdt = trim($_POST['sdt']);
  $vaitro = trim($_POST['vaitro']);
  $matkhau = $_POST['matkhau'];
  $xacnhan = $_POST['xacnhan'];

  if ($matkhau !== $xacnhan) {
    $_SESSION['toast'] = ['type' => 'error', 'msg' => '❌ Mật khẩu xác nhận không khớp!'];
  } else {
    $check = mysqli_query($ketnoi, "SELECT * FROM nguoidung WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
      $_SESSION['toast'] = ['type' => 'warning', 'msg' => '⚠️ Email đã tồn tại!'];
    } else {
      $hash = password_hash($matkhau, PASSWORD_DEFAULT);
      $sql = "INSERT INTO nguoidung (hoten, email, sdt, matkhau, vaitro, ngaytao) 
              VALUES ('$hoten', '$email', '$sdt', '$hash', '$vaitro', NOW())";
      if (mysqli_query($ketnoi, $sql)) {
        $_SESSION['toast'] = ['type' => 'success', 'msg' => '✅ Thêm người dùng thành công!'];
      } else {
        $_SESSION['toast'] = ['type' => 'error', 'msg' => '❌ Lỗi khi thêm người dùng!'];
      }
    }
  }
  header("Location: index.php?page_layout=danhsachnguoidung");
  exit();
}
?>

<div class="container mt-4">
  <div class="card shadow-lg border-0" style="border-radius:16px;">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bx bx-user-plus fs-4 me-2"></i>
      <h5 class="mb-0">Thêm người dùng mới</h5>
    </div>

    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label fw-bold">Họ tên</label>
          <input type="text" name="hoten" class="form-control" placeholder="Nhập họ tên" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Số điện thoại</label>
          <input type="text" name="sdt" class="form-control" placeholder="Nhập số điện thoại">
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Mật khẩu</label>
            <input type="password" name="matkhau" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Xác nhận mật khẩu</label>
            <input type="password" name="xacnhan" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Vai trò</label>
          <select name="vaitro" class="form-select" required>
            <option value="hoc_sinh">Học sinh</option>
            <option value="thuthu">Thủ thư</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <button type="submit" name="add_user" class="btn btn-success px-4">
            <i class="bx bx-save"></i> Lưu lại
          </button>
          <a href="index.php?page_layout=danhsachnguoidung" class="btn btn-secondary px-4">
            <i class="bx bx-arrow-back"></i> Quay lại
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
