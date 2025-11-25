<?php
require_once('ketnoi.php');
session_start();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="keywords" content="thư viện, sách, trường học, giới thiệu" />
    <meta name="description" content="Giới thiệu Thư viện Trường Ctech" />
    <meta name="author" content="Thư viện Trường Ctech" />
    <link rel="shortcut icon" href="images/Book.png" type="image/png">

    <title>Giới thiệu - Thư viện Trường Ctech</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <!-- Font Awesome -->
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <!-- Custom -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/header.css" rel="stylesheet">
    <link href="css/about.css" rel="stylesheet">
    <link href="css/footer.css" rel="stylesheet">
</head>

<body class="about-page">
    <?php include 'header.php'; ?>

    <!-- Giới thiệu -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="images/books.png" class="img-fluid rounded shadow" alt="Thư viện">
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold mb-3">Về Thư Viện Trường Ctech</h2>
                    <p>
                        Thư viện Trường Ctech là nơi lưu trữ và chia sẻ tri thức, phục vụ nhu cầu học tập – nghiên cứu
                        cho học sinh, sinh viên và giáo viên.
                        Với hàng ngàn đầu sách đa dạng về văn học, khoa học, kỹ thuật, kỹ năng sống và giáo dục, thư
                        viện luôn sẵn sàng đồng hành cùng bạn trên hành trình tri thức.
                    </p>
                    <p>
                        Hệ thống quản lý trực tuyến giúp bạn dễ dàng tra cứu, <b>mua sách</b>, và theo dõi đơn hàng chỉ bằng vài cú click chuột.
                        Mục tiêu của chúng tôi là xây dựng một môi trường học tập mở, hiện đại và thân thiện.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Đội ngũ thư viện -->
    <section class="layout_padding bg-light">
        <div class="container">
            <div class="heading_container heading_center mb-5">
                <h2 class="fw-bold">👩‍🏫 Đội ngũ quản lý thư viện</h2>
                <p class="text-muted">Những người luôn tận tâm hỗ trợ bạn trong hành trình học tập</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="team-card p-3 bg-white rounded shadow-sm">
                        <img src="images/nv4.png" class="w-100 mb-3" alt="Nhân viên 4">
                        <h5 class="fw-bold">Nguyễn Thị Lan</h5>
                        <p class="text-muted mb-1">Thủ thư trưởng</p>
                        <small>📧 lan.nguyen@edu.vn</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="team-card p-3 bg-white rounded shadow-sm">
                        <img src="images/nv2.png" class="w-100 mb-3" alt="Nhân viên 2">
                        <h5 class="fw-bold">Trần Văn Minh</h5>
                        <p class="text-muted mb-1">Quản lý hệ thống</p>
                        <small>📧 minh.tran@edu.vn</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="team-card p-3 bg-white rounded shadow-sm">
                        <img src="images/nv3.png" class="w-100 mb-3" alt="Nhân viên 3">
                        <h5 class="fw-bold">Lê Hồng Hạnh</h5>
                        <p class="text-muted mb-1">Hỗ trợ người dùng</p>
                        <small>📧 hanh.le@edu.vn</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Thống kê -->
    <section class="layout_padding">
        <div class="container">
            <div class="heading_container heading_center mb-4">
                <h2 class="fw-bold">📊 Thống kê thư viện</h2>
            </div>

            <?php
            $count_books = mysqli_fetch_row(mysqli_query($ketnoi, "SELECT COUNT(*) FROM sach"))[0];
            $count_users = mysqli_fetch_row(mysqli_query($ketnoi, "SELECT COUNT(*) FROM nguoidung"))[0];
            $count_orders = mysqli_fetch_row(mysqli_query($ketnoi, "SELECT COUNT(*) FROM donhang"))[0];
            ?>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stats-box">
                        <h3><?php echo $count_books; ?></h3>
                        <p>Đầu sách</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-box">
                        <h3><?php echo $count_users; ?></h3>
                        <p>Người dùng</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-box">
                        <h3><?php echo $count_orders; ?></h3>
                        <p>Đơn hàng</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2>Bắt đầu hành trình tri thức của bạn ngay hôm nay!</h2>
            <p class="mb-4">Khám phá kho sách khổng lồ và mua sách dễ dàng chỉ trong vài giây</p>
            <a href="menu.php">📚 Xem kho sách</a>
            <a href="book.php" class="ml-3">🛒 Mua ngay</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- JS -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/custom.js"></script>

</body>

</html>