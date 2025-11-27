<?php
require_once('ketnoi.php');

// --- 1. THỐNG KÊ KHO VÀ SẢN PHẨM ---

// A. Tổng số lượng sách tồn kho (Tổng cột soluong trong bảng sach)
$sql_tong_ton = "SELECT SUM(soluong) AS total_stock FROM sach";
$res_tong_ton = mysqli_query($ketnoi, $sql_tong_ton);
$data_tong_ton = mysqli_fetch_assoc($res_tong_ton);
$total_stock = $data_tong_ton['total_stock'] ?? 0;

// B. Tổng số đầu sách (Tổng số dòng trong bảng sach)
$sql_tong_dau_sach = "SELECT COUNT(idsach) AS total_titles FROM sach";
$res_tong_dau_sach = mysqli_query($ketnoi, $sql_tong_dau_sach);
$data_tong_dau_sach = mysqli_fetch_assoc($res_tong_dau_sach);
$total_titles = $data_tong_dau_sach['total_titles'] ?? 0;

// C. Tổng số loại sách
$sql_tong_loai = "SELECT COUNT(idloaisach) AS total_categories FROM loaisach";
$res_tong_loai = mysqli_query($ketnoi, $sql_tong_loai);
$data_tong_loai = mysqli_fetch_assoc($res_tong_loai);
$total_categories = $data_tong_loai['total_categories'] ?? 0;


// --- 2. THỐNG KÊ DOANH THU & ĐƠN HÀNG (Dựa trên bảng donhang) ---

// D. Tổng số đơn hàng thành công (Giả sử trang thai = 'Hoàn thành' là đơn hàng thành công)
$sql_tong_don = "SELECT COUNT(iddonhang) AS total_orders FROM donhang WHERE trangthai = 'Hoàn thành'";
$res_tong_don = mysqli_query($ketnoi, $sql_tong_don);
$data_tong_don = mysqli_fetch_assoc($res_tong_don);
$total_orders = $data_tong_don['total_orders'] ?? 0;

// E. Tổng doanh thu (Tổng cột tongtien trong bảng donhang)
$sql_doanh_thu = "SELECT SUM(tongtien) AS total_revenue FROM donhang WHERE trangthai = 'Hoàn thành'";
$res_doanh_thu = mysqli_query($ketnoi, $sql_doanh_thu);
$data_doanh_thu = mysqli_fetch_assoc($res_doanh_thu);
$total_revenue = $data_doanh_thu['total_revenue'] ?? 0;

// F. Sách bán chạy nhất (Top 5 - Dựa trên donhang_chitiet)
$sql_top_sach = "
    SELECT s.tensach, SUM(ct.soluong) AS total_sold
    FROM donhang_chitiet ct
    INNER JOIN sach s ON ct.idsach = s.idsach
    GROUP BY ct.idsach
    ORDER BY total_sold DESC
    LIMIT 5
";
$res_top_sach = mysqli_query($ketnoi, $sql_top_sach);
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #10b981, #34d399); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class="bx bx-bar-chart-alt-2"></i> Thống Kê & Báo Cáo</h4>
        </div>
    </div>

    <div class="row mt-4">
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-start border-success border-5 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng Doanh Thu (Hoàn thành)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($total_revenue, 0, ',', '.') ?>₫
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-dollar-circle bx-lg text-success opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-5 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng Đơn Hàng (Hoàn thành)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($total_orders) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-receipt bx-lg text-primary opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-5 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tổng Số Lượng Sách Tồn
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($total_stock) ?> cuốn
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-package bx-lg text-warning opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-start border-info border-5 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tổng Số Đầu Sách
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($total_titles) ?> đầu sách
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-book-open bx-lg text-info opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">🥇 Top 5 Sách Bán Chạy Nhất</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên Sách</th>
                                    <th>Tổng Lượng Bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                if (mysqli_num_rows($res_top_sach) > 0) {
                                    while ($top_row = mysqli_fetch_assoc($res_top_sach)) { ?>
                                        <tr>
                                            <td><span class="badge bg-success-subtle text-success fw-bold"><?= $rank++; ?></span></td>
                                            <td><?= htmlspecialchars($top_row['tensach']) ?></td>
                                            <td class="fw-bold text-primary"><?= number_format($top_row['total_sold']) ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Chưa có dữ liệu bán hàng.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">⚠️ Sách Tồn Kho Thấp (Dưới 10 cuốn)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên Sách</th>
                                    <th>Số Lượng Tồn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_ton_thap = "
                                    SELECT idsach, tensach, soluong 
                                    FROM sach 
                                    WHERE soluong < 10 
                                    AND soluong > 0 -- Loại bỏ sách hết hàng
                                    ORDER BY soluong ASC
                                    LIMIT 5
                                ";
                                $res_ton_thap = mysqli_query($ketnoi, $sql_ton_thap);
                                
                                $rank_low = 1;
                                if (mysqli_num_rows($res_ton_thap) > 0) {
                                    while ($low_row = mysqli_fetch_assoc($res_ton_thap)) { ?>
                                        <tr>
                                            <td><?= $rank_low++; ?></td>
                                            <td><?= htmlspecialchars($low_row['tensach']) ?></td>
                                            <td class="fw-bold text-danger"><?= number_format($low_row['soluong']) ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-success">Tất cả sách đều có đủ hàng.</td>
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