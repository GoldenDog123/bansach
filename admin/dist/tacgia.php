<?php
require_once('ketnoi.php');
if (!isset($ketnoi)) exit("Kết nối thất bại");

$sql = "SELECT * FROM tacgia ORDER BY idtacgia DESC";
$res = mysqli_query($ketnoi, $sql);
?>

<style>
.table-wrap { overflow-x:auto; }
.table th { background:#1e40af; color:#fff; }
.table tr:hover { background:#f1f5f9; }
.toast-msg {
  position: fixed; top: 24px; right: 24px; z-index: 1055;
  background: #1e3a8a; color: #fff; padding: 12px 20px;
  border-radius: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.2);
  opacity: 0; transform: translateX(50px); transition: all .4s;
}
.toast-msg.show { opacity: 1; transform: translateX(0); }
</style>

<div class="card shadow-sm p-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold text-primary"><i class="mdi mdi-account-edit"></i> Danh sách tác giả</h4>
    <a href="index.php?page_layout=them_tacgia" class="btn btn-primary">
      <i class="mdi mdi-plus"></i> Thêm tác giả
    </a>
  </div>

  <div class="table-wrap">
    <table class="table align-middle table-bordered">
      <thead>
        <tr>
          <th style="width:60px;">#</th>
          <th>Tên tác giả</th>
          <th>Ghi chú</th>
          <th style="width:180px;">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (mysqli_num_rows($res) > 0) {
          $i = 1;
          while ($row = mysqli_fetch_assoc($res)) {
            echo "<tr>
              <td>{$i}</td>
              <td>".htmlspecialchars($row['tentacgia'])."</td>
              <td>".htmlspecialchars($row['ghichu'] ?? '')."</td>
              <td>
                <a href='index.php?page_layout=sua_tacgia&id={$row['idtacgia']}' class='btn btn-warning btn-sm'><i class='mdi mdi-pencil'></i> Sửa</a>
                <a href='index.php?page_layout=xoa_tacgia&id={$row['idtacgia']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Xóa tác giả này?')\"><i class='mdi mdi-delete'></i> Xóa</a>
              </td>
            </tr>";
            $i++;
          }
        } else {
          echo "<tr><td colspan='4' class='text-center text-muted py-3'>Chưa có tác giả nào</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Hiển thị thông báo toast
document.addEventListener('DOMContentLoaded', () => {
  const toastData = localStorage.getItem('toast');
  if (toastData) {
    const { msg, type } = JSON.parse(toastData);
    const el = document.createElement('div');
    el.className = 'toast-msg';
    el.textContent = msg;
    el.style.background = type === 'success' ? '#16a34a' : '#dc2626';
    document.body.appendChild(el);
    setTimeout(() => el.classList.add('show'), 100);
    setTimeout(() => { el.classList.remove('show'); setTimeout(()=>el.remove(), 400); }, 2500);
    localStorage.removeItem('toast');
  }
});
</script>
