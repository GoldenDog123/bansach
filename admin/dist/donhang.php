<?php
require_once('ketnoi.php');

// Lấy danh sách đơn bán sách
$sql = "SELECT 
            donhang.iddonhang,
            nguoidung.hoten AS tennguoidung,
            sach.tensach,
            donhang.ngaydat,
            donhang.trangthai AS trangthai_don,
            giaohang.trangthai AS trangthai_giaohang,
            thanhtoan.trangthai AS trangthai_thanhtoan
        FROM donhang
        JOIN nguoidung ON donhang.idnguoidung = nguoidung.idnguoidung
        JOIN donhang_chitiet ON donhang.iddonhang = donhang_chitiet.iddonhang
        JOIN sach ON donhang_chitiet.idsach = sach.idsach
        LEFT JOIN giaohang ON donhang.iddonhang = giaohang.iddonhang
        LEFT JOIN thanhtoan ON donhang.iddonhang = thanhtoan.iddonhang
        ORDER BY donhang.iddonhang DESC";

$result = mysqli_query($ketnoi, $sql);
?>


<style>
  .card { border-radius: 18px; overflow: hidden; }
  .card-header {
    background: linear-gradient(135deg, #00bfa5, #009688);
    border-bottom: none;
    padding: 1rem 1.5rem;
  }
  .card-header h4 { font-weight: 600; }
  thead th { background-color: #e0f7fa !important; color: #00695c !important; font-weight: 600; }
  tbody tr:hover { background-color: #f1f8e9; }
</style>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class='bx bx-cart'></i> Quản lý bán sách</h4>
      <a href="index.php?page_layout=them_donhang" class="btn btn-light btn-sm">
        <i class="bx bx-plus"></i> Thêm đơn hàng
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead>
            <tr>
              <th>STT</th>
              <th>Người mua</th>
              <th>Sách</th>
              <th>Ngày đặt</th>
              <th>Trạng thái đơn</th>
              <th>Thanh toán</th>
              <th>Giao hàng</th>
              <th>Hành động</th>
            </tr>
          </thead>

          <tbody>
            <?php $i = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= htmlspecialchars($row['tennguoidung']); ?></td>
                <td><?= htmlspecialchars($row['tensach']); ?></td>
                <td><?= date('d/m/Y', strtotime($row['ngaydat'])); ?></td>

                <td><span class="badge bg-info text-dark"><?= $row['trangthai_don']; ?></span></td>

                <td>
                  <?= $row['trangthai_thanhtoan'] == 'thanh_cong' 
                      ? "<span class='badge bg-success'>Đã thanh toán</span>"
                      : "<span class='badge bg-warning'>Chưa thanh toán</span>"; ?>
                </td>

                <td>
                  <?= $row['trangthai_giaohang'] 
                      ? "<span class='badge bg-primary'>{$row['trangthai_giaohang']}</span>"
                      : "<span class='badge bg-secondary'>Chưa giao</span>"; ?>
                </td>

                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="index.php?page_layout=sua_donhang&iddonhang=<?= $row['iddonhang']; ?>" 
                       class="btn btn-warning btn-sm rounded-circle" title="Chỉnh sửa">
                      <i class="bx bx-edit"></i>
                    </a>
                    <a href="index.php?page_layout=xoa_donhang&iddonhang=<?= $row['iddonhang']; ?>" 
                       class="btn btn-danger btn-sm rounded-circle"
                       onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này không?');">
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
