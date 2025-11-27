<?php
require_once('ketnoi.php');

// Truy vấn để lấy tất cả đánh giá, liên kết với tên sách và tên người dùng
$sql = "
    SELECT 
        dg.iddanhgia,
        s.tensach,
        nd.hoten AS tennguoidung, -- ĐÃ SỬA LỖI: SỬ DỤNG 'hoten' THAY VÌ 'tennguoidung'
        dg.diem,
        dg.noidung,
        dg.ngaytao,
        dg.trangthai
    FROM danhgia dg
    INNER JOIN sach s ON dg.idsach = s.idsach
    INNER JOIN nguoidung nd ON dg.idnguoidung = nd.idnguoidung
    ORDER BY dg.ngaytao DESC
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
                                $status_text = $row['trangthai'] == 'Duyệt' ? 'Đã duyệt' : 'Chờ duyệt';
                                $status_class = $row['trangthai'] == 'Duyệt' ? 'bg-success' : 'bg-warning';
                                $status_icon = $row['trangthai'] == 'Duyệt' ? 'bx-check-circle' : 'bx-time-five';
                        ?>
                            <tr>
                                <td><strong><?= $row['iddanhgia'] ?></strong></td>
                                <td class="fw-semibold text-start"><?= htmlspecialchars($row['tensach']); ?></td>
                                <td><?= htmlspecialchars($row['tennguoidung']); ?></td>
                                <td>
                                    <span class="badge bg-primary px-2 py-1">
                                        <?= $row['diem'] ?> <i class='bx bxs-star'></i>
                                    </span>
                                </td>
                                <td class="text-start" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['noidung']); ?>">
                                    <?= htmlspecialchars($row['noidung']); ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ngaytao'])); ?></td>
                                <td>
                                    <span class="badge <?= $status_class ?> text-white px-3 py-2 shadow-sm">
                                        <i class='bx <?= $status_icon ?>'></i> <?= $status_text ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($row['trangthai'] != 'Duyệt'): ?>
                                            <a href="duyet_danhgia.php?id=<?= $row['iddanhgia'] ?>&action=duyet" 
                                               class="btn btn-success btn-sm shadow-sm rounded-pill px-2"
                                               title="Duyệt đánh giá">
                                                <i class='bx bx-check'></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="duyet_danhgia.php?id=<?= $row['iddanhgia'] ?>&action=huy" 
                                               class="btn btn-warning btn-sm shadow-sm rounded-pill px-2"
                                               title="Bỏ duyệt">
                                                <i class='bx bx-undo'></i>
                                            </a>
                                        <?php endif; ?>

                                        <button class="btn btn-danger btn-sm shadow-sm rounded-pill px-2"
                                                data-id="<?= $row['iddanhgia']; ?>"
                                                title="Xóa đánh giá"
                                                onclick="confirmDelete(this, '<?= htmlspecialchars($row['tensach']); ?>')">
                                            <i class='bx bx-trash'></i>
                                        </button>
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
        // Thực hiện fetch API để xóa
        fetch(`xoa_danhgia.php?iddanhgia=${id}`)
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