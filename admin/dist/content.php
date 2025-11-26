<?php
require_once('ketnoi.php');

function get_count($table, $ketnoi) {
  $sql = "SELECT COUNT(*) AS total FROM $table";
  $result = mysqli_query($ketnoi, $sql);
  $res = mysqli_fetch_assoc($result);
  return $res['total'] ?? 0;
}

// Tổng số bản ghi
$count_sach = get_count('sach', $ketnoi);
$count_tacgia = get_count('tacgia', $ketnoi);
$count_loaisach = get_count('loaisach', $ketnoi);
$count_nguoidung = get_count('nguoidung', $ketnoi);
$count_donhang = get_count('donhang', $ketnoi);

// Doanh thu theo tháng
$currentYear = date('Y');
$sql_revenue_month = "
  SELECT MONTH(ngaydat) AS thang, SUM(tongtien) AS doanhthu
  FROM donhang
  WHERE YEAR(ngaydat) = $currentYear
  GROUP BY MONTH(ngaydat)
  ORDER BY thang ASC
";
$result_revenue_month = mysqli_query($ketnoi, $sql_revenue_month);

$revenue_by_month = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($result_revenue_month)) {
  $revenue_by_month[(int)$row['thang']] = (float)$row['doanhthu'];
}

// Top 5 sách bán chạy
$sql_top_books = "
  SELECT s.tensach, SUM(ct.soluong) AS soluongban
  FROM donhang_chitiet ct
  JOIN sach s ON ct.idsach = s.idsach
  GROUP BY ct.idsach
  ORDER BY soluongban DESC
  LIMIT 5
";
$result_top_books = mysqli_query($ketnoi, $sql_top_books);

$top_books = [];
$top_quantities = [];

while ($row = mysqli_fetch_assoc($result_top_books)) {
  $top_books[] = $row['tensach'];
  $top_quantities[] = (int)$row['soluongban'];
}

// 5 đơn bán gần nhất
$sql_recent = "
  SELECT dh.iddonhang, nd.hoten, dh.ngaydat,
         COALESCE(GROUP_CONCAT(s.tensach SEPARATOR ', '), '') AS tensach
  FROM donhang dh
  JOIN nguoidung nd ON dh.idnguoidung = nd.idnguoidung
  LEFT JOIN donhang_chitiet ct ON dh.iddonhang = ct.iddonhang
  LEFT JOIN sach s ON ct.idsach = s.idsach
  GROUP BY dh.iddonhang
  ORDER BY dh.ngaydat DESC
  LIMIT 5
";
$recent = mysqli_query($ketnoi, $sql_recent);
?>


<div class="container-fluid py-4 dashboard">
  <!-- Toast -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="welcomeToast" class="toast align-items-center text-bg-primary border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          👋 Chào mừng bạn trở lại trang quản lý bán sách! Hôm nay là một ngày tuyệt vời để bán sách 📚
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <!-- Cards tổng quan -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon bg-red"><i class='bx bx-book-open'></i></div>
      <div class="stat-title">Tổng số sách</div>
      <div class="stat-number"><?= $count_sach ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon bg-blue"><i class='bx bx-user-voice'></i></div>
      <div class="stat-title">Tác giả</div>
      <div class="stat-number"><?= $count_tacgia ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon bg-yellow"><i class='bx bx-category-alt'></i></div>
      <div class="stat-title">Thể loại</div>
      <div class="stat-number"><?= $count_loaisach ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon bg-green"><i class='bx bx-group'></i></div>
      <div class="stat-title">Người dùng</div>
      <div class="stat-number"><?= $count_nguoidung ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon bg-orange"><i class='bx bx-cart'></i></div>
      <div class="stat-title">Tổng đơn bán</div>
      <div class="stat-number"><?= $count_donhang ?></div>
    </div>
  </div>

  <!-- Chart section -->
  <div class="chart-row mt-4">
    <div class="chart-card">
      <div class="chart-title">Doanh thu bán sách</div>
      <div class="chart-subtitle">Doanh thu theo tháng năm <?= $currentYear ?></div>
      <canvas id="revenueChart"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">Tỉ lệ sách theo thể loại</div>
      <div class="chart-subtitle">Phân bổ phần trăm theo danh mục</div>
      <canvas id="typeChart"></canvas>
    </div>
  </div>

  <div class="chart-card" style="grid-column: 1 / -1; margin-top: 30px;">
    <div class="chart-title">Top 5 sách bán chạy nhất</div>
    <div class="chart-subtitle">Số lượng bán ra</div>
    <canvas id="topBooksChart"></canvas>
  </div>

  <!-- Recent activities -->
  <div class="recent-card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="fw-bold mb-0"><i class='bx bx-history me-1'></i> 5 đơn bán gần nhất</h5>
      <span class="text-muted small">Thông tin đơn hàng</span>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tên người mua</th>
            <th>Sách</th>
            <th>Ngày đặt</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          if (mysqli_num_rows($recent) > 0):
            while ($row = mysqli_fetch_assoc($recent)): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['hoten']) ?></td>
                <td><?= htmlspecialchars($row['tensach']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['ngaydat'])) ?></td>
              </tr>
          <?php endwhile; else: ?>
              <tr><td colspan="4" class="text-center text-muted py-3">Chưa có đơn bán nào</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
