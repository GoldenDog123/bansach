<?php
require_once('ketnoi.php');

// Định nghĩa các trạng thái liên quan đến Giao hàng (dùng giá trị ENUM trong DB)
$delivery_statuses_db = ['cho_duyet', 'dang_giao'];
$status_list = "'" . implode("','", $delivery_statuses_db) . "'";

// Hàm hỗ trợ hiển thị trạng thái (Tái sử dụng cho donhang.php và chitiet_donhang.php)
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


// Truy vấn lấy đơn hàng với các trạng thái cần quản lý giao hàng
$sql = "
    SELECT 
        dh.iddonhang, 
        dh.ngaydat, 
        dh.tongtien, 
        dh.trangthai, /* ĐÃ SỬA: tinhtrang -> trangthai */
        nd.hoten,
        nd.email
    FROM donhang dh
    JOIN nguoidung nd ON dh.idnguoidung = nd.idnguoidung
    WHERE dh.trangthai IN ({$status_list}) /* ĐÃ SỬA: tinhtrang -> trangthai */
    ORDER BY dh.ngaydat ASC
";

$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    // Nếu vẫn có lỗi, thông báo rõ ràng hơn
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #16a34a, #22c55e); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-package'></i> Quản lý Giao hàng (Đơn hàng cần xử lý)</h4>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #dcfce7;">
                        <tr>
                            <th style="width: 80px;">ID Đơn</th>
                            <th style="width: 150px;">Ngày Đặt</th>
                            <th class="text-start">Khách hàng</th>
                            <th style="width: 150px;">Tổng tiền</th>
                            <th style="width: 150px;">Trạng thái</th>
                            <th style="width: 100px;">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                        ?>
                            <tr>
                                <td><strong class="text-success">DH<?= htmlspecialchars($row['iddonhang']); ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ngaydat'])); ?></td>
                                <td class="text-start">
                                    <strong><?= htmlspecialchars($row['hoten']); ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['email']); ?></small>
                                </td>
                                <td class="fw-bold text-danger">
                                    <?= number_format($row['tongtien'], 0, ',', '.') ?>₫
                                </td>
                                <td><?= display_status($row['trangthai']); ?></td>
                                <td>
                                    <a href="index.php?page_layout=chitiet_donhang&id=<?= $row['iddonhang'] ?>" 
                                       class="btn btn-success btn-sm"
                                       title="Xem chi tiết và cập nhật trạng thái">
                                        <i class='bx bx-send'></i> Xử lý
                                    </a>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted fst-italic py-3">
                                    Không có đơn hàng nào đang trong quá trình xử lý/giao hàng.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>