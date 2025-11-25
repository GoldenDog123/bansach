<?php
require_once('ketnoi.php');
$sql = "SELECT * FROM nguoidung ORDER BY idnguoidung DESC";
$query = mysqli_query($ketnoi, $sql);
?>

<!-- ===== CSS hiện đại ===== -->
<style>
.table-wrapper {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
  padding: 24px;
  animation: fadeUp 0.5s ease;
}

@keyframes fadeUp {
  from {opacity: 0; transform: translateY(20px);}
  to {opacity: 1; transform: translateY(0);}
}

.table thead th {
  background: linear-gradient(90deg, #2563eb, #1d4ed8);
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 13px;
  border: none;
}

.table tbody tr {
  transition: all 0.25s ease;
}

.table tbody tr:hover {
  background: #f0f9ff;
  transform: scale(1.005);
}

.badge {
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
}

.btn {
  border: none;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  transition: 0.25s;
}

.btn-add {
  background: linear-gradient(90deg, #22c55e, #16a34a);
  color: #fff;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
}
.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(34,197,94,0.3);
}

.btn-edit {
  background: #facc15;
  color: #000;
}
.btn-edit:hover {
  background: #eab308;
}

.btn-delete {
  background: #ef4444;
  color: #fff;
}
.btn-delete:hover {
  background: #dc2626;
}

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 8px;
}
</style>

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold text-primary"><i class="bx bx-group"></i> Quản lý người dùng</h3>
      <a href="index.php?page_layout=them_nguoidung" class="btn btn-add">
        <i class="bx bx-plus"></i> Thêm người dùng
      </a>
    </div>

    <div class="table-wrapper">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Số điện thoại</th>
            <th>Vai trò</th>
            <th>Ngày tạo</th>
            <th style="text-align:center;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $i = 1;
          while ($row = mysqli_fetch_assoc($query)) { ?>
          <tr>
            <td><?= $i++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($row['hoten']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['sdt']) ?></td>
            <td>
              <?php 
                if ($row['vaitro'] == 'admin')
                  echo '<span class="badge bg-danger">Admin</span>';
                elseif ($row['vaitro'] == 'thuthu')
                  echo '<span class="badge bg-warning text-dark">Thủ thư</span>';
                else
                  echo '<span class="badge bg-info text-dark">Học sinh</span>';
              ?>
            </td>
            <td><?= date('d/m/Y', strtotime($row['ngaytao'])) ?></td>
            <td>
              <div class="action-buttons">
                <a href="index.php?page_layout=sua_nguoidung&id=<?= $row['idnguoidung'] ?>" class="btn btn-edit">
                  <i class="bx bx-edit"></i> Sửa
                </a>
                <a href="index.php?page_layout=xoa_nguoidung&id=<?= $row['idnguoidung'] ?>" 
                   class="btn btn-delete"
                   onclick="return confirm('Bạn có chắc muốn xóa người dùng này không?')">
                  <i class="bx bx-trash"></i> Xóa
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

<!-- SweetAlert2 Toast -->
<?php if (isset($_SESSION['toast'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  toast: true,
  position: 'top-end',
  icon: '<?= $_SESSION['toast']['type'] ?>',
  title: '<?= $_SESSION['toast']['msg'] ?>',
  showConfirmButton: false,
  timer: 2300,
  timerProgressBar: true
});
</script>
<?php unset($_SESSION['toast']); endif; ?>
