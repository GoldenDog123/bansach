<?php
require_once('ketnoi.php');

// Truy vấn lấy tất cả coupon
$sql = "SELECT * FROM coupon ORDER BY ngaybatdau DESC";
$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}

// Hàm kiểm tra trạng thái hiệu lực
function get_coupon_status($ngaybatdau, $ngayketthuc, $soluong) {
    $now = time();
    $start_time = strtotime($ngaybatdau);
    $end_time = strtotime($ngayketthuc);

    if ($soluong <= 0) {
        return ['text' => 'Hết lượt dùng', 'class' => 'bg-danger'];
    } elseif ($now < $start_time) {
        return ['text' => 'Sắp diễn ra', 'class' => 'bg-secondary'];
    } elseif ($now > $end_time) {
        return ['text' => 'Đã kết thúc', 'class' => 'bg-danger'];
    } else {
        return ['text' => 'Đang áp dụng', 'class' => 'bg-success'];
    }
}

// Hàm định dạng giá trị giảm
function format_giatri($giatri, $loaigiam) {
    if ($loaigiam == 'percent') {
        return $giatri . '%';
    }
    // Fixed (giảm cố định)
    return number_format($giatri, 0, ',', '.') . '₫';
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #9333ea, #ec4899); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-gift'></i> Quản lý Khuyến mãi (Coupon)</h4>
            <a href="index.php?page_layout=them_khuyenmai" class="btn btn-light btn-sm fw-bold shadow-sm">
                <i class='bx bx-plus-circle'></i> Thêm Coupon Mới
            </a>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #fce7f3;">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Mã Coupon</th>
                            <th style="width: 150px;">Giá trị</th>
                            <th style="width: 120px;">Số lượng còn</th>
                            <th style="width: 150px;">Ngày Bắt đầu</th>
                            <th style="width: 150px;">Ngày Kết thúc</th>
                            <th style="width: 120px;">Trạng thái</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                                $status = get_coupon_status($row['ngaybatdau'], $row['ngayketthuc'], $row['soluong']);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['idcoupon']); ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($row['macoupon']); ?></td>
                                <td><?= format_giatri($row['giatri'], $row['loaigiam']); ?></td>
                                <td><?= number_format($row['soluong']); ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ngaybatdau'])); ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ngayketthuc'])); ?></td>
                                <td><span class="badge <?= $status['class']; ?>"><?= $status['text']; ?></span></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="index.php?page_layout=sua_khuyenmai&id=<?= $row['idcoupon'] ?>" 
                                           class="btn btn-info btn-sm text-white"
                                           title="Sửa khuyến mãi">
                                            <i class='bx bx-edit'></i> Sửa
                                        </a>
                                        <a href="index.php?page_layout=xoa_khuyenmai&id=<?= $row['idcoupon'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bạn có chắc chắn muốn XÓA mã khuyến mãi: <?= htmlspecialchars($row['macoupon']); ?>?');"
                                           title="Xóa khuyến mãi">
                                            <i class='bx bx-trash'></i> Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted fst-italic py-3">
                                    Chưa có mã khuyến mãi nào được tạo.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>