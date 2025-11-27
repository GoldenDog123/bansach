<?php
require_once('ketnoi.php');
session_start();

// Lấy danh sách thể loại & tác giả
$loaisach = mysqli_query($ketnoi, "SELECT * FROM loaisach ORDER BY tenloaisach ASC");
$tacgia = mysqli_query($ketnoi, "SELECT * FROM tacgia ORDER BY tentacgia ASC");

// Bộ lọc
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$idloaisach = isset($_GET['idloaisach']) ? intval($_GET['idloaisach']) : 0;
$idtacgia = isset($_GET['idtacgia']) ? intval($_GET['idtacgia']) : 0;

// Câu truy vấn sách
$sql = "SELECT sach.*, loaisach.tenloaisach, tacgia.tentacgia 
        FROM sach
        LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach
        LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia
        WHERE 1=1";

if ($keyword !== '') {
  $kw = mysqli_real_escape_string($ketnoi, $keyword);
  $sql .= " AND (sach.tensach LIKE '%$kw%' OR tacgia.tentacgia LIKE '%$kw%' OR loaisach.tenloaisach LIKE '%$kw%')";
}
if ($idloaisach > 0) {
  $sql .= " AND sach.idloaisach = $idloaisach";
}
if ($idtacgia > 0) {
  $sql .= " AND sach.idtacgia = $idtacgia";
}

$sql .= " ORDER BY sach.tensach ASC";
$books = mysqli_query($ketnoi, $sql);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kho sách - Thư Viện Trường Học</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link href="css/header.css" rel="stylesheet">
  <link rel="stylesheet" href="css/menu.css">
  <link href="css/footer.css" rel="stylesheet">
</head>

