<?php
require_once('ketnoi.php');

$iddonhang = $_GET['id'] ?? 0;
if (!is_numeric($iddonhang) || $iddonhang <= 0) {
    header('Location: index.php?page_layout=danhsachdonhang');
    exit();
}

// Danh sách trạng thái (sử dụng giá trị ENUM của DB)
$allowed_statuses_db = [
    'cho_duyet', 
    'dang_giao', 
    'hoan_thanh', 
    'da_huy'
];

// Hàm hiển thị trạng thái (Tương tự như donhang.php, để hiển thị tên tiếng Việt)
if (!function_exists('display_status')) {
    function display_status($status_db) {
        $class = '';
        $text = '';
        switch ($status_db) {
            case 'cho_duyet':
                $class = 'bg-secondary';
                $text = 'Chờ xử lý';
                break;
            case 'dang_giao':
                $class = 'bg-warning text-dark';
                $text = 'Đang giao hàng';
                break;
            case 'hoan_thanh':
                $class = 'bg-success';
                $text = 'Đã hoàn thành';
                break;
            case 'da_huy':
                $class = 'bg-danger';
                $text = 'Đã hủy';
                break;
            default:
                $class = 'bg-light text-dark';
                $text = 'Không xác định';
        }
        return "<span class='badge {$class}'>{$text}</span>";
    }
}

// 1. Logic Cập nhật Trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_status'])) {
    $new_status = mysqli_real_escape_string($ketnoi, $_POST['new_status']);
    
    if (in_array($new_status, $allowed_statuses_db)) { // Kiểm tra với giá trị ENUM
        // ĐÃ SỬA: tinhtrang -> trangthai
        $sql_update = "UPDATE donhang SET trangthai = '$new_status' WHERE iddonhang = $iddonhang";
        if (mysqli_query($ketnoi, $sql_update)) {
            // Sử dụng hàm display_status để lấy tên tiếng Việt cho thông báo
            $status_display_name = (function($s){ 
                $map = ['cho_duyet'=>'Chờ xử lý', 'dang_giao'=>'Đang giao hàng', 'hoan_thanh'=>'Đã hoàn thành', 'da_huy'=>'Đã hủy'];
                return $map[$s] ?? $s;
            })($new_status);

            echo "<script>showToast('Cập nhật trạng thái thành công sang: " . $status_display_name . "', 'success');</script>";
            header("Location: index.php?page_layout=chitiet_donhang&id=$iddonhang");
            exit();
        } else {
            echo "<script>showToast('Lỗi khi cập nhật trạng thái: " . mysqli_error($ketnoi) . "', 'danger');</script>";
        }
    } else {
        echo "<script>showToast('Trạng thái không hợp lệ!', 'warning');</script>";
    }
}

// 2. Lấy thông tin chung Đơn hàng và Địa chỉ
$sql_dh = "
    SELECT 
        dh.*, /* ĐÃ SỬA: dh.tinhtrang -> dh.trangthai (vì dh.* đã bao gồm) */
        nd.hoten as ten_khachhang, 
        nd.email,
        nd.sdt as sdt_khachhang
        -- GIẢ ĐỊNH: donhang KHÔNG có cột iddiachi, mà dùng diachi_nhan và sdt_nhan.
        -- Cập nhật thông tin địa chỉ lấy từ donhang
    FROM donhang dh
    JOIN nguoidung nd ON dh.idnguoidung = nd.idnguoidung
    WHERE dh.iddonhang = $iddonhang
";
$query_dh = mysqli_query($ketnoi, $sql_dh);
$donhang = mysqli_fetch_assoc($query_dh);

if (!$donhang) {
    echo "<script>showToast('Không tìm thấy Đơn hàng!', 'danger');</script>";
    header('Location: index.php?page_layout=danhsachdonhang');
    exit();
}

