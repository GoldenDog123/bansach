<?php
// donhang.php
require_once('ketnoi.php');


// ---------- CSRF token ----------
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];

// ---------- INPUTS ----------
$keyword = isset($_GET['search']) ? trim($_GET['search']) : "";
$filter  = isset($_GET['status']) ? trim($_GET['status']) : "";
$sort    = isset($_GET['sort']) ? trim($_GET['sort']) : "dh.iddonhang";
$order   = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

// ---------- PAGINATION ----------
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// ---------- WHERE CLAUSE ----------
$where = " WHERE 1=1 ";
$types = "";
$params = [];

if ($keyword !== "") {
  $where .= " AND (dh.iddonhang LIKE ? OR nd.hoten LIKE ?) ";
  $kw_like = "%{$keyword}%";
  $types .= "ss";
  $params[] = $kw_like;
  $params[] = $kw_like;
}

$allowed_status = ['cho_duyet', 'dang_giao', 'hoan_thanh', 'da_huy'];
if ($filter !== "" && in_array($filter, $allowed_status, true)) {
  $where .= " AND dh.trangthai = ? ";
  $types .= "s";
  $params[] = $filter;
}

// ---------- MAIN QUERY ----------
$sql = "
    SELECT
        dh.iddonhang,
        nd.idnguoidung,
        nd.hoten AS tennguoidung,
        GROUP_CONCAT(DISTINCT s.tensach SEPARATOR ', ') AS tensach,
        dh.ngaydat,
        dh.trangthai AS trangthai_don
    FROM donhang dh
    JOIN nguoidung nd ON dh.idnguoidung = nd.idnguoidung
    JOIN donhang_chitiet ct ON dh.iddonhang = ct.iddonhang
    JOIN sach s ON ct.idsach = s.idsach
    $where
    GROUP BY dh.iddonhang
";

// ---------- COUNT TOTAL ----------
$sql_count = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT dh.iddonhang
        FROM donhang dh
        JOIN donhang_chitiet ct ON dh.iddonhang = ct.iddonhang
        JOIN sach s ON ct.idsach = s.idsach
        JOIN nguoidung nd ON dh.idnguoidung = nd.idnguoidung
        $where
        GROUP BY dh.iddonhang
    ) t
";

$stmt = $ketnoi->prepare($sql_count);
if ($stmt === false) die("Prepare failed: " . htmlspecialchars($ketnoi->error));

if ($types !== "") {
  $bind_names = [];
  $bind_names[] = $types;
  for ($i = 0; $i < count($params); $i++) $bind_names[] = &$params[$i];
  call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

$stmt->execute();
$res = $stmt->get_result();
$total = ($r = $res->fetch_assoc()) ? (int)$r['total'] : 0;
$stmt->close();

$totalPages = max(1, ceil($total / $perPage));

// ---------- FINAL QUERY ----------
$sql .= " ORDER BY $sort $order LIMIT ? OFFSET ? ";
$types_final = $types . "ii";
$params_final = array_merge($params, [$perPage, $offset]);

$stmt = $ketnoi->prepare($sql);
if ($stmt === false) die("Prepare failed: " . htmlspecialchars($ketnoi->error));

$bind_names = [];
$bind_names[] = $types_final;
for ($i = 0; $i < count($params_final); $i++) $bind_names[] = &$params_final[$i];
call_user_func_array([$stmt, 'bind_param'], $bind_names);

$stmt->execute();
$result = $stmt->get_result();

// ---------- STATUS CHIP ----------
function status_chip($value)
{
  switch ($value) {
    case 'cho_duyet':
      return '<span class="status-chip wait">Chờ duyệt</span>';
    case 'dang_giao':
      return '<span class="status-chip shipping">Đang giao</span>';
    case 'hoan_thanh':
      return '<span class="status-chip done">Hoàn thành</span>';
    case 'da_huy':
      return '<span class="status-chip cancel">Đã hủy</span>';
    default:
      return '<span class="status-chip grey">Không rõ</span>';
  }
}
?>

<style>
  /* CSS tương tự trước nhưng tối ưu action buttons & status chip */
  .card {
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 16px;
    background: #fff;
  }

  .toolbar {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    align-items: center;
    flex-wrap: wrap;
  }

  .toolbar input,
  .toolbar select {
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
  }

  .toolbar input:focus,
  .toolbar select:focus {
    border-color: #26a69a;
    box-shadow: 0 0 6px rgba(38, 166, 154, 0.2);
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  table thead th {
    background: #e0f2f1;
    font-weight: 600;
    text-align: center;
    padding: 10px;
  }

  table tbody td {
    padding: 10px;
    vertical-align: middle;
  }

  tbody tr:hover {
    background: #f1f8e9;
    transition: 0.2s;
  }

  .status-chip {
    padding: 6px 16px;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
    text-align: center;
    min-width: 80px;
  }

  .wait {
    background: #ffe082;
    color: #6d4c41;
  }

  .shipping {
    background: #64b5f6;
    color: #0d47a1;
  }

  .done {
    background: #81c784;
    color: #1b5e20;
  }

  .cancel {
    background: #ef9a9a;
    color: #b71c1c;
  }

  .grey {
    background: #cfd8dc;
    color: #37474f;
  }

  .action-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
  }

  .action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  }

  td>.d-flex {
    gap: 10px;
    justify-content: center;
  }

  .pagination {
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: center;
    margin-top: 16px;
    flex-wrap: wrap;
  }

  .pagination a,
  .pagination span {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #37474f;
    transition: all 0.2s;
  }

  .pagination a:hover {
    background: #26a69a;
    color: #fff;
    border-color: #26a69a;
  }

  .pagination .active {
    background: #2e7d32;
    color: #fff;
    border-color: #2e7d32;
  }
