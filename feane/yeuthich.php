<?php
session_start();
require_once('ketnoi.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['idnguoidung'])) {
    header('Location: dangnhap.php');
    exit;
}

$idnguoidung = $_SESSION['idnguoidung'];

// Lấy danh sách sách yêu thích
$stmt = $ketnoi->prepare("
    SELECT sach.*, loaisach.tenloaisach, tacgia.tentacgia
    FROM yeuthich
    JOIN sach ON yeuthich.idsach = sach.idsach
    LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach
    LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia
    WHERE yeuthich.idnguoidung = ?
");
$stmt->bind_param("i", $idnguoidung);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sách yêu thích - Thư Viện Trường Học</title>
    <link rel="shortcut icon" href="images/Book.png" type="image/png">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="css/header.css" rel="stylesheet">
    <link href="css/footer.css" rel="stylesheet">
    <link href="css/yeuthich.css" rel="stylesheet">
</head>

<body>

    <!-- Header -->
    <?php include 'header.php'; ?>
    <!-- end header section -->

    <!-- MAIN -->
    <section class="layout_padding">
        <div class="container">
            <h2 class="text-center fw-bold mb-4" style="color:#222;">
                <i class="fa fa-heart text-danger"></i> Sách yêu thích của bạn
            </h2>

            <div class="row g-4">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($result)): ?>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="card book-card h-100">
                                <button class="favorite-btn" data-id="<?php echo $r['idsach']; ?>">
                                    <i class="fa fa-heart"></i>
                                </button>
                                <img src="images/<?php echo htmlspecialchars($r['hinhanhsach']); ?>" alt="">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="text-truncate"><?php echo htmlspecialchars($r['tensach']); ?></h5>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($r['tentacgia']); ?></p>
                                        <p class="text-secondary small"><?php echo htmlspecialchars($r['tenloaisach']); ?></p>
                                    </div>
                                    <a href="chitietsach.php?idsach=<?php echo $r['idsach']; ?>" class="btn btn-warning btn-sm rounded-pill mt-2">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Bạn chưa thêm sách nào vào danh sách yêu thích.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        $(document).on("click", ".favorite-btn", function() {
            const btn = $(this);
            const icon = btn.find("i");
            const idsach = btn.data("id");

            $.post("xuly_yeuthich.php", {
                idsach
            }, function(res) {
                if (res.status === "added") {
                    icon.addClass("text-danger"); // tô đỏ trái tim
                    btn.attr("title", "Đã thêm vào yêu thích ❤️");
                } else if (res.status === "removed") {
                    icon.removeClass("text-danger"); // bỏ đỏ trái tim
                    btn.attr("title", "Đã bỏ khỏi yêu thích 💔");

                    // Nếu bạn đang ở trang yeuthich.php, xóa sách khỏi danh sách
                    if (window.location.pathname.includes("yeuthich.php")) {
                        btn.closest(".col-sm-6, .col-md-4, .col-lg-3").fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                }

                // (Tuỳ chọn) Thông báo nhẹ
                if (res.message) {
                    console.log(res.message);
                }
            }, "json");
        });
    </script>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
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

        <script src="js/jquery-3.4.1.min.js"></script>
        <script src="js/bootstrap.js"></script>
        <script src="js/custom.js"></script>
        <script>
            $(document).on("click", ".favorite-btn", function() {
                const btn = $(this);
                const icon = btn.find("i");
                const idsach = btn.data("id");

                $.post("xuly_yeuthich.php", {
                    idsach
                }, function(res) {
                    if (res.status === "added") {
                        icon.addClass("text-danger"); // tô đỏ trái tim
                        btn.attr("title", "Đã thêm vào yêu thích ❤️");
                    } else if (res.status === "removed") {
                        icon.removeClass("text-danger"); // bỏ đỏ trái tim
                        btn.attr("title", "Đã bỏ khỏi yêu thích 💔");

                        // Nếu bạn đang ở trang yeuthich.php, xóa sách khỏi danh sách
                        if (window.location.pathname.includes("yeuthich.php")) {
                            btn.closest(".col-sm-6, .col-md-4, .col-lg-3").fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    }

                    // (Tuỳ chọn) Thông báo nhẹ
                    if (res.message) {
                        console.log(res.message);
                    }
                }, "json");
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>