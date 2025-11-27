<?php
require_once('ketnoi.php');

// Truy vấn lấy danh sách thanh toán và thông tin đơn hàng liên quan
$sql = "
    SELECT 
        tt.idthanhtoan, 
        tt.iddonhang, 
        tt.ngaythanhtoan, 
        tt.hinhthuc, /* ĐÃ SỬA LẦN CUỐI: phuongthuc -> hinhthuc */
        tt.sotien,
        dh.trangthai, 
        nd.hoten 
    FROM thanhtoan tt
    JOIN donhang dh ON tt.iddonhang = dh.iddonhang
    JOIN nguoidung nd ON dh.idnguoidung = nd.idnguoidung
    ORDER BY tt.ngaythanhtoan DESC
";
$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}

// Hàm hiển thị trạng thái đơn hàng (Tái sử dụng từ file donhang.php)
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
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #10b981, #06b6d4); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-credit-card-front'></i> Quản Lý Thanh Toán</h4>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #d1fae5;">
                        <tr>
                            <th style="width: 80px;">ID TT</th>
                            <th style="width: 150px;">Ngày TT</th>
                            <th style="width: 100px;">ID Đơn hàng</th>
                            <th class="text-start">Khách hàng</th>
                            <th style="width: 180px;">H.Thức TT</th>
                            <th style="width: 150px;">Số tiền</th>
                            <th style="width: 150px;">Trạng thái ĐH</th>
                            <th style="width: 100px;">Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                        ?>
                            <tr>
                                <td><strong class="text-info"><?= htmlspecialchars($row['idthanhtoan']); ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ngaythanhtoan'])); ?></td>
                                <td><strong class="text-primary">DH<?= htmlspecialchars($row['iddonhang']); ?></strong></td>
                                <td class="text-start"><?= htmlspecialchars($row['hoten']); ?></td>
                                <td><?= htmlspecialchars($row['hinhthuc']); ?></td> 
                                <td class="fw-bold text-success">
                                    <?= number_format($row['sotien'], 0, ',', '.') ?>₫
                                </td>
                                <td><?= display_status($row['trangthai']); ?></td>
                                <td>
                                    <a href="index.php?page_layout=chitiet_donhang&id=<?= $row['iddonhang'] ?>" 
                                       class="btn btn-secondary btn-sm"
                                       title="Xem chi tiết đơn hàng">
                                        <i class='bx bx-detail'></i> Xem
                                    </a>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted fst-italic py-3">
                                    Chưa có giao dịch thanh toán nào được ghi nhận.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>