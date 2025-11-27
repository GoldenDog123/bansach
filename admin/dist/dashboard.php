<?php
require_once('ketnoi.php');

// ==========================================================
// 1. TÍNH TOÁN CÁC CHỈ SỐ TỔNG QUAN (KPI CARDS)
// ==========================================================

// --- Tổng Doanh Thu (từ các đơn hàng đã Hoàn thành) ---
$sql_doanh_thu = "
    SELECT SUM(tongtien) as total_revenue 
    FROM donhang 
    WHERE trangthai = 'hoan_thanh'
";
$result_doanh_thu = mysqli_query($ketnoi, $sql_doanh_thu);
$row_doanh_thu = mysqli_fetch_assoc($result_doanh_thu);
$doanh_thu_tong = $row_doanh_thu['total_revenue'] ?? 0;

// --- Tổng Số Lượng Đơn Hàng ---
$sql_tong_don = "SELECT COUNT(iddonhang) as total_orders FROM donhang";
$result_tong_don = mysqli_query($ketnoi, $sql_tong_don);
$row_tong_don = mysqli_fetch_assoc($result_tong_don);
$tong_don_hang = $row_tong_don['total_orders'] ?? 0;

// --- Tổng Số Lượng Sách (Sản phẩm) ---
$sql_tong_sach = "SELECT COUNT(idsach) as total_products FROM sach";
$result_tong_sach = mysqli_query($ketnoi, $sql_tong_sach);
$row_tong_sach = mysqli_fetch_assoc($result_tong_sach);
$tong_sach = $row_tong_sach['total_products'] ?? 0;

// --- Tổng Số Lượng Người Dùng ---
$sql_tong_nguoidung = "SELECT COUNT(idnguoidung) as total_users FROM nguoidung";
$result_tong_nguoidung = mysqli_query($ketnoi, $sql_tong_nguoidung);
$row_tong_nguoidung = mysqli_fetch_assoc($result_tong_nguoidung);
$tong_nguoi_dung = $row_tong_nguoidung['total_users'] ?? 0;


// ==========================================================
// 2. THỐNG KÊ ĐƠN HÀNG MỚI NHẤT
// ==========================================================
$sql_don_moi = "
    SELECT 
        dh.iddonhang, 
        dh.ngaydat, 
        dh.tongtien, 
        dh.trangthai, 
        nd.hoten 
    FROM donhang dh
    JOIN nguoidung nd ON dh.idnguoidung = nd.idnguoidung
    ORDER BY dh.ngaydat DESC
    LIMIT 5 
";
$query_don_moi = mysqli_query($ketnoi, $sql_don_moi);


// ==========================================================
// 3. THỐNG KÊ SÁCH BÁN CHẠY NHẤT (Top 5) - ĐÃ SỬA: donhang_chitiet
// ==========================================================
$sql_sach_chay = "
    SELECT 
        ct.idsach, 
        s.tensach, 
        SUM(ct.soluong) as total_sold 
    FROM donhang_chitiet ct /* ĐÃ SỬA TÊN BẢNG */
    JOIN donhang dh ON ct.iddonhang = dh.iddonhang
    JOIN sach s ON ct.idsach = s.idsach
    WHERE dh.trangthai = 'hoan_thanh' 
    GROUP BY ct.idsach, s.tensach
    ORDER BY total_sold DESC
    LIMIT 5
";
$query_sach_chay = mysqli_query($ketnoi, $sql_sach_chay);


// Hàm hiển thị trạng thái đơn hàng 
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
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4 text-dark"><i class='bx bx-tachometer'></i> Trang Tổng quan (Dashboard)</h2>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary text-white shadow h-100 py-2" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Tổng Doanh thu (Hoàn thành)
                            </div>
                            <div class="h5 mb-0 fw-bold">
                                <?= number_format($doanh_thu_tong, 0, ',', '.') ?>₫
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class='bx bx-dollar-circle bx-lg'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white shadow h-100 py-2" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Tổng Số Đơn Hàng
                            </div>
                            <div class="h5 mb-0 fw-bold">
                                <?= number_format($tong_don_hang) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class='bx bx-package bx-lg'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-info text-white shadow h-100 py-2" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Tổng Số Sách
                            </div>
                            <div class="h5 mb-0 fw-bold">
                                <?= number_format($tong_sach) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class='bx bx-book-bookmark bx-lg'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-dark shadow h-100 py-2" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Tổng Số Người Dùng
                            </div>
                            <div class="h5 mb-0 fw-bold">
                                <?= number_format($tong_nguoi_dung) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class='bx bx-group bx-lg'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr/>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow" style="border-radius: 16px;">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class='bx bx-list-ol'></i> 5 Đơn hàng Mới nhất
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 100px;">ID ĐH</th>
                                    <th>Khách hàng</th>
                                    <th style="width: 130px;">Tổng tiền</th>
                                    <th style="width: 130px;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($query_don_moi) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_don_moi)) { 
                                ?>
                                    <tr>
                                        <td><a href="index.php?page_layout=chitiet_donhang&id=<?= $row['iddonhang'] ?>" class="text-primary fw-bold">DH<?= htmlspecialchars($row['iddonhang']); ?></a></td>
                                        <td><?= htmlspecialchars($row['hoten']); ?></td>
                                        <td class="fw-bold text-success"><?= number_format($row['tongtien'], 0, ',', '.') ?>₫</td>
                                        <td><?= display_status($row['trangthai']); ?></td>
                                    </tr>
                                <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted fst-italic py-3">Chưa có đơn hàng nào.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow" style="border-radius: 16px;">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class='bx bx-trending-up'></i> 5 Sách Bán Chạy nhất
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tên Sách</th>
                                    <th style="width: 100px;">Đã bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($query_sach_chay) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_sach_chay)) { 
                                ?>
                                    <tr>
                                        <td><a href="index.php?page_layout=sua_sach&id=<?= $row['idsach'] ?>"><?= htmlspecialchars($row['tensach']); ?></a></td>
                                        <td class="fw-bold text-primary"><?= number_format($row['total_sold']); ?></td>
                                    </tr>
                                <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted fst-italic py-3">Chưa có dữ liệu bán hàng.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>