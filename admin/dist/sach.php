<?php
require_once('ketnoi.php');

$sql = "SELECT s.*, l.tenloaisach, t.tentacgia
        FROM sach s
        LEFT JOIN loaisach l ON s.idloaisach = l.idloaisach
        LEFT JOIN tacgia t ON s.idtacgia = t.idtacgia
        ORDER BY s.ngaynhap DESC";
$result = mysqli_query($ketnoi, $sql);
?>

<!-- ========== DANH SÁCH SÁCH ========== -->
<div class="container mt-4">
  <div class="card shadow border-0" style="border-radius: 16px;">
    <div class="card-header text-white d-flex justify-content-between align-items-center"
         style="background: linear-gradient(90deg, #06b6d4, #67e8f9); color: #fff;">
      <h4 class="mb-0 fw-bold"><i class="bx bx-book"></i> Quản lý Sách</h4>
      <a href="index.php?page_layout=them_sach" class="btn btn-light btn-sm fw-semibold shadow-sm rounded-pill px-3">
        <i class="bx bx-plus-circle"></i> Thêm Sách
      </a>
    </div>

    <div class="card-body bg-light">
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
          <thead class="text-center align-middle" style="background-color: #e0f7fa;">
            <tr>
              <th>STT</th>
              <th>Ảnh bìa</th>
              <th>Tên sách</th>
              <th>Tác giả</th>
              <th>Thể loại</th>
              <th>Số lượng</th>
              <th>Đơn giá</th>
              <th>Ngày nhập</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) { ?>
              <tr>
                <td><strong><?= $i++; ?></strong></td>
                <td>
                  <?php if (!empty($row['hinhanhsach'])): ?>
                    <img src="../../feane/images/<?= htmlspecialchars($row['hinhanhsach']); ?>" 
                         width="60" height="80"
                         class="shadow-sm rounded border"
                         style="object-fit:cover;">
                  <?php else: ?>
                    <span class="text-muted fst-italic">Chưa có ảnh</span>
                  <?php endif; ?>
                </td>
                <td class="fw-semibold text-start"><?= htmlspecialchars($row['tensach']); ?></td>
                <td><?= htmlspecialchars($row['tentacgia'] ?? 'Không rõ'); ?></td>
                <td><span class="badge bg-info-subtle text-dark px-3 py-2 shadow-sm"><?= htmlspecialchars($row['tenloaisach'] ?? 'Không rõ'); ?></span></td>
                <td><?= $row['soluong']; ?></td>
                <td class="text-success fw-bold"><?= number_format($row['dongia']); ?>₫</td>
                <td><?= date('d/m/Y', strtotime($row['ngaynhap'])); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="index.php?page_layout=sua_sach&idsach=<?= $row['idsach']; ?>" 
                       class="btn btn-warning btn-sm shadow-sm text-dark rounded-pill px-2"
                       data-bs-toggle="tooltip" title="Chỉnh sửa">
                      <i class="bx bx-edit-alt"></i>
                    </a>
                    <button class="btn btn-danger btn-sm shadow-sm rounded-pill px-2"
                            data-id="<?= $row['idsach']; ?>"
                            data-bs-toggle="tooltip" title="Xóa sách"
                            onclick="confirmDelete(this)">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<!-- JS xử lý Tooltip + Toast + Xóa -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});

function confirmDelete(btn) {
  if (confirm("⚠️ Bạn có chắc muốn xóa sách này không?")) {
    const id = btn.dataset.id;
    fetch(`xoa_sach.php?idsach=${id}`)
      .then(res => res.text())
      .then(msg => {
        const ok = msg.includes('✅');
        showToast(ok ? '✅ Xóa thành công!' : '❌ Không thể xóa!', ok ? 'success' : 'danger');
        setTimeout(() => window.location.reload(), 1500);
      });
  }
}

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
</script>

<!-- CSS -->
<style>
  .table-hover tbody tr:hover {
    background-color: #f0fdfa !important;
    transition: all 0.25s ease;
  }
  .card-header {
    border-bottom: none;
  }
  .btn-sm i {
    font-size: 1rem;
  }
</style>
