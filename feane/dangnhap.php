<?php
require_once('ketnoi.php');
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $matkhau = trim($_POST['matkhau']);

  if ($email === '' || $matkhau === '') {
    $message = '<div class="alert alert-danger text-center">Vui lòng nhập đầy đủ Email và Mật khẩu.</div>';
  } else {

    // Kiểm tra lại tên cột mật khẩu trong DB của bạn
    $sql = "SELECT idnguoidung, hoten, matkhau, vaitro FROM nguoidung WHERE email = ?";

    $stmt = mysqli_prepare($ketnoi, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    // Lưu kết quả vào bộ nhớ
    mysqli_stmt_store_result($stmt);

    // Nếu có 1 dòng khớp email
    if (mysqli_stmt_num_rows($stmt) > 0) {

      mysqli_stmt_bind_result($stmt, $idnguoidung, $hoten, $hash, $vaitro);
      mysqli_stmt_fetch($stmt);

      // Debug nếu hash bị NULL
      if ($hash === null) {
        die("<b>Lỗi:</b> Trường mật khẩu trong DB không phải tên 'matkhau'.<br>Hãy gửi cấu trúc bảng để mình sửa chính xác.");
      }

      // Kiểm tra mật khẩu
      if (password_verify($matkhau, $hash)) {

        $_SESSION['idnguoidung'] = $idnguoidung;
        $_SESSION['hoten'] = $hoten;
        $_SESSION['email'] = $email;
        $_SESSION['vaitro'] = $vaitro;

        header("Location: index.php");
        exit;
      } else {
        $message = '<div class="alert alert-danger text-center">Sai mật khẩu. Vui lòng thử lại.</div>';
      }
    } else {
      $message = '<div class="alert alert-danger text-center">Email không tồn tại trong hệ thống.</div>';
    }

    mysqli_stmt_close($stmt);
  }
}

mysqli_close($ketnoi);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Đăng nhập - Thư viện CTECH</title>
  <link rel="shortcut icon" href="images/Book.png" type="image/png">
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
  <link href="css/style.css" rel="stylesheet" />
  <link href="css/responsive.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dangnhap.css">
  <link href="css/footer.css" rel="stylesheet">
</head>

<body>

  <div class="login-container">
    <h3>Đăng Nhập Hệ Thống</h3>
    <?php if ($message != '') echo $message; ?>
    <form method="POST">
      <div class="form-group mb-3">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Nhập email..." required>
      </div>
      <div class="form-group mb-3">
        <label for="matkhau">Mật khẩu</label>
        <input type="password" name="matkhau" class="form-control" placeholder="Nhập mật khẩu..." required>
      </div>
      <button type="submit" class="btn btn-login">Đăng nhập</button>
    </form>

    <div class="login-footer mt-3">
      <p>Chưa có tài khoản? <a href="dangky.php">Đăng ký</a></p>
      <p><a href="index.php"><i class="fa fa-arrow-left"></i> Quay lại trang chủ</a></p>
    </div>
  </div>

  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="js/bootstrap.js"></script>
</body>

</html>