// 3. Lấy Chi tiết các sản phẩm trong đơn
// ĐÃ SỬA: donhang_chitiet có tên là donhang_chitiet
$sql_ct = "
    SELECT 
        ct.soluong, 
        ct.dongia as gia, /* Cột trong DB là dongia */
        s.tensach
    FROM donhang_chitiet ct /* Tên bảng chính xác trong DB */
    INNER JOIN sach s ON ct.idsach = s.idsach
    WHERE ct.iddonhang = $iddonhang
";
$query_ct = mysqli_query($ketnoi, $sql_ct);
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4 text-primary"><i class='bx bx-detail'></i> Chi tiết Đơn hàng DH<?= htmlspecialchars($donhang['iddonhang']); ?></h2>
    
    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">Thông tin Đơn hàng</div>
                <div class="card-body">
                    <p><strong>ID Đơn:</strong> <span class="text-primary fw-bold">DH<?= htmlspecialchars($donhang['iddonhang']); ?></span></p>
                    <p><strong>Ngày Đặt:</strong> <?= date('d/m/Y H:i', strtotime($donhang['ngaydat'])); ?></p>
                    <p><strong>Tổng Tiền:</strong> <span class="fw-bold text-danger"><?= number_format($donhang['tongtien'], 0, ',', '.') ?>₫</span></p>
                    <p><strong>Trạng thái:</strong> <?= display_status($donhang['trangthai']); ?></p>
                    <hr>
                    <p class="fw-bold mb-1">Khách hàng:</p>
                    <p class="ms-3">
                        <?= htmlspecialchars($donhang['ten_khachhang']); ?> (<?= htmlspecialchars($donhang['email']); ?>)<br>
                        SĐT: <?= htmlspecialchars($donhang['sdt_khachhang']); ?>
                    </p>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white fw-bold">Địa chỉ Giao hàng</div>
                <div class="card-body">
                    <p><strong>Địa chỉ nhận:</strong> <?= htmlspecialchars($donhang['diachi_nhan'] ?? 'N/A'); ?></p>
                    <p><strong>SĐT nhận:</strong> <?= htmlspecialchars($donhang['sdt_nhan'] ?? 'N/A'); ?></p>
                    <p class="text-muted fst-italic mt-2">
                        *Lưu ý: Địa chỉ này được lưu trực tiếp trong bảng DonHang.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white fw-bold">Cập nhật Trạng thái Đơn hàng</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="input-group">
                            <select class="form-select" name="new_status" required>
                                <?php 
                                foreach ($allowed_statuses_db as $status_db) {
                                    $selected = ($status_db == $donhang['trangthai']) ? 'selected' : '';
                                    $display_name = (function($s){ 
                                        $map = ['cho_duyet'=>'Chờ xử lý', 'dang_giao'=>'Đang giao hàng', 'hoan_thanh'=>'Đã hoàn thành', 'da_huy'=>'Đã hủy'];
                                        return $map[$s] ?? $s;
                                    })($status_db);
                                    echo "<option value='{$status_db}' {$selected}>{$display_name}</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" class="btn btn-success"><i class='bx bx-refresh'></i> Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white fw-bold">Danh sách Sản phẩm</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th>Tên Sách</th>
                                    <th>Số lượng</th>
                                    <th>Giá (Đ/cuốn)</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $tong_so_luong = 0;
                                while ($item = mysqli_fetch_assoc($query_ct)): 
                                    $thanh_tien = $item['soluong'] * $item['gia'];
                                    $tong_so_luong += $item['soluong'];
                                ?>
                                <tr>
                                    <td class="text-start"><?= htmlspecialchars($item['tensach']); ?></td>
                                    <td class="text-center fw-bold"><?= number_format($item['soluong']); ?></td>
                                    <td class="text-end"><?= number_format($item['gia'], 0, ',', '.') ?>₫</td>
                                    <td class="text-end fw-bold text-success"><?= number_format($thanh_tien, 0, ',', '.') ?>₫</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td class="fw-bold text-end" colspan="2">Tổng số lượng sách:</td>
                                    <td class="text-center fw-bold text-primary" colspan="2"><?= number_format($tong_so_luong); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end">
        <a href="index.php?page_layout=danhsachdonhang" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
    </div>
</div>