<body class="menu-page">
  <?php include 'header.php'; ?>
  <?php
  // --- Lấy danh sách thể loại & tác giả ---
  $loaisach = mysqli_query($ketnoi, "SELECT * FROM loaisach ORDER BY tenloaisach ASC");
  $tacgia = mysqli_query($ketnoi, "SELECT * FROM tacgia ORDER BY tentacgia ASC");

  // --- Bộ lọc ---
  $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
  $idloaisach = isset($_GET['idloaisach']) ? intval($_GET['idloaisach']) : 0;
  $idtacgia = isset($_GET['idtacgia']) ? intval($_GET['idtacgia']) : 0;
  $new = isset($_GET['new']);
  $featured = isset($_GET['featured']);

  // --- Truy vấn sách ---
  $sql = "SELECT sach.*, loaisach.tenloaisach, tacgia.tentacgia 
        FROM sach
        LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach
        LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia
        WHERE 1=1";

  if ($keyword !== '') {
    $kw = mysqli_real_escape_string($ketnoi, $keyword);
    $sql .= " AND (sach.tensach COLLATE utf8mb4_vietnamese_ci LIKE '%$kw%' 
             OR tacgia.tentacgia COLLATE utf8mb4_vietnamese_ci LIKE '%$kw%' 
             OR loaisach.tenloaisach COLLATE utf8mb4_vietnamese_ci LIKE '%$kw%')";
  }

  if ($idloaisach > 0) {
    $sql .= " AND sach.idloaisach = $idloaisach";
  }

  if ($idtacgia > 0) {
    $sql .= " AND sach.idtacgia = $idtacgia";
  }

  // ✅ Sách mới trong 30 ngày gần nhất
  if ($new) {
    $sql .= " AND sach.ngaynhap >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $sql .= " ORDER BY sach.ngaynhap DESC";
  }
  // ✅ Sách nổi bật (được mượn nhiều)
  elseif ($featured) {
    $sql .= " AND sach.luotmuon >= 10";
    $sql .= " ORDER BY sach.luotmuon DESC";
  }
  // ✅ Mặc định: hiển thị toàn bộ theo tên
  else {
    $sql .= " ORDER BY sach.tensach ASC";
  }

  $books = mysqli_query($ketnoi, $sql);

  ?>

  <!-- DANH SÁCH THỂ LOẠI -->
  <div class="container">
    <div class="category-scroll">
      <ul class="filters_menu">

        <!-- TẤT CẢ -->
        <li class="<?= ($idloaisach == 0 && !$new && !$featured) ? 'active' : ''; ?>">
          <a href="menu.php" class="filter-link">Tất cả</a>
        </li>

        <!-- SÁCH MỚI VỀ -->
        <li class="<?= ($new) ? 'active' : ''; ?>">
          <a href="menu.php?new=1" class="filter-link">Sách mới về</a>
        </li>

        <!-- SÁCH NỔI BẬT -->
        <li class="<?= ($featured) ? 'active' : ''; ?>">
          <a href="menu.php?featured=1" class="filter-link">Sách nổi bật</a>
        </li>

        <!-- CÁC THỂ LOẠI -->
        <?php
        $maxVisible = 4; // số thể loại hiển thị trực tiếp
        $count = 0;
        mysqli_data_seek($loaisach, 0); // reset con trỏ danh sách thể loại

        while ($row = mysqli_fetch_assoc($loaisach)) {
          $active = ($idloaisach == $row['idloaisach']) ? 'active' : '';

          // Nếu số lượng chưa đạt maxVisible → hiển thị bình thường
          if ($count < $maxVisible) {
            echo '<li class="' . $active . '" style="white-space:nowrap;">
                <a href="menu.php?idloaisach=' . $row['idloaisach'] . '" class="filter-link">'
              . htmlspecialchars($row['tenloaisach']) .
              '</a>
              </li>';
          } else {
            // Các thể loại còn lại vẫn in ra nhưng nằm trong scroll
            echo '<li class="' . $active . '" style="white-space:nowrap;">
                <a href="menu.php?idloaisach=' . $row['idloaisach'] . '" class="filter-link">'
              . htmlspecialchars($row['tenloaisach']) .
              '</a>
              </li>';
          }
          $count++;
        }
        ?>
      </ul>
    </div>
  </div>



  <!-- PHẦN DANH SÁCH SÁCH -->
  <section>
    <div class="container">
      <!-- DANH SÁCH SÁCH -->
      <div class="row">
        <?php while ($r = mysqli_fetch_assoc($books)) {
          $img = 'images/' . $r['hinhanhsach'];
        ?>
          <div class="col-sm-6 col-lg-4 mb-4">
            <div class="box">
              <div class="img-box position-relative">
                <img src="<?= htmlspecialchars($img); ?>" alt="<?= htmlspecialchars($r['tensach']); ?>">
              </div>
              <div class="detail-box">
                <h5 class="fw-bold text-truncate"><?= htmlspecialchars($r['tensach']); ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($r['tentacgia']); ?></p>
                <h6 class="text-secondary small mb-3"><?= htmlspecialchars($r['tenloaisach']); ?></h6>

                <div class="options d-flex justify-content-center gap-2 flex-wrap">
                  <a href="chitietsach.php?idsach=<?= $r['idsach']; ?>" class="btn btn-outline-primary rounded-pill px-3">
                    <i class="fa fa-info-circle me-1"></i> Chi tiết
                  </a>
                  <button class="btn btn-success rounded-pill px-3 add-to-cart"
                    data-id="<?= $r['idsach']; ?>"
                    data-name="<?= htmlspecialchars($r['tensach']); ?>"
                    data-price="<?= $r['dongia']; ?>"
                    data-img="<?= $r['hinhanhsach']; ?>">
                    <i class="fa fa-cart-plus me-1"></i> Thêm vào giỏ
                  </button>
                  <a href="javascript:void(0);"
                    class="btn btn-outline-danger rounded-pill px-3 favorite-btn <?= in_array($r['idsach'], $_SESSION['favorites'] ?? []) ? 'liked' : ''; ?>"
                    data-id="<?= $r['idsach']; ?>">
                    <i class="fa fa-heart me-1"></i> Thích
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>


  <!-- SCRIPT -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Footer -->
  <?php include 'footer.php'; ?>

  <!-- Toast Container -->
  <div id="toast-container"></div>
  <script>
    function showToast(message) {
      const toast = $(`
    <div class="toast">
      <i class="fa fa-info-circle"></i>
      <span>${message}</span>
    </div>
  `);
      $("#toast-container").append(toast);
      setTimeout(() => toast.addClass("show"), 100);
      setTimeout(() => {
        toast.removeClass("show");
        setTimeout(() => toast.remove(), 500);
      }, 3000);
    }

    $(document).on("click", ".favorite-btn", function() {
      const btn = $(this);
      const idsach = btn.data("id");

      $.ajax({
        url: "xuly_yeuthich.php",
        type: "POST",
        data: {
          idsach: idsach
        },
        dataType: "json",
        success: function(res) {
          if (res.status === "added") {
            btn.addClass("liked");
            showToast("✅ Đã thêm vào danh sách yêu thích");
          } else if (res.status === "removed") {
            btn.removeClass("liked");
            showToast("💔 Đã xóa khỏi danh sách yêu thích");
          } else if (res.status === "error") {
            showToast(res.message);
          }
        },
        error: function() {
          showToast("⚠️ Lỗi kết nối máy chủ");
        },
      });
    });
  </script>
  <script>
    $(document).on("click", ".add-to-cart", function() {
      let idsach = $(this).data("id");
      let tensach = $(this).data("name");
      let dongia = $(this).data("price");
      let hinhanhsach = $(this).data("img");

      // Lấy giỏ hàng từ localStorage
      let cart = JSON.parse(localStorage.getItem('cart')) || [];

      // Kiểm tra sách có tồn tại trong giỏ không
      let item = cart.find(i => i.idsach === idsach);
      if (item) {
        item.soluong += 1;
      } else {
        cart.push({
          idsach: idsach,
          tensach: tensach,
          dongia: dongia,
          hinhanhsach: hinhanhsach,
          soluong: 1
        });
      }

      // Lưu vào localStorage
      localStorage.setItem('cart', JSON.stringify(cart));

      // Dispatch event để header.php cập nhật
      window.dispatchEvent(new Event('cartUpdated'));

      showToast("🛒 Đã thêm vào giỏ hàng");
    });
  </script>

  <!-- JS -->
  <script>
    const scrollArea = document.querySelector('.category-scroll');
    let isScrolling = false;
    let scrollVelocity = 0;
    scrollArea.addEventListener('wheel', function(e) {
      if (e.deltaY === 0) return;
      e.preventDefault();
      const speed = 6;
      scrollArea.scrollBy({
        left: e.deltaY * speed,
        behavior: 'smooth'
      });
    });

    function momentumScroll() {
      isScrolling = true;

      // Cập nhật vị trí scroll
      scrollArea.scrollLeft += scrollVelocity;

      // Nếu tốc độ nhỏ → dừng
      if (Math.abs(scrollVelocity) > 0) {
        requestAnimationFrame(momentumScroll);
      } else {
        scrollVelocity = 0;
        isScrolling = false;
      }
    }
  </script>

  <script>
    const toggleBtn = document.getElementById("userToggle");
    const dropdown = document.getElementById("userDropdown");

    if (toggleBtn && dropdown) {
      toggleBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdown.classList.toggle("show");
      });

      // Đóng menu khi click ra ngoài
      document.addEventListener("click", (e) => {
        if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
          dropdown.classList.remove("show");
        }
      });

      // Mở menu khi hover (tùy chọn)
      toggleBtn.addEventListener("mouseenter", () => dropdown.classList.add("show"));
      dropdown.addEventListener("mouseleave", () => dropdown.classList.remove("show"));
    }
  </script>

  <!-- Bootstrap & Custom JS -->
  <script src="js/bootstrap.js"></script>
  <script src="js/custom.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Isotope Layout (cho filter sách) -->
  <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>


</body>

</html>