<?php
require_once('ketnoi.php');

$idphieunhap = $_GET['id'] ?? 0;
if (!is_numeric($idphieunhap) || $idphieunhap <= 0) {
    header('Location: index.php?page_layout=danhsachnhapkho');
    exit();
}

// 1. Lấy thông tin chung của phiếu nhập
$sql_pn = "SELECT * FROM phieunhap WHERE idphieunhap = $idphieunhap";
$query_pn = mysqli_query($ketnoi, $sql_pn);
$phieunhap = mysqli_fetch_assoc($query_pn);

if (!$phieunhap) {
    echo "<script>showToast('Không tìm thấy Phiếu nhập!', 'danger');</script>";
    header('Location: index.php?page_layout=danhsachnhapkho');
    exit();
}

// 2. Lấy chi tiết các sách đã nhập
$sql_ct = "
    SELECT 
        ct.soluongnhap, 
        ct.gianhap, 
        s.tensach, 
        s.idsach
    FROM phieunhap_chitiet ct
    INNER JOIN sach s ON ct.idsach = s.idsach
    WHERE ct.idphieunhap = $idphieunhap
";
$query_ct = mysqli_query($ketnoi, $sql_ct);

$tong_so_luong = 0;
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4 text-primary"><i class='bx bx-detail'></i> Chi tiết Phiếu nhập PN<?= htmlspecialchars($phieunhap['idphieunhap']); ?></h2>
    
    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white fw-bold">Thông tin Phiếu nhập</div>
                <div class="card-body">
                    <p><strong>ID Phiếu:</strong> <span class="text-success fw-bold">PN<?= htmlspecialchars($phieunhap['idphieunhap']); ?></span></p>
                    <p><strong>Ngày Nhập:</strong> <?= date('d/m/Y H:i', strtotime($phieunhap['ngaynhap'])); ?></p>
                    <p><strong>Nhà Cung Cấp:</strong> <span class="fw-bold"><?= htmlspecialchars($phieunhap['nhacungcap'] ?? 'N/A'); ?></span></p>
                    <p><strong>Tổng Tiền:</strong> <span class="fw-bold text-danger"><?= number_format($phieunhap['tongtiennhap'], 0, ',', '.') ?>₫</span></p>
                    <p><strong>Ghi chú:</strong> <?= htmlspecialchars($phieunhap['ghichu'] ?? 'Không có ghi chú'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white fw-bold">Danh sách Sách nhập</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th>Tên Sách</th>
                                    <th>Số lượng</th>
                                    <th>Giá nhập (Đ/cuốn)</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                while ($item = mysqli_fetch_assoc($query_ct)): 
                                    $thanh_tien = $item['soluongnhap'] * $item['gianhap'];
                                    $tong_so_luong += $item['soluongnhap'];
                                ?>
                                <tr>
                                    <td class="text-start"><?= htmlspecialchars($item['tensach']); ?></td>
                                    <td class="text-center fw-bold"><?= number_format($item['soluongnhap']); ?></td>
                                    <td class="text-end"><?= number_format($item['gianhap'], 0, ',', '.') ?>₫</td>
                                    <td class="text-end fw-bold text-success"><?= number_format($thanh_tien, 0, ',', '.') ?>₫</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td class="fw-bold text-end">Tổng số lượng:</td>
                                    <td class="text-center fw-bold text-primary"><?= number_format($tong_so_luong); ?></td>
                                    <td class="fw-bold text-end">Tổng tiền phiếu nhập:</td>
                                    <td class="text-end fw-bold text-danger"><?= number_format($phieunhap['tongtiennhap'], 0, ',', '.') ?>₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end">
        <a href="index.php?page_layout=danhsachnhapkho" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
    </div>
</div>