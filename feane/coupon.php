<?php
require_once('ketnoi.php');
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['idnguoidung'])) {
    header('Location: dangnhap.php');
    exit;
}
$idnguoidung = $_SESSION['idnguoidung'];
$message = '';
// Xử lý áp dụng coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $macoupon = strtoupper(trim($_POST['macoupon']));
    if (empty($macoupon)) {
        $message = '<div class="alert alert-warning">⚠️ Vui lòng nhập mã coupon!</div>';
    } else {
        // Kiểm tra coupon
        $now = date('Y-m-d H:i:s');
        $sql_check = "SELECT * FROM coupon 
                      WHERE macoupon = '$macoupon' 
                      AND soluong > 0
                      AND (ngaybatdau IS NULL OR ngaybatdau <= '$now')
                      AND (ngayketthuc IS NULL OR ngayketthuc >= '$now')";
        
        $result = mysqli_query($ketnoi, $sql_check);
        if (mysqli_num_rows($result) > 0) {
            $coupon = mysqli_fetch_assoc($result);
            $_SESSION['applied_coupon'] = $coupon;
            $message = '<div class="alert alert-success">✅ Áp dụng mã giảm giá thành công!</div>';
        } else {
            $message = '<div class="alert alert-danger">❌ Mã giảm giá không hợp lệ hoặc đã hết hạn!</div>';
        }
    }
}
// Xử lý xóa coupon đã áp dụng
if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    $message = '<div class="alert alert-info">ℹ️ Đã xóa mã giảm giá!</div>';
}
// Lấy danh sách coupon đang hoạt động
$now = date('Y-m-d H:i:s');
$sql_coupons = "SELECT * FROM coupon 
                WHERE soluong > 0
                AND (ngaybatdau IS NULL OR ngaybatdau <= '$now')
                AND (ngayketthuc IS NULL OR ngayketthuc >= '$now')
                ORDER BY giatri DESC";
$coupons = mysqli_query($ketnoi, $sql_coupons);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="images/Book.png" type="image/png">
    <title>Phiếu Giảm Giá</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php include 'header.php'; ?>

    <section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
        <div class="container">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="text-white fw-bold mb-3">
                    <i class='bx bx-gift bx-lg'></i> Phiếu Giảm Giá
                </h1>
                <p class="text-white-50">Sử dụng mã giảm giá để tiết kiệm chi phí khi mua sách</p>
            </div>

            <!-- Form áp dụng coupon -->
            <div class="card shadow-lg border-0 mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class='bx bx-barcode'></i> Nhập Mã Giảm Giá</h4>
                    
                    <?= $message ?>

                    <?php if (isset($_SESSION['applied_coupon'])): 
                        $applied = $_SESSION['applied_coupon'];
                        $giatri_display = $applied['loaigiam'] == 'percent' 
                            ? $applied['giatri'] . '%' 
                            : number_format($applied['giatri'], 0, ',', '.') . '₫';
                    ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Mã đã áp dụng:</strong> 
                                <span class="badge bg-primary px-3 py-2 ms-2" style="font-size: 16px;">
                                    <?= $applied['macoupon'] ?>
                                </span>
                                <span class="ms-2">- Giảm <?= $giatri_display ?></span>
                            </div>
                            <a href="?remove_coupon=1" class="btn btn-sm btn-danger">
                                <i class='bx bx-x'></i> Xóa
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" name="macoupon" class="form-control form-control-lg" 
                                       placeholder="Nhập mã giảm giá (VD: GIAM50K)" 
                                       style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="apply_coupon" class="btn btn-primary btn-lg w-100">
                                    <i class='bx bx-check-circle'></i> Áp dụng
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danh sách coupon -->
            <h4 class="text-white mb-4"><i class='bx bx-list-ul'></i> Mã Giảm Giá Có Sẵn</h4>
            
            <div class="row">
                <?php if (mysqli_num_rows($coupons) > 0): ?>
                    <?php while ($coupon = mysqli_fetch_assoc($coupons)): 
                        $giatri_display = $coupon['loaigiam'] == 'percent' 
                            ? $coupon['giatri'] . '%' 
                            : number_format($coupon['giatri'], 0, ',', '.') . '₫';
                        
                        $loaigiam_text = $coupon['loaigiam'] == 'percent' ? 'Giảm' : 'Giảm';
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow border-0" style="border-radius: 15px; overflow: hidden;">
                                <div class="card-header text-white text-center py-3" 
                                     style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <h3 class="mb-0 fw-bold"><?= $giatri_display ?></h3>
                                    <small><?= $loaigiam_text ?></small>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <span class="badge bg-dark px-4 py-2" style="font-size: 18px; letter-spacing: 2px;">
                                            <?= htmlspecialchars($coupon['macoupon']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class='bx bx-time'></i> 
                                            <?php if ($coupon['ngayketthuc']): ?>
                                                HSD: <?= date('d/m/Y', strtotime($coupon['ngayketthuc'])) ?>
                                            <?php else: ?>
                                                Không giới hạn
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class='bx bx-package'></i> 
                                            Còn lại: <strong><?= $coupon['soluong'] ?></strong> lượt
                                        </small>
                                    </div>

                                    <button class="btn btn-primary w-100" 
                                            onclick="copyCoupon('<?= $coupon['macoupon'] ?>')">
                                        <i class='bx bx-copy'></i> Sao chép mã
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card shadow border-0" style="border-radius: 15px;">
                            <div class="card-body text-center py-5">
                                <i class='bx bx-sad bx-lg text-muted mb-3' style="font-size: 80px;"></i>
                                <h5 class="text-muted">Hiện tại chưa có mã giảm giá nào</h5>
                                <p class="text-muted">Vui lòng quay lại sau!</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Hướng dẫn sử dụng -->
            <div class="card shadow-lg border-0 mt-5" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h4 class="mb-3"><i class='bx bx-info-circle'></i> Hướng Dẫn Sử Dụng</h4>
                    <ol class="mb-0">
                        <li class="mb-2">Chọn mã giảm giá bạn muốn sử dụng và click "Sao chép mã"</li>
                        <li class="mb-2">Hoặc nhập mã trực tiếp vào ô "Nhập mã giảm giá" ở trên</li>
                        <li class="mb-2">Click "Áp dụng" để kích hoạt mã giảm giá</li>
                        <li class="mb-2">Mã giảm giá sẽ được tự động áp dụng khi bạn thanh toán</li>
                        <li>Mỗi mã chỉ có thể sử dụng một lần và có thời hạn sử dụng</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
    
    <script>
    function copyCoupon(code) {
        // Copy to clipboard
        navigator.clipboard.writeText(code).then(() => {
            // Show success message
            alert('✅ Đã sao chép mã: ' + code + '\nHãy dán vào ô nhập mã và click "Áp dụng"');
            
            // Auto fill input
            const input = document.querySelector('input[name="macoupon"]');
            if (input) {
                input.value = code;
                input.focus();
            }
        }).catch(err => {
            alert('❌ Không thể sao chép mã. Vui lòng nhập thủ công: ' + code);
        });
    }
    </script>
</body>
</html>
