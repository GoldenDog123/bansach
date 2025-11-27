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
  <link rel="stylesheet" href="css/chitiet_reviews.css">
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
              <button class="btn btn-main add-to-cart"
                data-id="<?php echo $sach['idsach']; ?>"
                data-name="<?php echo htmlspecialchars($sach['tensach']); ?>"
                data-price="<?php echo $sach['dongia']; ?>"
                data-img="<?php echo $sach['hinhanhsach']; ?>">
                🛒 Thêm vào giỏ
              </button>
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

  <!-- ===== PHẦN ĐÁNH GIÁ VÀ BÌNH LUẬN ===== -->
  <section class="reviews_section py-5" style="background: #0a0a0a;">
    <div class="container">
      <div class="row">
        <!-- ĐÁNH GIÁ SÁCH -->
        <div class="col-lg-4 mb-4">
          <div class="rating-card p-4 rounded" style="background: #1a1a1a; border: 1px solid rgba(255, 193, 7, 0.2);">
            <h4 class="text-white mb-4">⭐ Đánh giá sách</h4>
            
            <!-- Thống kê đánh giá -->
            <?php
            $sql_avg = "SELECT AVG(diem_danh_gia) as avg_rating, COUNT(*) as total_ratings FROM danh_gia WHERE idsach = $idsach";
            $result_avg = mysqli_query($ketnoi, $sql_avg);
            $avg_data = mysqli_fetch_assoc($result_avg);
            $avg_rating = round($avg_data['avg_rating'] ?? 0, 1);
            $total_ratings = $avg_data['total_ratings'] ?? 0;
            ?>
            
            <div class="avg-rating-display mb-4">
              <h2 class="text-warning"><?php echo $avg_rating; ?></h2>
              <div class="stars mb-2">
                <?php for($i = 1; $i <= 5; $i++): ?>
                  <i class="fa fa-star" style="color: <?php echo $i <= floor($avg_rating) ? '#ffc107' : '#555'; ?>; font-size: 20px;"></i>
                <?php endfor; ?>
              </div>
              <small class="text-muted"><?php echo $total_ratings; ?> đánh giá</small>
            </div>

            <!-- Form đánh giá -->
            <form id="ratingForm" class="mt-4">
              <div class="mb-3">
                <label class="form-label text-white">Mức độ hài lòng:</label>
                <div class="star-rating-input">
                  <?php for($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" style="display: none;">
                    <label for="star<?php echo $i; ?>" class="star-label" style="font-size: 28px; cursor: pointer; color: #ffc107; margin: 0 5px;">
                      <i class="fa fa-star"></i>
                    </label>
                  <?php endfor; ?>
                </div>
              </div>
              <button type="submit" class="btn btn-warning w-100">
                <i class="fa fa-send"></i> Gửi đánh giá
              </button>
            </form>

            <!-- Biểu đồ đánh giá -->
            <?php
            $sql_dist = "SELECT diem_danh_gia, COUNT(*) as count FROM danh_gia WHERE idsach = $idsach GROUP BY diem_danh_gia ORDER BY diem_danh_gia DESC";
            $result_dist = mysqli_query($ketnoi, $sql_dist);
            $ratings_dist = [];
            while($row = mysqli_fetch_assoc($result_dist)) {
              $ratings_dist[$row['diem_danh_gia']] = $row['count'];
            }
            ?>
            
            <div class="ratings-distribution mt-4">
              <?php for($i = 5; $i >= 1; $i--): ?>
                <?php $count = $ratings_dist[$i] ?? 0; ?>
                <div class="rating-bar d-flex align-items-center mb-2">
                  <small class="text-muted" style="width: 20px;"><?php echo $i; ?>⭐</small>
                  <div class="progress flex-grow-1 mx-2" style="height: 8px; background: #333;">
                    <div class="progress-bar bg-warning" style="width: <?php echo $total_ratings > 0 ? ($count / $total_ratings * 100) : 0; ?>%"></div>
                  </div>
                  <small class="text-muted" style="width: 30px; text-align: right;"><?php echo $count; ?></small>
                </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>

        <!-- BÌNH LUẬN -->
        <div class="col-lg-8">
          <div class="comments-card p-4 rounded" style="background: #1a1a1a; border: 1px solid rgba(255, 193, 7, 0.2);">
            <h4 class="text-white mb-4">💬 Bình luận từ khách hàng</h4>
            
            <!-- Form bình luận -->
            <?php if (isset($_SESSION['idnguoidung'])): ?>
            <form id="commentForm" class="mb-4 pb-4 border-bottom" style="border-color: rgba(255, 193, 7, 0.1) !important;">
              <div class="alert alert-info" style="background: rgba(23, 162, 184, 0.1); border-color: #17a2b8;">
                <small>👤 Đăng nhập là: <strong><?php echo htmlspecialchars($_SESSION['hoten'] ?? 'Khách'); ?></strong></small>
              </div>
              <div class="mt-3">
                <textarea id="commentText" class="form-control" rows="3" placeholder="Chia sẻ nhận xét của bạn..." style="background: #0a0a0a; color: #fff; border-color: #333;"></textarea>
              </div>
              <button type="submit" class="btn btn-warning mt-3">
                <i class="fa fa-comment"></i> Gửi bình luận
              </button>
            </form>
            <?php else: ?>
            <div class="alert alert-warning" style="background: rgba(255, 193, 7, 0.1); border-color: #ffc107; color: #ffc107;">
              <i class="fa fa-lock"></i> <strong>Bạn cần đăng nhập để bình luận</strong>
              <div class="mt-2">
                <a href="dangnhap.php" class="btn btn-sm btn-warning">
                  <i class="fa fa-sign-in"></i> Đăng nhập
                </a>
                <a href="dangky.php" class="btn btn-sm btn-outline-warning">
                  <i class="fa fa-user-plus"></i> Đăng ký
                </a>
              </div>
            </div>
            <?php endif; ?>

            <!-- Danh sách bình luận -->
            <div id="commentsList">
              <?php
              $sql_comments = "SELECT * FROM binh_luan WHERE idsach = $idsach AND trang_thai = 'approved' ORDER BY ngay_binh_luan DESC";
              $result_comments = mysqli_query($ketnoi, $sql_comments);
              $comments_count = mysqli_num_rows($result_comments);
              
              if ($comments_count > 0):
                while($comment = mysqli_fetch_assoc($result_comments)):
              ?>
                <div class="comment-item mb-4 pb-4" style="border-bottom: 1px solid rgba(255, 193, 7, 0.1);">
                  <div class="comment-header d-flex justify-content-between align-items-center mb-2">
                    <div>
                      <strong class="text-warning"><?php echo htmlspecialchars($comment['ho_ten']); ?></strong>
                      <small class="text-muted ms-2"><?php echo date('d/m/Y H:i', strtotime($comment['ngay_binh_luan'])); ?></small>
                    </div>
                  </div>
                  <p class="text-light mb-0" style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($comment['noi_dung'])); ?>
                  </p>
                </div>
              <?php 
                endwhile;
              else:
              ?>
                <p class="text-muted text-center py-4">
                  <i class="fa fa-comments"></i> Chưa có bình luận nào. Hãy là người đầu tiên!
                </p>
              <?php endif; ?>
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

    // Toast notification
    function showToast(message) {
      const toast = document.createElement('div');
      toast.className = 'toast';
      toast.innerHTML = `<i class="fa fa-info-circle"></i><span>${message}</span>`;
      
      if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
      }
      document.getElementById('toast-container').appendChild(toast);
      
      setTimeout(() => toast.classList.add('show'), 100);
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 500);
      }, 3000);
    }

    // Add to cart
    document.addEventListener('click', function(e) {
      if (e.target.closest('.add-to-cart')) {
        const btn = e.target.closest('.add-to-cart');
        let idsach = parseInt(btn.dataset.id);
        let tensach = btn.dataset.name;
        let dongia = parseInt(btn.dataset.price);
        let hinhanhsach = btn.dataset.img;

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
      }
    });

    // ===== XỬ LÝ ĐÁNH GIÁ SÁCH =====
    document.getElementById('ratingForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const rating = document.querySelector('input[name="rating"]:checked');
      
      if (!rating) {
        showToast("⚠️ Vui lòng chọn mức đánh giá");
        return;
      }

      try {
        const response = await fetch('xuly_danh_gia.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `idsach=<?php echo $idsach; ?>&diem=${rating.value}`
        });

        const result = await response.json();
        if (result.success) {
          showToast("✅ " + result.message);
          document.getElementById('ratingForm').reset();
          location.reload();
        } else {
          showToast("❌ " + result.message);
        }
      } catch (error) {
        showToast("❌ Lỗi: " + error.message);
      }
    });

    // ===== XỬ LÝ BÌNH LUẬN =====
    document.getElementById('commentForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const content = document.getElementById('commentText').value.trim();

      if (!content) {
        showToast("⚠️ Vui lòng nhập nội dung bình luận");
        return;
      }

      try {
        const response = await fetch('xuly_binh_luan.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `idsach=<?php echo $idsach; ?>&noi_dung=${encodeURIComponent(content)}`
        });

        const result = await response.json();
        if (result.success) {
          showToast("✅ " + result.message);
          document.getElementById('commentForm').reset();
          
          // Cập nhật comment real-time mà không cần reload
          addNewCommentToList(result.comment);
        } else if (result.requireLogin) {
          showToast("❌ Bạn cần đăng nhập để bình luận");
          setTimeout(() => window.location.href = 'dangnhap.php', 1500);
        } else {
          showToast("❌ " + result.message);
        }
      } catch (error) {
        showToast("❌ Lỗi: " + error.message);
      }
    });

    // Hàm thêm comment mới vào danh sách real-time
    function addNewCommentToList(comment) {
      const commentsList = document.getElementById('commentsList');
      
      // Nếu có thông báo "Chưa có bình luận", xóa nó
      const emptyMsg = commentsList.querySelector('.text-muted');
      if (emptyMsg) {
        emptyMsg.remove();
      }

      // Tạo HTML bình luận mới
      const commentHTML = `
        <div class="comment-item mb-4 pb-4" style="border-bottom: 1px solid rgba(255, 193, 7, 0.1); animation: fadeInUp 0.5s ease;">
          <div class="comment-header d-flex justify-content-between align-items-center mb-2">
            <div>
              <strong class="text-warning">${escapeHtml(comment.ho_ten)}</strong>
              <small class="text-muted ms-2">Vừa xong</small>
            </div>
          </div>
          <p class="text-light mb-0" style="line-height: 1.6;">
            ${escapeHtml(comment.noi_dung).replace(/\n/g, '<br>')}
          </p>
        </div>
      `;

      // Thêm vào đầu danh sách
      commentsList.insertAdjacentHTML('afterbegin', commentHTML);
    }

    // Hàm escape HTML để tránh XSS
    function escapeHtml(text) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text.replace(/[&<>"']/g, m => map[m]);
    }

    // ===== INTERACTIVE STAR RATING INPUT =====
    document.querySelectorAll('.star-label').forEach(label => {
      label.addEventListener('mouseover', function() {
        const rating = this.previousElementSibling.value;
        document.querySelectorAll('.star-label').forEach((l, i) => {
          l.style.color = (5 - i) <= rating ? '#ffc107' : '#555';
        });
      });
    });

    document.querySelector('.star-rating-input')?.addEventListener('mouseleave', function() {
      const checked = document.querySelector('input[name="rating"]:checked');
      document.querySelectorAll('.star-label').forEach((l, i) => {
        if (checked) {
          l.style.color = (5 - i) <= checked.value ? '#ffc107' : '#555';
        } else {
          l.style.color = '#555';
        }
      });
    });
  </script>
  <?php include 'footer.php'; ?>
</body>


</html>