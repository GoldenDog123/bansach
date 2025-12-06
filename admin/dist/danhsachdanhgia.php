<?php
require_once('ketnoi.php');

// Truy vấn để lấy tất cả đánh giá, liên kết với tên sách và tên người dùng
// Lấy điểm từ bảng danh_gia và nội dung từ bảng binh_luan
$sql = "
    SELECT 
        dg.id_danh_gia,
        s.tensach,
        COALESCE(bl.ho_ten, nd.hoten, 'Khách') AS tennguoidung,
        dg.diem_danh_gia,
        bl.noi_dung,
        COALESCE(bl.ngay_binh_luan, dg.ngay_tao) AS ngay_tao,
        CASE 
            WHEN bl.trang_thai = 'approved' THEN 'Duyệt'
            WHEN bl.trang_thai = 'pending' THEN 'Chờ duyệt'
            WHEN bl.trang_thai = 'rejected' THEN 'Từ chối'
            ELSE 'Chờ duyệt'
        END AS trang_thai,
        bl.id_binh_luan
    FROM danh_gia dg
    INNER JOIN sach s ON dg.idsach = s.idsach
    LEFT JOIN nguoidung nd ON dg.id_khach_hang = nd.idnguoidung
    LEFT JOIN binh_luan bl ON dg.idsach = bl.idsach AND dg.id_khach_hang = bl.id_khach_hang
    ORDER BY COALESCE(bl.ngay_binh_luan, dg.ngay_tao) DESC
";
$query = mysqli_query($ketnoi, $sql);

// Kiểm tra lỗi truy vấn
if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}
// ... giữ nguyên phần còn lại của file HTML/PHP
// ...
?>

<div class="container mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #f59e0b, #fbbf24); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class="bx bx-star"></i> Quản lý Đánh giá & Phản hồi</h4>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #fffbeb;">
                        <tr>
                            <th>ID</th>
                            <th>Sách</th>
                            <th>Người dùng</th>
                            <th>Điểm</th>
                            <th>Nội dung</th>
                            <th>Ngày đánh giá</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                                // Xử lý hiển thị trạng thái và màu sắc
                                $status_text = $row['trang_thai'] == 'Duyệt' ? 'Đã duyệt' : 'Chờ duyệt';
                                $status_class = $row['trang_thai'] == 'Duyệt' ? 'bg-success' : 'bg-warning';
                                $status_icon = $row['trang_thai'] == 'Duyệt' ? 'bx-check-circle' : 'bx-time-five';
                                
                                // Sử dụng id_binh_luan nếu có, nếu không dùng id_danh_gia
                                $record_id = $row['id_binh_luan'] ?? $row['id_danh_gia'];
                        ?>
                            <tr>
                                <td><strong><?= $row['id_danh_gia'] ?></strong></td>
                                <td class="fw-semibold text-start"><?= htmlspecialchars($row['tensach']); ?></td>
                                <td><?= htmlspecialchars($row['tennguoidung']); ?></td>
                                <td>
                                    <span class="badge bg-primary px-2 py-1">
                                        <?= $row['diem_danh_gia'] ?> <i class='bx bxs-star'></i>
                                    </span>
                                </td>
                                <td class="text-start" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['noi_dung'] ?? ''); ?>">
                                    <?= htmlspecialchars($row['noi_dung'] ?? 'Không có nội dung'); ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ngay_tao'])); ?></td>
                                <td>
                                    <span class="badge <?= $status_class ?> text-white px-3 py-2 shadow-sm">
                                        <i class='bx <?= $status_icon ?>'></i> <?= $status_text ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($row['id_binh_luan']): ?>
                                            <?php if ($row['trang_thai'] != 'Duyệt'): ?>
                                                <a href="duyet_danhgia.php?id_binh_luan=<?= $row['id_binh_luan'] ?>&action=duyet" 
                                                   class="btn btn-success btn-sm shadow-sm rounded-pill px-2"
                                                   title="Duyệt đánh giá">
                                                    <i class='bx bx-check'></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="duyet_danhgia.php?id_binh_luan=<?= $row['id_binh_luan'] ?>&action=huy" 
                                                   class="btn btn-warning btn-sm shadow-sm rounded-pill px-2"
                                                   title="Bỏ duyệt">
                                                    <i class='bx bx-undo'></i>
                                                </a>
                                            <?php endif; ?>

                                            <button class="btn btn-danger btn-sm shadow-sm rounded-pill px-2"
                                                    data-id="<?= $row['id_binh_luan']; ?>"
                                                    data-type="binh_luan"
                                                    title="Xóa đánh giá"
                                                    onclick="confirmDelete(this, '<?= htmlspecialchars($row['tensach']); ?>')">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Chưa có bình luận</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted fst-italic py-3">
                                    Không có đánh giá nào trong hệ thống.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Khởi tạo Tooltip (Nếu sử dụng Bootstrap)
    document.querySelectorAll('[title]').forEach(el => new bootstrap.Tooltip(el));
});

function confirmDelete(btn, tensach) {
    if (confirm(`⚠️ Bạn có chắc muốn xóa đánh giá cho sách "${tensach}" không? Hành động này không thể hoàn tác.`)) {
        const id = btn.dataset.id;
        const type = btn.dataset.type || 'danh_gia';
        
        // Thực hiện fetch API để xóa
        const url = type === 'binh_luan' 
            ? `xoa_danhgia.php?id_binh_luan=${id}` 
            : `xoa_danhgia.php?id_danh_gia=${id}`;
        
        fetch(url)
            .then(res => res.text())
            .then(msg => {
                // Giả định xoa_danhgia.php trả về chuỗi có 'thành công' khi xóa OK
                const ok = msg.includes('thành công');
                alert(ok ? '✅ Xóa đánh giá thành công!' : '❌ Không thể xóa đánh giá!');
                if (ok) {
                    window.location.reload();
                }
            })
            .catch(err => {
                alert('Lỗi kết nối khi xóa.');
                console.error(err);
            });
    }
}
</script>

<style>
/* CSS bổ sung để làm đẹp bảng */
.table-hover tbody tr:hover {
    background-color: #fffaf0 !important;
    transition: all 0.25s ease;
}
</style>