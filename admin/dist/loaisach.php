<?php
require_once('ketnoi.php');
$sql = "SELECT * FROM loaisach ORDER BY idloaisach DESC";
$result = mysqli_query($ketnoi, $sql);
?>

<style>
  /* ====== STYLE TINH GỌN VÀ HIỆN ĐẠI ====== */
  .card {
    border-radius: 18px;
    overflow: hidden;
    border: none;
  }

  .card-header {
    background: linear-gradient(90deg, #4f46e5, #06b6d4);
    padding: 1rem 1.5rem;
  }

  .card-header h4 {
    font-weight: 600;
    letter-spacing: 0.3px;
  }

  .btn-light {
    background-color: #ffffff;
    color: #0ea5e9;
    border: none;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .btn-light:hover {
    background-color: #e0f2fe;
    transform: translateY(-1px);
  }

  thead th {
    background-color: #e0f7fa !important;
    color: #00695c !important;
    font-weight: 600;
    border-bottom: none;
  }

  tbody tr {
    transition: 0.2s ease;
  }

  tbody tr:hover {
    background-color: #f1f8e9;
    transform: scale(1.01);
  }

  .btn-warning, .btn-danger {
    border: none;
    transition: 0.25s;
  }

  .btn-warning {
    background-color: #ffca28;
    color: #fff;
  }

  .btn-warning:hover {
    background-color: #ffb300;
  }

  .btn-danger {
    background-color: #ef5350;
    color: #fff;
  }

  .btn-danger:hover {
    background-color: #e53935;
  }

  .table {
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
  }

  td, th {
    vertical-align: middle;
  }

  .table-hover > tbody > tr:hover td {
    background-color: #f9fff8 !important;
  }
</style>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bx bx-category"></i> Danh sách thể loại</h4>
      <a href="index.php?page_layout=them_loaisach" class="btn btn-light btn-sm fw-semibold">
        <i class="bx bx-plus"></i> Thêm thể loại
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Tên thể loại</th>
              <th>Ngày tạo</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
              <tr>
                <td><strong><?= $i++; ?></strong></td>
                <td class="fw-semibold text-capitalize"><?= htmlspecialchars($row['tenloaisach']); ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="index.php?page_layout=sua_loaisach&idloaisach=<?= $row['idloaisach']; ?>"
                       class="btn btn-warning btn-sm rounded-circle" data-bs-toggle="tooltip" title="Chỉnh sửa">
                      <i class="bx bx-edit"></i>
                    </a>
                    <a href="index.php?page_layout=xoa_loaisach&idloaisach=<?= $row['idloaisach']; ?>"
                       onclick="return confirm('⚠️ Bạn có chắc muốn xóa thể loại này không?');"
                       class="btn btn-danger btn-sm rounded-circle" data-bs-toggle="tooltip" title="Xóa thể loại">
                      <i class="bx bx-trash"></i>
                    </a>
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

<script>
  // Tooltip Bootstrap
  document.addEventListener("DOMContentLoaded", () => {
    const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipList.map(el => new bootstrap.Tooltip(el));
  });
</script>
