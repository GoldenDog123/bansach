<?php
require_once('ketnoi.php');

$idcoupon = $_GET['id'] ?? 0;
if (!is_numeric($idcoupon) || $idcoupon <= 0) {
    header('Location: index.php?page_layout=danhsachkhuyenmai');
    exit();
}

// 1. Lấy thông tin coupon hiện tại
$sql_hien_tai = "SELECT * FROM coupon WHERE idcoupon = $idcoupon";
$query_hien_tai = mysqli_query($ketnoi, $sql_hien_tai);
$coupon_hien_tai = mysqli_fetch_assoc($query_hien_tai);

if (!$coupon_hien_tai) {
    echo "<script>showToast('Không tìm thấy Mã khuyến mãi!', 'danger');</script>";
    header('Location: index.php?page_layout=danhsachkhuyenmai');
    exit();
}

// Tách ngày và giờ cho form hiển thị
$ngaybatdau_date = date('Y-m-d', strtotime($coupon_hien_tai['ngaybatdau']));
$ngaybatdau_time = date('H:i', strtotime($coupon_hien_tai['ngaybatdau']));
$ngayketthuc_date = date('Y-m-d', strtotime($coupon_hien_tai['ngayketthuc']));
$ngayketthuc_time = date('H:i', strtotime($coupon_hien_tai['ngayketthuc']));


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $macoupon = mysqli_real_escape_string($ketnoi, strtoupper($_POST['macoupon'] ?? ''));
    $giatri = (int)($_POST['giatri'] ?? 0);
    $loaigiam = mysqli_real_escape_string($ketnoi, $_POST['loaigiam'] ?? 'fixed');
    $soluong = (int)($_POST['soluong'] ?? 0);
    
    $ngaybatdau_dt = $_POST['ngaybatdau'] . ' ' . $_POST['giobatdau'] . ':00';
    $ngayketthuc_dt = $_POST['ngayketthuc'] . ' ' . $_POST['gioketthuc'] . ':00';

    if (empty($macoupon) || $giatri <= 0 || empty($_POST['ngaybatdau']) || empty($_POST['ngayketthuc'])) {
        echo "<script>showToast('Vui lòng nhập đầy đủ Mã Coupon, Giá trị và Thời gian hiệu lực!', 'warning');</script>";
    } elseif (strtotime($ngaybatdau_dt) >= strtotime($ngayketthuc_dt)) {
        echo "<script>showToast('Ngày bắt đầu phải trước Ngày kết thúc!', 'danger');</script>";
    } else {
        // Kiểm tra mã coupon đã tồn tại với coupon khác chưa
        $sql_check = "SELECT idcoupon FROM coupon WHERE macoupon = '$macoupon' AND idcoupon != $idcoupon";
        if (mysqli_num_rows(mysqli_query($ketnoi, $sql_check)) > 0) {
            echo "<script>showToast('Mã coupon đã tồn tại với mã khác!', 'danger');</script>";
        } else {
            $sql_update = "UPDATE coupon SET 
                           macoupon = '$macoupon', 
                           giatri = $giatri, 
                           loaigiam = '$loaigiam', 
                           soluong = $soluong, 
                           ngaybatdau = '$ngaybatdau_dt', 
                           ngayketthuc = '$ngayketthuc_dt'
                           WHERE idcoupon = $idcoupon";
            
            if (mysqli_query($ketnoi, $sql_update)) {
                echo "<script>showToast('Cập nhật khuyến mãi thành công!', 'success');</script>";
                header('Location: index.php?page_layout=danhsachkhuyenmai');
                exit();
            } else {
                echo "<script>showToast('Lỗi khi cập nhật coupon: " . mysqli_error($ketnoi) . "', 'danger');</script>";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2 class="mb-4 text-info"><i class='bx bx-edit'></i> Sửa Khuyến mãi: <?= htmlspecialchars($coupon_hien_tai['macoupon']); ?></h2>
        <form method="POST">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white fw-bold">Thông tin Coupon</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="macoupon" class="form-label fw-bold">Mã Coupon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="macoupon" name="macoupon" 
                                   value="<?= htmlspecialchars($coupon_hien_tai['macoupon']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="soluong" class="form-label fw-bold">Số lượng (0 = Không giới hạn)</label>
                            <input type="number" class="form-control" id="soluong" name="soluong" 
                                   value="<?= htmlspecialchars($coupon_hien_tai['soluong']); ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="giatri" class="form-label fw-bold">Giá trị giảm <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="giatri" name="giatri" required min="1" 
                                   value="<?= htmlspecialchars($coupon_hien_tai['giatri']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="loaigiam" class="form-label fw-bold">Loại Giảm</label>
                            <select class="form-select" id="loaigiam" name="loaigiam" required>
                                <option value="fixed" <?= ($coupon_hien_tai['loaigiam'] == 'fixed' ? 'selected' : '') ?>>Giảm cố định (VNĐ)</option>
                                <option value="percent" <?= ($coupon_hien_tai['loaigiam'] == 'percent' ? 'selected' : '') ?>>Giảm theo phần trăm (%)</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 text-primary">Thời gian Hiệu lực</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ngaybatdau" class="form-label fw-bold">Ngày Bắt đầu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="ngaybatdau" name="ngaybatdau" value="<?= $ngaybatdau_date; ?>" required>
                                <input type="time" class="form-control" id="giobatdau" name="giobatdau" value="<?= $ngaybatdau_time; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ngayketthuc" class="form-label fw-bold">Ngày Kết thúc <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="ngayketthuc" name="ngayketthuc" value="<?= $ngayketthuc_date; ?>" required>
                                <input type="time" class="form-control" id="gioketthuc" name="gioketthuc" value="<?= $ngayketthuc_time; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page_layout=danhsachkhuyenmai" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                <button type="submit" class="btn btn-info text-white"><i class='bx bx-save'></i> Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>