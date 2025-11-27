<?php
require_once('ketnoi.php');

// Truy vấn lấy danh sách tác giả (ĐÃ SỬA: mota -> ghichu)
$sql = "SELECT tg.idtacgia, tg.tentacgia, tg.ghichu, COUNT(s.idsach) as so_luong_sach
        FROM tacgia tg
        LEFT JOIN sach s ON tg.idtacgia = s.idtacgia
        GROUP BY tg.idtacgia
        ORDER BY tg.idtacgia DESC";
$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #f97316, #f59e0b); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-user-voice'></i> Quản Lý Tác giả</h4>
            <a href="index.php?page_layout=them_tacgia" class="btn btn-light btn-sm fw-bold shadow-sm">
                <i class='bx bx-plus-circle'></i> Thêm Tác giả Mới
            </a>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #ffedd5;">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th class="text-start" style="width: 250px;">Tên Tác giả</th>
                            <th class="text-start">Ghi chú/Mô tả</th>
                            <th style="width: 150px;">Số lượng sách</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['idtacgia']); ?></td>
                                <td class="text-start fw-semibold text-primary"><?= htmlspecialchars($row['tentacgia']); ?></td>
                                <td class="text-start">
                                    <?= htmlspecialchars(mb_substr($row['ghichu'] ?? '', 0, 100, 'UTF-8')) . (mb_strlen($row['ghichu'] ?? '', 'UTF-8') > 100 ? '...' : ''); ?>
                                </td>
                                <td><span class="badge bg-dark"><?= number_format($row['so_luong_sach']); ?></span></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="index.php?page_layout=sua_tacgia&id=<?= $row['idtacgia'] ?>" 
                                           class="btn btn-info btn-sm text-white"
                                           title="Sửa tác giả">
                                            <i class='bx bx-edit'></i> Sửa
                                        </a>
                                        <a href="index.php?page_layout=xoa_tacgia&id=<?= $row['idtacgia'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bạn có chắc chắn muốn XÓA tác giả: <?= htmlspecialchars($row['tentacgia']); ?>?');"
                                           title="Xóa tác giả">
                                            <i class='bx bx-trash'></i> Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted fst-italic py-3">
                                    Không có tác giả nào trong hệ thống.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>