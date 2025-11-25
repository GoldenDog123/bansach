<?php
require_once('ketnoi.php');
session_start();

// ===== THÔNG TIN NGƯỜI DÙNG ĐĂNG NHẬP =====
$logged_name = $_SESSION['hoten'] ?? '';
$logged_email = $_SESSION['email'] ?? '';

// ===== THÔNG TIN SÁCH =====
$selected_books = [];
if (isset($_GET['idsach'])) {
  $ids = [(int)$_GET['idsach']];
} elseif (isset($_GET['ids'])) {
  $ids = array_map('intval', explode(',', $_GET['ids']));
} else {
  $ids = [];
}

if (!empty($ids)) {
  $id_str = implode(',', $ids);
  $q = mysqli_query($ketnoi, "
        SELECT sach.idsach, sach.tensach, tacgia.tentacgia, loaisach.tenloaisach 
        FROM sach 
        LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia 
        LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach 
        WHERE sach.idsach IN ($id_str)
    ");
  while ($r = mysqli_fetch_assoc($q)) {
    $selected_books[] = $r;
  }
}

// ====== XỬ LÝ GỬI FORM ======
// ====== XỬ LÝ GỬI FORM ======
$message_form = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hoten = trim($_POST['hoten']);
  $email = trim($_POST['email']);
  $book_ids = $_POST['book_ids'] ?? [];
  $quantities = $_POST['soluong'] ?? [];
  $ngaydat = date('Y-m-d');

  if (empty($hoten) || empty($email) || empty($book_ids)) {
    $message_form = '<div class="alert alert-danger">⚠️ Vui lòng nhập đầy đủ thông tin và chọn ít nhất 1 sách.</div>';
  } else {

    // Tìm người dùng
    $stmt_user = mysqli_prepare($ketnoi, "SELECT idnguoidung FROM nguoidung WHERE email=?");
    mysqli_stmt_bind_param($stmt_user, 's', $email);
    mysqli_stmt_execute($stmt_user);
    mysqli_stmt_bind_result($stmt_user, $uid);

    if (mysqli_stmt_fetch($stmt_user)) {
      $idnguoidung = $uid;
    } else {
      $idnguoidung = null;
    }
    mysqli_stmt_close($stmt_user);

    if (!$idnguoidung) {
      $message_form = '<div class="alert alert-danger">❌ Không tìm thấy tài khoản người dùng.</div>';
    } else {

      // 🔥 1) Tạo đơn hàng
      $stmt_don = mysqli_prepare($ketnoi, "
        INSERT INTO donhang (idnguoidung, ngaydat, trangthai)
        VALUES (?, ?, 'cho_xu_ly')
      ");
      mysqli_stmt_bind_param($stmt_don, 'is', $idnguoidung, $ngaydat);
      mysqli_stmt_execute($stmt_don);
      $iddonhang = mysqli_insert_id($ketnoi);
      mysqli_stmt_close($stmt_don);

      // 🔥 2) Thêm chi tiết đơn hàng
      $inserted = 0;

      foreach ($book_ids as $idsach) {
        $sl = max(1, intval($quantities[$idsach] ?? 1));

        $stmt_ct = mysqli_prepare($ketnoi, "
          INSERT INTO donhang_chitiet (iddonhang, idsach, soluong)
          VALUES (?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt_ct, 'iii', $iddonhang, $idsach, $sl);

        if (mysqli_stmt_execute($stmt_ct)) {
          $inserted++;
          // Trừ kho
          mysqli_query($ketnoi, "
            UPDATE sach 
            SET soluong = soluong - $sl 
            WHERE idsach = $idsach AND soluong >= $sl
          ");
        }

        mysqli_stmt_close($stmt_ct);
      }

      if ($inserted > 0) {
        $message_form = '<div class="alert alert-success">✅ Đặt hàng thành công! Mã đơn: ' . $iddonhang . '</div>';
      } else {
        $message_form = '<div class="alert alert-danger">❌ Không thể tạo đơn hàng.</div>';
      }
    }
  }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="shortcut icon" href="images/Book.png" type="image/png">
  <title>Bán sách</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/book.css">
  <link rel="stylesheet" href="css/footer.css">
</head>

<body>
  <?php include 'header.php'; ?>
  <!-- ===== FORM MƯỢN SÁCH ===== -->
  <section class="book_section py-5">
    <div class="container">
      <div class="card p-4 shadow-lg border-0" style="border-radius: 15px;">
        <h3 class="mb-4 text-center text-warning">
          <i class="fa fa-shopping-cart me-2"></i> Xác nhận đơn hàng
        </h3>

        <form method="POST">
          <!-- Họ tên -->
          <div class="form-group mb-3">
            <label>Họ và tên</label>
            <input type="text" name="hoten" class="form-control bg-dark text-white border-secondary"
              value="<?php echo htmlspecialchars($logged_name); ?>"
              placeholder="Nhập họ và tên..." required>
          </div>

          <!-- Email -->
          <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control bg-dark text-white border-secondary"
              value="<?php echo htmlspecialchars($logged_email); ?>"
              placeholder="Nhập email của bạn..." required>
          </div>

          <!-- Ngày mượn -->
          <div class="form-group mb-3">
            <label>Ngày đặt</label>
            <input type="date" name="ngaymuon" class="form-control bg-dark text-white border-secondary"
              value="<?php echo date('Y-m-d'); ?>" readonly>
          </div>

          <!-- Danh sách sách đã chọn -->
          <?php if (!empty($selected_books)): ?>
            <div class="form-group mb-3">
              <label>📚 Danh sách sách bạn sẽ mua:</label>
              <ul class="book-list list-unstyled bg-dark text-white p-3 rounded">
                <?php foreach ($selected_books as $b): ?>
                  <li class="py-2 border-bottom border-secondary">
                    <i class="fa fa-book me-2 text-warning"></i>
                    <b><?php echo htmlspecialchars($b['tensach']); ?></b>
                    — <small><?php echo htmlspecialchars($b['tentacgia']); ?> (<?php echo htmlspecialchars($b['tenloaisach']); ?>)</small>

                    <input type="hidden" name="book_ids[]" value="<?php echo $b['idsach']; ?>">

                    <input type="number"
                      name="soluong[<?php echo $b['idsach']; ?>]"
                      class="form-control bg-dark text-white border-secondary mt-2"
                      value="1" min="1" required>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <div class="alert alert-warning text-center">⚠️ Bạn chưa chọn sách nào để mua!</div>
          <?php endif; ?>

          <!-- Nút xác nhận -->
          <div class="text-center mt-4">
            <button type="submit" class="btn btn-warning px-5 py-2 fw-bold rounded-pill">
              💳 Thanh toán
            </button>
          </div>
        </form>

        <!-- Hiển thị thông báo -->
        <div class="mt-4">
          <?php echo $message_form; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <?php include 'footer.php'; ?>

  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="js/bootstrap.js"></script>
</body>

</html>