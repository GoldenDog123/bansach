<?php
// Đảm bảo biến $ketnoi được khởi tạo
require_once('ketnoi.php');

// Truy vấn lấy dữ liệu Sách, kết hợp Tên Thể loại và Tên Tác giả
$sql = "
    SELECT 
        s.idsach, 
        s.tensach, 
        s.dongia,  
        s.soluong, 
        s.hinhanhsach, 
        tg.tentacgia, 
        ls.tenloaisach
    FROM sach s
    LEFT JOIN tacgia tg ON s.idtacgia = tg.idtacgia
    LEFT JOIN loaisach ls ON s.idloaisach = ls.idloaisach
    ORDER BY s.idsach DESC
";

$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #0ea5e9, #38bdf8); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-book'></i> Quản lý Sách</h4>
            <a href="index.php?page_layout=them_sach" class="btn btn-light btn-sm fw-bold shadow-sm rounded-pill px-3">
                <i class='bx bx-plus-circle'></i> Thêm Sách Mới
            </a>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #e0f2fe;">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th style="width: 100px;">Ảnh</th>
                            <th class="text-start">Tên Sách</th>
                            <th style="width: 150px;">Tác giả</th>
                            <th style="width: 150px;">Thể loại</th>
                            <th style="width: 120px;">Giá bán</th>
                            <th style="width: 100px;">Tồn kho</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                                $stock_class = ($row['soluong'] <= 5) ? 'text-danger fw-bold' : 'text-primary';
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['idsach']); ?></td>
                                    <td>
                                        <?php if (!empty($row['hinhanhsach'])): ?>
                                            <img src="../../feane/images/<?= htmlspecialchars($row['hinhanhsach']); ?>" 
                                                 alt="<?= htmlspecialchars($row['tensach']); ?>" 
                                                 style="width: 70px; height: 90px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Không có ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-start fw-semibold"><?= htmlspecialchars($row['tensach']); ?></td>
                                    <td><?= htmlspecialchars($row['tentacgia'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-info-subtle text-dark px-3 py-1 shadow-sm">
                                            <?= htmlspecialchars($row['tenloaisach'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        <?= number_format($row['dongia'], 0, ',', '.') ?>₫ 
                                    </td>
                                    <td class="<?= $stock_class; ?>">
                                        <?= htmlspecialchars($row['soluong']); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="index.php?page_layout=sua_sach&id=<?= $row['idsach'] ?>" 
                                               class="btn btn-warning btn-sm shadow-sm text-dark rounded-pill px-2"
                                               title="Sửa sách">
                                                <i class='bx bx-edit'></i> 
                                            </a>
                                            
                                            <button class="btn btn-danger btn-sm shadow-sm rounded-pill px-2"
                                                    data-id="<?= $row['idsach']; ?>"
                                                    title="Xóa sách"
                                                    onclick="confirmDelete(this)">
                                                <i class='bx bx-trash'></i> 
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted fst-italic py-3">
                                    Không có sách nào trong hệ thống.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Khởi tạo Tooltip Bootstrap (nếu bạn sử dụng Bootstrap 5)
    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }
});

function showToast(message, type = 'info') {
    const color = {
        success: 'bg-success',
        danger: 'bg-danger',
        info: 'bg-info'
    }[type] || 'bg-primary';
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white border-0 ${color} show`;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body fw-semibold">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    document.getElementById('toastContainer').appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function confirmDelete(btn) {
    // Sử dụng confirm mặc định để xác nhận
    if (confirm("⚠️ Bạn có chắc muốn xóa sách này vĩnh viễn không?")) {
        const id = btn.dataset.id;
        
        // Gửi yêu cầu xóa qua Fetch API (Đã thống nhất dùng index.php)
        fetch(`index.php?page_layout=xoa_sach&id=${id}`) 
            .then(res => res.text())
            .then(msg => {
                // Kiểm tra chuỗi trả về từ PHP ("✅ Xóa thành công")
                const ok = msg.includes('✅'); 
                showToast(ok ? '✅ Xóa thành công!' : msg, ok ? 'success' : 'danger');
                
                // Tải lại trang sau khi xóa thành công hoặc thất bại
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(error => {
                showToast('❌ Lỗi kết nối server!', 'danger');
            });
    }
}
</script>

<style>
    .table-hover tbody tr:hover {
        background-color: #f0fdfa !important;
        transition: all 0.25s ease;
    }
</style>