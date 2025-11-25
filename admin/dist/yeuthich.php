<?php
require_once('ketnoi.php');

// Lấy danh sách yêu thích (JOIN người dùng + sách)
$sql = "SELECT y.id, n.hoten, s.tensach, s.hinhanhsach, y.ngaythem
        FROM yeuthich y
        LEFT JOIN nguoidung n ON y.idnguoidung = n.idnguoidung
        LEFT JOIN sach s ON y.idsach = s.idsach
        ORDER BY y.ngaythem DESC";
$result = mysqli_query($ketnoi, $sql);
?>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header text-white d-flex justify-content-between align-items-center"
         style="background: linear-gradient(90deg, #4f46e5, #06b6d4);">
      <h4 class="mb-0"><i class='bx bx-heart'></i> Danh sách Yêu Thích</h4>
      <a href="index.php?page_layout=them_yeuthich" class="btn btn-light btn-sm fw-semibold shadow-sm">
        <i class="bx bx-plus"></i> Thêm mới
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead class="table-primary align-middle">
            <tr>
              <th>STT</th>
              <th>Ảnh bìa</th>
              <th>Tên sách</th>
              <th>Người dùng</th>
              <th>Ngày thêm</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) { ?>
              <tr>
                <td><strong><?= $i++; ?></strong></td>
                <td>
                  <?php if (!empty($row['hinhanhsach'])): ?>
                    <img src="../../feane/images/<?= htmlspecialchars($row['hinhanhsach']); ?>" 
                         alt="Bìa sách" width="60" height="80" class="rounded shadow-sm"
                         style="object-fit: cover;">
                  <?php else: ?>
                    <span class="text-muted fst-italic">Không có ảnh</span>
                  <?php endif; ?>
                </td>
                <td class="fw-semibold"><?= htmlspecialchars($row['tensach'] ?? 'Không rõ'); ?></td>
                <td><?= htmlspecialchars($row['hoten'] ?? 'Không rõ'); ?></td>
                <td><?= date('d/m/Y', strtotime($row['ngaythem'])); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="index.php?page_layout=sua_yeuthich&id=<?= $row['id']; ?>" 
                       class="btn btn-warning btn-sm text-dark shadow-sm" 
                       data-bs-toggle="tooltip" title="Chỉnh sửa">
                      <i class="bx bx-edit-alt"></i>
                    </a>
                    <button class="btn btn-danger btn-sm shadow-sm" 
                            data-id="<?= $row['id']; ?>"
                            data-bs-toggle="tooltip" title="Xóa"
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

<!-- Toast container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
});

// Xác nhận xóa + Toast
function confirmDelete(btn) {
  if (confirm("⚠️ Bạn có chắc muốn xóa mục này khỏi danh sách yêu thích không?")) {
    const id = btn.getAttribute('data-id');
    fetch(`xoa_yeuthich.php?id=${id}`)
      .then(res => res.text())
      .then(msg => {
        showToast(msg.includes('✅') ? '✅ Xóa thành công!' : '❌ Không thể xóa!', 
                  msg.includes('✅') ? 'success' : 'danger');
        setTimeout(() => window.location.reload(), 1500);
      });
  }
}

// Hàm toast thông báo đẹp
function showToast(message, type='info') {
  const color = type==='success' ? 'bg-success' : (type==='danger' ? 'bg-danger' : 'bg-primary');
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white border-0 ${color} show`;
  toast.role = 'alert';
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body fw-semibold">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  document.getElementById('toastContainer').appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
</script>

<style>
  .card {
    border-radius: 15px;
    overflow: hidden;
  }
  .table-hover tbody tr:hover {
    background-color: #eef4ff !important;
    transition: 0.2s;
  }
  .btn-sm {
    padding: 5px 8px !important;
  }
  .badge {
    font-size: 0.85rem;
  }
  .table img {
    border: 1px solid #ddd;
  }
</style>