:root {
  --bg-card: linear-gradient(135deg, #ffffff, #fdf7f2);
  --bg-card-dark: linear-gradient(135deg, #2b2b2b, #1c1c1c);
  --text-primary: #222;
  --text-secondary: #555;
  --text-primary-dark: #f5f5f5;
  --text-secondary-dark: #ccc;
}

[data-bs-theme="dark"] .stat-card {
  background: var(--bg-card-dark);
  color: var(--text-primary-dark);
}
[data-bs-theme="dark"] .chart-card, [data-bs-theme="dark"] .recent-card {
  background: var(--bg-card-dark);
  color: var(--text-secondary-dark);
}

.dashboard { display: flex; flex-direction: column; gap: 24px; animation: fadeIn 0.6s ease; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 18px; }

.stat-card {
  background: var(--bg-card);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,0.1); }
.stat-icon { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; margin-bottom: 10px; }
.stat-title { font-size: 14px; font-weight: 600; color: var(--text-secondary); }
.stat-number { font-size: 26px; font-weight: 700; color: var(--text-primary); }

.bg-red { background: linear-gradient(135deg, #ef5350, #e53935); }
.bg-orange { background: linear-gradient(135deg, #fb8c00, #f57c00); }
.bg-yellow { background: linear-gradient(135deg, #fdd835, #fbc02d); color:#222; }
.bg-blue { background: linear-gradient(135deg, #42a5f5, #1e88e5); }
.bg-green { background: linear-gradient(135deg, #66bb6a, #43a047); }

.chart-row { display: grid; grid-template-columns: 2fr 1fr; gap: 22px; }
.chart-card, .recent-card {
  background: var(--bg-card);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.06);
  transition: box-shadow 0.3s ease;
}
.chart-card:hover, .recent-card:hover { box-shadow: 0 12px 30px rgba(0,0,0,0.09); }
.chart-title { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
.chart-subtitle { font-size: 13px; color: #777; margin-bottom: 18px; }

@keyframes fadeIn { from {opacity: 0; transform: translateY(12px);} to {opacity: 1; transform: translateY(0);} }
@media(max-width: 992px) { .chart-row { grid-template-columns: 1fr; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const toast = new bootstrap.Toast(document.getElementById('welcomeToast'));
  toast.show();
});

// Biểu đồ doanh thu bán sách theo tháng
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
    datasets: [{
      label: 'Doanh thu (VNĐ)',
      data: <?= json_encode(array_values($revenue_by_month)) ?>,
      borderWidth: 3,
      tension: 0.3,
      fill: true,
      backgroundColor: 'rgba(66, 165, 245, 0.2)',
      borderColor: '#1e88e5'
    }]
  },
  options: {
    plugins: { legend: { display: true } },
    scales: { y: { beginAtZero: true } }
  }
});

// Biểu đồ tỉ lệ sách theo thể loại (giữ nguyên tạm như mẫu)
new Chart(document.getElementById('typeChart'), {
  type: 'doughnut',
  data: {
    labels: ['Văn học','Khoa học','Thiếu nhi','Kinh tế','Lịch sử'],
    datasets: [{
      data: [10,7,5,8,4],
      backgroundColor: ['#ef5350','#42a5f5','#ffee58','#66bb6a','#ab47bc'],
      borderWidth: 1
    }]
  },
  options: {
    plugins: {
      legend: { position: 'bottom', labels: { font: { size: 12 } } }
    }
  }
});

// Biểu đồ top 5 sách bán chạy nhất
new Chart(document.getElementById('topBooksChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($top_books) ?>,
    datasets: [{
      label: 'Số lượng bán',
      data: <?= json_encode($top_quantities) ?>,
      backgroundColor: '#ffa726',
      borderRadius: 6
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, stepSize: 1 },
      x: { ticks: { maxRotation: 45, minRotation: 30 } }
    }
  }
});
</script>