</style>

<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
      <h4 class="mb-0"><i class='bx bx-cart-alt'></i> Quản lý đơn hàng</h4>
      <a href="index.php?page_layout=them_donhang" class="btn btn-light btn-sm"><i class="bx bx-plus"></i> Thêm đơn</a>
    </div>
    <div class="card-body">
      <!-- Toolbar -->
      <form method="GET" class="toolbar">
        <input type="hidden" name="page_layout" value="danhsachdonhang">
        <input type="text" name="search" placeholder="Tìm ID đơn / tên khách / tên sách..." value="<?= htmlspecialchars($keyword) ?>">
        <select name="status">
          <option value="">-- Lọc trạng thái --</option>
          <option value="cho_duyet" <?= $filter == "cho_duyet" ? "selected" : "" ?>>Chờ duyệt</option>
          <option value="dang_giao" <?= $filter == "dang_giao" ? "selected" : "" ?>>Đang giao</option>
          <option value="hoan_thanh" <?= $filter == "hoan_thanh" ? "selected" : "" ?>>Hoàn thành</option>
          <option value="da_huy" <?= $filter == "da_huy" ? "selected" : "" ?>>Đã hủy</option>
        </select>
        <select name="sort">
          <option value="dh.iddonhang" <?= $sort == "dh.iddonhang" ? "selected" : "" ?>>Mới nhất</option>
          <option value="dh.ngaydat" <?= $sort == "dh.ngaydat" ? "selected" : "" ?>>Ngày đặt</option>
        </select>
        <select name="order">
          <option value="desc" <?= $order == "DESC" ? "selected" : "" ?>>Giảm dần</option>
          <option value="asc" <?= $order == "ASC" ? "selected" : "" ?>>Tăng dần</option>
        </select>
        <button class="btn btn-success"><i class="bx bx-search"></i> Lọc</button>
      </form>

      <!-- Table -->
      <div class="table-responsive mt-3">
        <table class="table table-hover align-middle text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>Mã đơn</th>
              <th>Người mua</th>
              <th>Sách</th>
              <th>Ngày đặt</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = $offset + 1;
            while ($row = $result->fetch_assoc()) { ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><strong>#<?= htmlspecialchars($row['iddonhang']) ?></strong></td>
                <td><?= htmlspecialchars($row['tennguoidung']) ?></td>
                <td style="max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($row['tensach']) ?></td>
                <td><?= date("d/m/Y", strtotime($row['ngaydat'])) ?></td>
                <td><?= status_chip($row['trangthai_don']); ?></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <!-- Duyệt đơn -->
                    <a class="btn btn-success btn-sm action-btn"
                      title="Duyệt đơn"
                      href="index.php?page_layout=duyet_donhang&iddonhang=<?= urlencode($row['iddonhang']) ?>&csrf=<?= $csrf_token ?>"
                      onclick="return confirm('Bạn có chắc muốn duyệt đơn này?');">
                      <i class="bx bx-check"></i>
                    </a>

                    <!-- Thanh toán -->
                    <a class="btn btn-info btn-sm action-btn"
                      title="Thanh toán"
                      href="index.php?page_layout=capnhap_thanhtoan&iddonhang=<?= urlencode($row['iddonhang']) ?>&csrf=<?= $csrf_token ?>"
                      onclick="return confirm('Bạn có chắc muốn đánh dấu đơn này là đã thanh toán?');">
                      <i class="bx bx-dollar-circle"></i>
                    </a>

                    <!-- Sửa -->
                    <a class="btn btn-warning btn-sm action-btn"
                      title="Sửa"
                      href="index.php?page_layout=sua_donhang&iddonhang=<?= urlencode($row['iddonhang']) ?>">
                      <i class="bx bx-edit"></i>
                    </a>

                    <!-- Xóa -->
                    <a class="btn btn-danger btn-sm action-btn"
                      title="Xóa"
                      onclick="return confirm('Xác nhận xóa đơn hàng?');"
                      href="index.php?page_layout=xoa_donhang&iddonhang=<?= urlencode($row['iddonhang']) ?>&csrf=<?= $csrf_token ?>">
                      <i class="bx bx-trash"></i>
                    </a>
                  </div>
                </td>

              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <?php
        $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
        $queryParams = $_GET;
        for ($p = 1; $p <= $totalPages; $p++) {
          $queryParams['page'] = $p;
          $qs = http_build_query($queryParams);
          $class = ($p == $page) ? 'active' : '';
          echo "<a class='{$class}' href=\"{$baseUrl}?{$qs}\">{$p}</a>";
        }
        ?>
      </div>
    </div>
  </div>
</div>
<!-- Modal Xác nhận -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Xác nhận hành động</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        <p id="confirmMessage">Bạn có chắc chắn?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <a href="#" id="confirmBtn" class="btn btn-primary">Xác nhận</a>
      </div>
    </div>
  </div>
</div>

<?php
$stmt->close();
$ketnoi->close();
?>