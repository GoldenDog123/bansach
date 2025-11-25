<?php
include('ketnoi.php');

// 🟡 Kiểm tra và lấy thông tin sách
if (isset($_GET['idsach'])) {
  $idsach = intval($_GET['idsach']);
  $sql = "SELECT sach.*, loaisach.tenloaisach, tacgia.tentacgia 
          FROM sach
          LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach
          LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia
          WHERE sach.idsach = $idsach";
  $result = mysqli_query($ketnoi, $sql);
  $sach = mysqli_fetch_assoc($result);
  // Lấy 4 sách gợi ý cùng thể loại, không lấy sách hiện tại
  $sql_goiy = "SELECT sach.*, loaisach.tenloaisach, tacgia.tentacgia 
             FROM sach
             LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach
             LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia
             WHERE sach.idloaisach = {$sach['idloaisach']}
             AND sach.idsach != {$sach['idsach']}
             ORDER BY RAND() ";
  $goiy_result = mysqli_query($ketnoi, $sql_goiy);

  // giữ nguyên mysqli_result → không fetch_all
  $goiy = $goiy_result;


  if (!$sach) {
    echo "<div class='container py-5 text-center text-white'><h3>Không tìm thấy sách!</h3></div>";
    exit;
  }
} else {
  echo "<div class='container py-5 text-center text-white'><h3>Thiếu mã sách!</h3></div>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($sach['tensach']); ?> - Thư viện</title>

  <!-- Liên kết CSS -->
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/chitiet.css">
  <link rel="stylesheet" href="css/footer.css">

</head>

<body>

  <?php
  // 🟡 Gọi header và báo cho nó biết đây là trang chi tiết
  $pageType = 'detail';
  include 'header.php';
  ?>

  <!-- ===== CHI TIẾT SÁCH ===== -->
  <section class="book_section py-5">
    <div class="container py-4">
      <div class="book-card row g-0">
        <!-- ẢNH SÁCH -->
        <div class="col-md-5">
          <img src="images/<?php echo htmlspecialchars($sach['hinhanhsach']); ?>"
            alt="<?php echo htmlspecialchars($sach['tensach']); ?>" class="book-image">
        </div>

        <!-- THÔNG TIN -->
        <div class="col-md-7">
          <div class="book-info">
            <h2 class="book-title mb-3"><?php echo htmlspecialchars($sach['tensach']); ?></h2>

            <div class="book-meta mb-3">
              <p><strong>📚 Thể loại:</strong> <?php echo htmlspecialchars($sach['tenloaisach']); ?></p>
              <p><strong>✍️ Tác giả:</strong> <?php echo htmlspecialchars($sach['tentacgia']); ?></p>
              <p><strong>📦 Số lượng còn:</strong> <?php echo htmlspecialchars($sach['soluong']); ?> cuốn</p>
            </div>

            <?php if (!empty($sach['dongia'])): ?>
              <p class="book-price">💰 Giá: <?php echo number_format($sach['dongia']); ?> VNĐ</p>
            <?php endif; ?>

            <p style="text-align: justify;"><?php echo nl2br(htmlspecialchars($sach['mota'])); ?></p>

            <div class="mt-4 d-flex flex-wrap gap-3">
              <a href="book.php?idsach=<?php echo $sach['idsach']; ?>" class="btn btn-main">
                📘 Mua Sách
              </a>
              <a href="menu.php" class="btn btn-back">
                ⬅ Quay lại
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ===== SÁCH GỢI Ý ===== -->
  <?php if (mysqli_num_rows($goiy) > 0): ?>
    <section class="book_section recommended_books py-5">
      <div class="container">
        <h3 class="text-white mb-4">📖 Sách gợi ý</h3>
        <div class="recommended_books_wrapper position-relative">
          <button class="arrow-btn left-arrow"><i class="fa fa-chevron-left"></i></button>

          <div class="recommended_books_row d-flex gap-3 pb-2">
            <?php while ($item = mysqli_fetch_assoc($goiy)): ?>
              <div class="box flex-shrink-0" style="width: 220px;">
                <div class="img-box">
                  <img src="images/<?php echo htmlspecialchars($item['hinhanhsach']); ?>" alt="">
                </div>
                <div class="detail-box">
                  <h5><?php echo htmlspecialchars($item['tensach']); ?></h5>
                  <p class="text-muted"><?php echo htmlspecialchars($item['tentacgia']); ?></p>
                  <h6><?php echo htmlspecialchars($item['tenloaisach']); ?></h6>
                  <div class="options">
                    <a href="chitietsach.php?idsach=<?php echo $item['idsach']; ?>" class="btn btn-warning">
                      <i class="fa fa-info-circle"></i> Chi tiết
                    </a>
                    <a href="book.php?idsach=<?php echo $item['idsach']; ?>" class="btn btn-outline-primary">
                      <i class="fa fa-book"></i> Mua
                    </a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>

          <button class="arrow-btn right-arrow"><i class="fa fa-chevron-right"></i></button>
        </div>

      </div>
    </section>
  <?php endif; ?>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.recommended_books_wrapper').forEach(wrapper => {
      const row = wrapper.querySelector('.recommended_books_row');
      const leftBtn = wrapper.querySelector('.left-arrow');
      const rightBtn = wrapper.querySelector('.right-arrow');

      leftBtn.addEventListener('click', () => {
        row.scrollBy({
          left: -250,
          behavior: 'smooth'
        });
      });

      rightBtn.addEventListener('click', () => {
        row.scrollBy({
          left: 250,
          behavior: 'smooth'
        });
      });
    });
  </script>
  <?php include 'footer.php'; ?>
</body>


</html>