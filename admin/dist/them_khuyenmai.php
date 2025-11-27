<?php
require_once('ketnoi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $macoupon = mysqli_real_escape_string($ketnoi, strtoupper($_POST['macoupon'] ?? ''));
    $giatri = (int)($_POST['giatri'] ?? 0);
    $loaigiam = mysqli_real_escape_string($ketnoi, $_POST['loaigiam'] ?? 'fixed');
    $soluong = (int)($_POST['soluong'] ?? 0);
    
    // Kết hợp ngày và giờ cho datetime
    $ngaybatdau_dt = $_POST['ngaybatdau'] . ' ' . $_POST['giobatdau'] . ':00';
    $ngayketthuc_dt = $_POST['ngayketthuc'] . ' ' . $_POST['gioketthuc'] . ':00';

    if (empty($macoupon) || $giatri <= 0 || empty($_POST['ngaybatdau']) || empty($_POST['ngayketthuc'])) {
        echo "<script>showToast('Vui lòng nhập đầy đủ Mã Coupon, Giá trị và Thời gian hiệu lực!', 'warning');</script>";
    } elseif (strtotime($ngaybatdau_dt) >= strtotime($ngayketthuc_dt)) {
        echo "<script>showToast('Ngày bắt đầu phải trước Ngày kết thúc!', 'danger');</script>";
    } else {
        // Kiểm tra mã coupon đã tồn tại chưa
        $sql_check = "SELECT idcoupon FROM coupon WHERE macoupon = '$macoupon'";
        if (mysqli_num_rows(mysqli_query($ketnoi, $sql_check)) > 0) {
            echo "<script>showToast('Mã coupon đã tồn tại!', 'danger');</script>";
        } else {
            $sql_insert = "INSERT INTO coupon (macoupon, giatri, loaigiam, soluong, ngaybatdau, ngayketthuc)
                           VALUES ('$macoupon', $giatri, '$loaigiam', $soluong, '$ngaybatdau_dt', '$ngayketthuc_dt')";
            
            if (mysqli_query($ketnoi, $sql_insert)) {
                echo "<script>showToast('Thêm mã khuyến mãi thành công!', 'success');</script>";
                header('Location: index.php?page_layout=danhsachkhuyenmai');
                exit();
            } else {
                echo "<script>showToast('Lỗi khi thêm coupon: " . mysqli_error($ketnoi) . "', 'danger');</script>";
            }
        }
    }
}

// Thiết lập giá trị mặc định cho form
$ngay_hien_tai = date('Y-m-d');
$ngay_ket_thuc_mac_dinh = date('Y-m-d', strtotime('+1 month'));
$gio_mac_dinh = date('H:i');
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2 class="mb-4 text-primary"><i class='bx bx-gift'></i> Thêm Khuyến mãi Mới</h2>
        <form method="POST">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">Thông tin Coupon</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="macoupon" class="form-label fw-bold">Mã Coupon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="macoupon" name="macoupon" required placeholder="VD: TET2025">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="soluong" class="form-label fw-bold">Số lượng (0 = Không giới hạn)</label>
                            <input type="number" class="form-control" id="soluong" name="soluong" value="0" min="0">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="giatri" class="form-label fw-bold">Giá trị giảm <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="giatri" name="giatri" required min="1" placeholder="Nhập giá trị (ví dụ: 10000 hoặc 10)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="loaigiam" class="form-label fw-bold">Loại Giảm</label>
                            <select class="form-select" id="loaigiam" name="loaigiam" required>
                                <option value="fixed">Giảm cố định (VNĐ)</option>
                                <option value="percent">Giảm theo phần trăm (%)</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 text-info">Thời gian Hiệu lực</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ngaybatdau" class="form-label fw-bold">Ngày Bắt đầu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="ngaybatdau" name="ngaybatdau" value="<?= $ngay_hien_tai; ?>" required>
                                <input type="time" class="form-control" id="giobatdau" name="giobatdau" value="<?= $gio_mac_dinh; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ngayketthuc" class="form-label fw-bold">Ngày Kết thúc <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="ngayketthuc" name="ngayketthuc" value="<?= $ngay_ket_thuc_mac_dinh; ?>" required>
                                <input type="time" class="form-control" id="gioketthuc" name="gioketthuc" value="<?= $gio_mac_dinh; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page_layout=danhsachkhuyenmai" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Thêm Khuyến mãi</button>
            </div>
        </form>
    </div>
</div>