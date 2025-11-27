<?php
require_once('ketnoi.php');
session_start();

// Messages
$message_form = '';
$message_modal = '';
$modal_to_open = 0;

// Helper: get or create user by email
function get_or_create_user($ketnoi, $hoten, $email)
{
    $hoten = trim($hoten);
    $email = trim($email);

    $stmt = mysqli_prepare($ketnoi, "SELECT idnguoidung FROM nguoidung WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $uid);
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        return (int)$uid;
    }
    mysqli_stmt_close($stmt);

    $default_pass = password_hash('12345', PASSWORD_DEFAULT);
    $vaitro = 'hoc_sinh';

    $insert = mysqli_prepare($ketnoi, "INSERT INTO nguoidung (hoten, email, matkhau, vaitro) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert, 'ssss', $hoten, $email, $default_pass, $vaitro);

    if (mysqli_stmt_execute($insert)) {
        $newid = mysqli_insert_id($ketnoi);
        mysqli_stmt_close($insert);
        return (int)$newid;
    } else {
        mysqli_stmt_close($insert);
        return false;
    }
}

// =======================
// XỬ LÝ ĐẶT MUA
// =======================
// =======================
// XỬ LÝ ĐẶT MUA (KIỂU A - 1 đơn nhiều sách)
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hoten = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $book_ids = $_POST['book_ids'] ?? [];
    $quantities = $_POST['quantities'] ?? [];

    if ($hoten === '' || $email === '' || empty($book_ids)) {
        $message_form = '<div class="alert alert-danger">⚠️ Vui lòng nhập đầy đủ thông tin và chọn ít nhất 1 sản phẩm.</div>';
    } else {

        // Get/create user
        $idnguoidung = get_or_create_user($ketnoi, $hoten, $email);
        if (!$idnguoidung) {
            $message_form = '<div class="alert alert-danger">❌ Không thể tạo tài khoản khách hàng.</div>';
        } else {

            $tongtien = 0;
            $items = [];

            // Kiểm tra từng sách
            foreach ($book_ids as $i => $idsach) {
                $idsach = intval($idsach);
                $soluong = intval($quantities[$i]);

                $rs = mysqli_query($ketnoi, "SELECT tensach, soluong, dongia FROM sach WHERE idsach=$idsach");
                $s = mysqli_fetch_assoc($rs);

                if (!$s || $soluong <= 0 || $soluong > $s['soluong']) {
                    $message_form = '<div class="alert alert-danger">❌ Số lượng sách không hợp lệ: '
                        . htmlspecialchars($s['tensach']) . '</div>';
                    return;
                }

                $dongia = $s['dongia'];
                $thanhtien = $dongia * $soluong;

                $tongtien += $thanhtien;

                $items[] = [
                    'idsach' => $idsach,
                    'soluong' => $soluong,
                    'dongia' => $dongia,
                    'thanhtien' => $thanhtien
                ];
            }

            // Tạo đơn hàng
            $ngaydat = date('Y-m-d H:i:s');
            mysqli_query(
                $ketnoi,
                "INSERT INTO donhang (idnguoidung, tongtien, trangthai, ngaydat)
                 VALUES ($idnguoidung, $tongtien, 'cho_duyet', '$ngaydat')"
            );
            $iddonhang = mysqli_insert_id($ketnoi);

            // Lưu từng chi tiết
            foreach ($items as $item) {
                mysqli_query(
                    $ketnoi,
                    "INSERT INTO donhang_chitiet (iddonhang, idsach, soluong, dongia, thanhtien)
                     VALUES ($iddonhang, {$item['idsach']}, {$item['soluong']}, {$item['dongia']}, {$item['thanhtien']})"
                );

                // Trừ kho
                mysqli_query(
                    $ketnoi,
                    "UPDATE sach SET soluong = soluong - {$item['soluong']}
                     WHERE idsach = {$item['idsach']}"
                );
            }

            $message_form = '<div class="alert alert-success">🎉 Đặt hàng thành công! Cảm ơn bạn ❤️</div>';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="keywords" content="thư viện, sách, mua sách, đọc sách, học tập" />
    <meta name="description" content="Hệ thống quản lý thư viện trường học" />
    <meta name="author" content="Thư viện Trường Học" />
    <link rel="shortcut icon" href="images/Book.png" type="image/png">

    <title>Cửa Hàng Sách    </title>

    <!-- bootstrap core css -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <!-- font awesome -->
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <!-- custom styles -->
    <link href="css/style.css" rel="stylesheet" />
    <link href="css/responsive.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/header.css">
    <link href="css/index.css" rel="stylesheet">
    <link href="css/footer.css" rel="stylesheet">
</head>

<body class="index-page">
    <div class="hero_area">
        <div class="bg-box">
            <img src="images/baner2.png" alt="Banner Thư viện">
        </div>
        <?php include 'header.php'; ?>
        <!-- slider section -->
        <section class="slider_section ">
            <div id="customCarousel1" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">

                    <div class="carousel-item active">
                        <div class="container ">
                            <div class="row">
                                <div class="col-md-7 col-lg-6 ">
                                    <div class="detail-box">
                                        <h1>Kho Sách Khổng Lồ</h1>
                                        <p>
                                            Nơi lưu trữ hàng ngàn đầu sách hay dành cho học sinh, sinh viên và giáo
                                            viên.
                                            Bạn có thể dễ dàng tìm kiếm và mua sách chỉ với vài cú click chuột.
                                        </p>
                                        <div class="btn-box">
                                            <a href="menu.php" class="btn1">Khám phá ngay</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item ">
                        <div class="container ">
                            <div class="row">
                                <div class="col-md-7 col-lg-6 ">
                                    <div class="detail-box">
                                        <h1>Sách Mới Về</h1>
                                        <p>
                                            Cập nhật nhanh các đầu sách mới nhất, đa dạng thể loại: văn học, khoa học,
                                            công nghệ, và kỹ năng sống.
                                        </p>
                                        <div class="btn-box">
                                            <a href="menu.php" class="btn1">Xem ngay</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="container ">
                            <div class="row">
                                <div class="col-md-7 col-lg-6 ">
                                    <div class="detail-box">
                                        <h1>Mua sách</h1>
                                        <p>
                                            Hãy chọn sách yêu thích của bạn và đăng ký mua ngay hôm nay.
                                            Hệ thống giúp bạn quản lý lịch sử mua dễ dàng, nhanh chóng.
                                        </p>
                                        <div class="btn-box">
                                            <a href="book.php" class="btn1">Mua Ngay</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="container">
                    <ol class="carousel-indicators">
                        <li data-target="#customCarousel1" data-slide-to="0" class="active"></li>
                        <li data-target="#customCarousel1" data-slide-to="1"></li>
                        <li data-target="#customCarousel1" data-slide-to="2"></li>
                    </ol>
                </div>
            </div>
        </section>
        <!-- end slider -->
    </div>

    <!-- =========================
       SÁCH NỔI BẬT
       ========================= -->
    <!-- Offer Section (Sách nổi bật) -->
    <section class="offer_section layout_padding-bottom">
        <div class="offer_container">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="box">
                            <div class="img-box"><img src="images/Capture.png" alt=""></div>
                            <div class="detail-box">
                                <h5>Sách Nổi Bật</h5>
                                <h6><span>Top</span> danh sách</h6>
                                <a href="menu.php">Xem ngay</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box">
                            <div class="img-box"><img src="images/1.png" alt=""></div>
                            <div class="detail-box">
                                <h5>Sách Được Yêu Thích</h5>
                                <h6><span>100+</span> Lượt mua</h6>
                                <a href="menu.php">Khám phá</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- =========================
   DANH SÁCH SÁCH YÊU THÍCH
========================= -->
    <section class="about_section layout_padding" style="background-color: #1e1f26;">
        <div class="container">
            <div class="heading_container heading_center mb-5">
                <h2 class="fw-bold text-light">
                    📚 Kho Sách Cửa hàng
                </h2>
                <p class="text-secondary">Khám phá các cuốn sách nổi bật trong thư viện của chúng tôi</p>
            </div>

            <div class="row g-4 justify-content-center">
                <?php
                // Lấy 8 sách đầu tiên
                $sql_all = "SELECT sach.*, loaisach.tenloaisach, tacgia.tentacgia
                  FROM sach
                  LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach
                  LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia
                  ORDER BY sach.tensach ASC
                  LIMIT 8";
                $res = mysqli_query($ketnoi, $sql_all);

                if ($res && mysqli_num_rows($res) > 0) {
                    while ($r = mysqli_fetch_assoc($res)) {
                        $img = 'images/' . $r['hinhanhsach'];
                        $idsach = (int)$r['idsach'];
                ?>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="card book-card shadow-sm border-0 rounded-4 overflow-hidden h-100 position-relative">

                                <!-- Nút yêu thích -->
                                <button
                                    class="favorite-btn <?php echo in_array($r['idsach'], $_SESSION['favorites'] ?? []) ? 'liked' : ''; ?>"
                                    data-id="<?php echo $r['idsach']; ?>">
                                    <i class="fa fa-heart"></i>
                                </button>

                                <div class="overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top img-hover-scale"
                                        style="height:260px; object-fit:cover;">
                                </div>

                                <div class="card-body text-center d-flex flex-column bg-dark text-light">
                                    <h5 class="fw-bold text-truncate" title="<?php echo htmlspecialchars($r['tensach']); ?>">
                                        <?php echo htmlspecialchars($r['tensach']); ?>
                                    </h5>
                                    <p class="text-secondary small mb-3">
                                        <?php echo htmlspecialchars($r['tentacgia']); ?> •
                                        <?php echo htmlspecialchars($r['tenloaisach']); ?>
                                    </p>
                                    <div class="mt-auto d-flex justify-content-center gap-2">
                                        <a href="chitietsach.php?idsach=<?php echo $idsach; ?>"
                                            class="btn btn-sm btn-primary rounded-pill px-3">
                                            Chi tiết
                                        </a>
                                        <button class="btn btn-sm btn-success rounded-pill px-3 add-to-cart"
                                            data-id="<?php echo $idsach; ?>"
                                            data-name="<?php echo htmlspecialchars($r['tensach']); ?>"
                                            data-price="<?php echo $r['dongia']; ?>"
                                            data-img="<?php echo $r['hinhanhsach']; ?>">
                                            <i class="fa fa-cart-plus me-1"></i> Giỏ
                                        </button>
                                        <a href="book.php?idsach=<?php echo $idsach; ?>"
                                            class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3">
                                            Mua
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<p class="text-center text-muted">Hiện chưa có sách trong thư viện.</p>';
                }
                ?>
            </div>

            <!-- Nút Xem Thêm -->
            <div class="text-center mt-5">
                <a href="menu.php" class="btn btn-warning px-5 py-2 fw-bold rounded-pill shadow-sm">
                    Xem thêm
                </a>
            </div>
        </div>

        <!-- Script -->
        <script>
            document.querySelectorAll(".favorite-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    this.classList.toggle("active");
                });
            });
        </script>
    </section>
    <!-- =========================
       GIỚI THIỆU THƯ VIỆN
       ========================= -->
    <section class="about_section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-6 ">
                    <div class="img-box">
                        <img src="images/books.png" alt="" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-box">
                        <div class="heading_container">
                            <h2>Giới thiệu </h2>
                        </div>
                        <p>
                            Trang bán sách là không gian học tập và nghiên cứu, cung cấp hàng ngàn đầu sách đa
                            dạng: văn học, khoa học,
                            công nghệ, kỹ năng và tài liệu tham khảo cho giáo viên và học sinh. Chúng tôi hỗ trợ mua
                            sách trực tuyến để giúp
                            việc tra cứu và học tập thuận tiện hơn.
                        </p>
                        <a href="about.php">Xem thêm</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Thông báo nhỏ nút yêu thích -->
    <div id="toast-container"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <!-- Footer -->
    <?php include 'footer.php'; ?>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/custom.js"></script>
</body>

</html>