<?php
require_once('ketnoi.php');
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hoten = trim($_POST['hoten']);
  $email = trim($_POST['email']);
  $matkhau = trim($_POST['matkhau']);
  $xacnhan = trim($_POST['xacnhan']);

  if ($hoten === '' || $email === '' || $matkhau === '' || $xacnhan === '') {
    $message = '<div class="alert alert-danger text-center">Vui lòng nhập đầy đủ thông tin.</div>';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = '<div class="alert alert-danger text-center">Email không hợp lệ.</div>';
  } elseif (!preg_match('/@gmail\.com$/', $email)) {
    $message = '<div class="alert alert-danger text-center">Vui lòng sử dụng địa chỉ Gmail hợp lệ (ví dụ: ten@gmail.com).</div>';

  } elseif ($matkhau !== $xacnhan) {
    $message = '<div class="alert alert-danger text-center">Mật khẩu xác nhận không khớp.</div>';
  } elseif (strlen($matkhau) < 8) {
    $message = '<div class="alert alert-danger text-center">Mật khẩu phải có ít nhất 8 ký tự.</div>';

  } else {
    // Kiểm tra email tồn tại
    $stmt = mysqli_prepare($ketnoi, "SELECT idnguoidung FROM nguoidung WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
      $message = '<div class="alert alert-warning text-center">Email này đã được đăng ký trước đó.</div>';
    } else {
      // Thêm mới người dùng
      $hash = password_hash($matkhau, PASSWORD_DEFAULT);
      $vaitro = 'hoc_sinh';
      $ins = mysqli_prepare($ketnoi, "INSERT INTO nguoidung (hoten, email, matkhau, vaitro) VALUES (?, ?, ?, ?)");
      mysqli_stmt_bind_param($ins, 'ssss', $hoten, $email, $hash, $vaitro);
      if (mysqli_stmt_execute($ins)) {
        $message = '<div class="alert alert-success text-center">
                ✅ Đăng ký thành công! Đang chuyển hướng đến trang đăng nhập...
              </div>';
        echo '<meta http-equiv="refresh" content="2;url=dangnhap.php">';
        // hoặc dùng JavaScript nếu bạn muốn hiệu ứng mượt hơn:
        // echo "<script>setTimeout(() => { window.location.href='dangnhap.php'; }, 2000);</script>";
      } else {
        $message = '<div class="alert alert-danger text-center">Có lỗi xảy ra, vui lòng thử lại.</div>';
      }
    }
    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Đăng ký - Thư viện CTECH</title>
  <link rel="shortcut icon" href="images/Book.png" type="image/png">
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
  <link href="css/style.css" rel="stylesheet" />
  <link href="css/responsive.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dangky.css">
  <link href="css/footer.css" rel="stylesheet">
</head>

<body>
  <div class="register-container">
    <h3>Tạo Tài Khoản Thư Viện</h3>
    <?php echo $message; ?>
    <form method="POST">
      <div class="form-group mb-3">
        <label>Họ và tên</label>
        <input type="text" name="hoten" class="form-control" placeholder="Nhập họ tên..."
          value="<?= htmlspecialchars($_POST['hoten'] ?? '') ?>" required>
      </div>

      <div class="form-group mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Nhập email..."
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="form-group mb-3">
        <label>Mật khẩu</label>
        <input type="password" name="matkhau" class="form-control" placeholder="Tạo mật khẩu..." required>
      </div>

      <div class="form-group mb-4">
        <label>Xác nhận mật khẩu</label>
        <input type="password" name="xacnhan" class="form-control" placeholder="Nhập lại mật khẩu..." required>
      </div>
      <button type="submit" class="btn btn-register">Đăng ký ngay</button>
    </form>

    <div class="register-footer mt-3">
      <p>Đã có tài khoản? <a href="dangnhap.php">Đăng nhập tại đây</a></p>
      <p><a href="index.php"><i class="fa fa-arrow-left"></i> Quay lại trang chủ</a></p>
    </div>
  </div>

  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="js/bootstrap.js"></script>
</body>

</html>