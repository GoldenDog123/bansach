<?php
require_once('ketnoi.php');

$sql = "
    SELECT 
        pn.idphieunhap, 
        pn.ngaynhap, 
        pn.tongtiennhap,  /* ĐÃ THÊM: Cột này cần được tạo trong DB */
        pn.nhacungcap,    /* ĐÃ SỬA: Lấy nhacungcap từ DB */
        pn.ghichu
    FROM phieunhap pn
    ORDER BY pn.ngaynhap DESC
";

$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #10b981, #34d399); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-archive-in'></i> Quản lý Phiếu Nhập Kho</h4>
            <a href="index.php?page_layout=them_phieunhap" class="btn btn-light btn-sm fw-bold shadow-sm">
                <i class='bx bx-plus-circle'></i> Tạo Phiếu Nhập Mới
            </a>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #d1fae5;">
                        <tr>
                            <th style="width: 80px;">ID Phiếu</th>
                            <th style="width: 150px;">Ngày Nhập</th>
                            <th style="width: 200px;">Nhà Cung Cấp</th> <th style="width: 150px;">Tổng Tiền Nhập</th>
                            <th style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                        ?>
                            <tr>
                                <td><strong class="text-success">PN<?= $row['idphieunhap'] ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ngaynhap'])); ?></td>
                                <td class="text-start"><?= htmlspecialchars($row['nhacungcap'] ?? 'N/A'); ?></td>
                                <td class="fw-bold text-success">
                                    <?= number_format($row['tongtiennhap'], 0, ',', '.') ?>₫
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="index.php?page_layout=chitiet_phieunhap&id=<?= $row['idphieunhap'] ?>" 
                                           class="btn btn-primary btn-sm"
                                           title="Xem chi tiết phiếu nhập">
                                            <i class='bx bx-detail'></i> Chi tiết
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted fst-italic py-3">
                                    Chưa có phiếu nhập kho nào.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>