<?php
include('ketnoi.php');

if (isset($_GET['idsach'])) {
  $idsach = intval($_GET['idsach']);
  $sql_sach = "SELECT * FROM sach WHERE idsach = $idsach";
  $result_sach = mysqli_query($ketnoi, $sql_sach);
  $sach = mysqli_fetch_assoc($result_sach);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $soluongmua = intval($_POST['soluong']);
    $ngaydat = date('Y-m-d H:i:s');

    // ❌ Không cho mua quá số lượng
    if ($soluongmua <= 0 || $soluongmua > $sach['soluong']) {
      echo "<script>
              alert('❌ Số lượng không hợp lệ hoặc vượt quá số lượng còn lại!');
              window.location.href='chitietsach.php?idsach=$idsach';
            </script>";
      exit;
    }

    // 🔍 Kiểm tra tài khoản
    $check = mysqli_query($ketnoi, "SELECT * FROM nguoidung WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
      $user = mysqli_fetch_assoc($check);
      $idnguoidung = $user['idnguoidung'];
    } else {
      mysqli_query($ketnoi, "INSERT INTO nguoidung (hoten, email, matkhau, vaitro) 
                             VALUES ('$hoten', '$email', '12345', 'hoc_sinh')");
      $idnguoidung = mysqli_insert_id($ketnoi);
    }

    // 👉 Tính giá tiền
    $dongia = $sach['dongia'];
    $thanhtien = $dongia * $soluongmua;

    // 🧾 1. Tạo đơn hàng
    $sql_donhang = "INSERT INTO donhang (idnguoidung, tongtien, trangthai, ngaydat)
                    VALUES ($idnguoidung, $thanhtien, 'cho_duyet', '$ngaydat')";
    mysqli_query($ketnoi, $sql_donhang);
    $iddonhang = mysqli_insert_id($ketnoi);

    // 🧾 2. Thêm chi tiết đơn hàng
    $sql_ct = "INSERT INTO donhang_chitiet (iddonhang, idsach, soluong, dongia, thanhtien)
               VALUES ($iddonhang, $idsach, $soluongmua, $dongia, $thanhtien)";
    mysqli_query($ketnoi, $sql_ct);

    // 📉 3. Trừ số lượng sách
    mysqli_query($ketnoi, "UPDATE sach SET soluong = soluong - $soluongmua WHERE idsach = $idsach");

    echo "<script>
            alert('🛒 Đặt mua thành công! Đơn hàng đang chờ duyệt.');
            window.location.href='donhangcuatoi.php';
          </script>";
    exit;
  }
} else {
  echo "<script>alert('Thiếu mã sách!'); window.location.href='index.php';</script>";
  exit;
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Mua Sách - <?php echo htmlspecialchars($sach['tensach']); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
  <link href="css/footer.css" rel="stylesheet">
  <link href="css/header.css" rel="stylesheet">
  <link href="css/muonsach.css" rel="stylesheet">
</head>

<body>

  <div class="container py-5">
    <div class="card p-4 mx-auto" style="max-width: 700px;">

      <div class="text-center mb-4">
        <img src="http://localhost/thuvien/feane/images/<?php echo htmlspecialchars($sach['hinhanhsach']); ?>"
          alt="<?php echo htmlspecialchars($sach['tensach']); ?>"
          class="img-fluid book-image" style="max-height: 280px;">
      </div>

      <h3 class="text-center fw-bold mb-3"><?php echo htmlspecialchars($sach['tensach']); ?></h3>

      <p class="text-center text-muted mb-4">
        <b>Giá bán:</b> <?php echo number_format($sach['dongia']); ?> VNĐ &nbsp; | &nbsp;
        <b>Còn lại:</b> <?php echo $sach['soluong']; ?> cuốn
      </p>

      <form method="POST" class="px-3">

        <div class="mb-3">
          <label class="form-label fw-semibold">Họ và tên</label>
          <input type="text" name="hoten" class="form-control form-control-lg" placeholder="Nhập họ tên của bạn" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control form-control-lg" placeholder="Nhập email của bạn" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Số lượng mua</label>
          <input type="number" name="soluong" min="1" max="<?php echo $sach['soluong']; ?>"
            class="form-control form-control-lg" value="1" required>
        </div>

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">🛒 Xác nhận mua sách</button>
          <a href="index.php" class="btn btn-outline-secondary px-5 py-2 ms-2 fw-bold">⬅ Quay lại</a>
        </div>

      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>