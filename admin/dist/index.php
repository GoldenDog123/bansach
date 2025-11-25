<?php
ob_start();
session_start();
require_once('ketnoi.php');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thư Viện Trường Học</title>

  <!-- Bootstrap & Boxicons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <style>
    :root {
      --primary: #4f46e5;
      --accent: #06b6d4;
      --bg-light: #f3f4f6;
      --bg-dark: #0f172a;
      --text-light: #1f2937;
      --text-dark: #f3f4f6;
      --sidebar-w: 260px;
      --navbar-h: 70px;
      --radius: 14px;
      --card-bg: rgba(255, 255, 255, 0.7);
      --card-dark: rgba(30, 41, 59, 0.6);
    }

    /* Dark Mode */
    body.dark {
      --bg-light: var(--bg-dark);
      --text-light: var(--text-dark);
      --card-bg: var(--card-dark);
    }

    /* Reset */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: "Poppins", sans-serif;
      background: var(--bg-light);
      color: var(--text-light);
      transition: background 0.4s ease, color 0.4s ease;
      overflow-x: hidden;
    }

    /* Navbar */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: var(--navbar-h);
      background: linear-gradient(90deg, var(--primary), var(--accent));
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      color: white;
      z-index: 1100;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
    }

    .navbar .brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .navbar .brand img {
      width: 44px;
      height: 44px;
      border-radius: 10px;
    }

    .navbar .title {
      font-weight: 700;
      font-size: 18px;
    }

    .navbar .actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: var(--navbar-h);
      left: 0;
      width: var(--sidebar-w);
      height: calc(100vh - var(--navbar-h));
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(14px);
      box-shadow: 4px 0 16px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      overflow-y: auto;
      padding: 20px 10px;
      z-index: 1000;
    }

    body.dark .sidebar {
      background: rgba(15, 23, 42, 0.7);
    }

    .sidebar .nav-category {
      font-size: 12px;
      text-transform: uppercase;
      color: #9ca3af;
      margin: 16px 0 8px 14px;
      letter-spacing: 0.5px;
    }

    .sidebar .nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border-radius: 10px;
      text-decoration: none;
      color: var(--text-light);
      font-weight: 500;
      transition: 0.3s;
    }

    .sidebar .nav-link i {
      font-size: 20px;
      color: var(--primary);
    }

    .sidebar .nav-link:hover {
      background: linear-gradient(90deg, #e0f2fe, #fef9c3);
      transform: translateX(5px);
    }

    .sidebar .nav-link.active {
      background: linear-gradient(90deg, var(--primary), var(--accent));
      color: white;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .sidebar .nav-link.active i {
      color: white;
    }

    /* Toggle sidebar */
    #toggleSidebar {
      display: none;
      cursor: pointer;
      font-size: 26px;
    }

    @media(max-width:992px) {
      .sidebar {
        left: -100%;
      }

      .sidebar.show {
        left: 0;
      }

      #toggleSidebar {
        display: block;
      }
    }

    /* Content */
    .content-area {
      margin-left: var(--sidebar-w);
      margin-top: var(--navbar-h);
      padding: 32px;
      transition: margin 0.3s ease;
    }

    @media(max-width:992px) {
      .content-area {
        margin-left: 0;
      }
    }

    .card {
      background: var(--card-bg);
      border: none;
      border-radius: var(--radius);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      padding: 24px;
      backdrop-filter: blur(12px);
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-4px);
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 10px;
    }

    thead th {
      background: linear-gradient(90deg, var(--primary), var(--accent));
      color: white;
      padding: 12px;
      border-radius: 8px;
      text-transform: uppercase;
      font-size: 13px;
    }

    tbody tr {
      background: rgba(255, 255, 255, 0.9);
      transition: all 0.2s ease;
    }

    tbody tr:hover {
      transform: scale(1.01);
      background: #e0f2fe;
    }

    td {
      padding: 12px;
    }

    .footer {
      text-align: center;
      margin-top: 40px;
      font-size: 13px;
      color: #9ca3af;
    }

    /* Dark mode button */
    .toggle-dark {
      cursor: pointer;
      background: rgba(255, 255, 255, 0.2);
      border: none;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      transition: 0.3s;
    }

    .toggle-dark:hover {
      background: rgba(255, 255, 255, 0.35);
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="brand">
      <i id="toggleSidebar" class='bx bx-menu'></i>
      <img src="assets/images/logothuvien.png" alt="logo">
      <div>
        <div class="title">Thư viện CTECH</div>
        <div style="font-size:12px;">Quản lý & Mượn trả</div>
      </div>
    </div>
    <div class="actions">
      <form class="d-none d-md-block" style="position:relative;">
        <input type="search" class="form-control form-control-sm rounded-pill ps-3 pe-5" placeholder="Tìm kiếm...">
        <i class='bx bx-search position-absolute top-50 end-0 translate-middle-y me-3'></i>
      </form>
      <button class="toggle-dark" id="darkModeToggle"><i class='bx bx-moon'></i></button>
      <div class="dropdown">
        <a href="#" data-bs-toggle="dropdown" class="text-white text-decoration-none d-flex align-items-center">
          <i class='bx bx-user-circle fs-3'></i>
          <span class="ms-1 fw-semibold">Binh</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li><a class="dropdown-item" href="#">Hồ sơ</a></li>
          <li><a class="dropdown-item" href="#">Đăng xuất</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar">
    <ul class="nav flex-column">
      <li><a href="index.php" class="nav-link active">
          <i class='bx bx-home'></i> <span>Trang chủ</span>
        </a></li>

      <div class="nav-category">Quản lý</div>

      <li><a href="index.php?page_layout=danhsachloaisach" class="nav-link">
          <i class='bx bx-category'></i><span>Thể loại</span>
        </a></li>

      <li><a href="index.php?page_layout=danhsachsach" class="nav-link">
          <i class='bx bx-book'></i><span>Sách</span>
        </a></li>

    <li><a href="index.php?page_layout=danhsachdonhang" class="nav-link">
  <i class='bx bx-cart'></i><span>Quản lý đơn hàng</span>
</a></li>

      <li><a href="index.php?page_layout=danhsachtacgia" class="nav-link">
          <i class='bx bx-user-voice'></i><span>Tác giả</span>
        </a></li>

      <li><a href="index.php?page_layout=danhsachnguoidung" class="nav-link">
          <i class='bx bx-group'></i><span>Người dùng</span>
        </a></li>

      <li><a href="index.php?page_layout=danhsachyeuthich" class="nav-link">
          <i class='bx bx-heart'></i><span>Yêu thích</span>
        </a></li>
    </ul>
  </nav>


  <!-- Content -->
  <div class="content-area">
    <div class="content-wrapper">
      <div class="mb-3 text-muted small">
        <?php
        $crumb = 'Trang chủ';
        $map = [
          'danhsachloaisach' => 'Thể loại sách',
          'danhsachsach' => 'Sách',
          'danhsachmuonsach' => 'Mượn sách',
          'danhsachtacgia' => 'Tác giả',
          'danhsachnguoidung' => 'Người dùng'
        ];
        if (isset($_GET['page_layout']) && isset($map[$_GET['page_layout']])) {
          $crumb = 'Trang chủ / ' . $map[$_GET['page_layout']];
        }
        echo htmlspecialchars($crumb);
        ?>
      </div>

      <div class="card">
        <?php
        if (isset($_GET["page_layout"])) {
          switch ($_GET["page_layout"]) {
            // --- NGƯỜI DÙNG ---
            case "danhsachnguoidung":
              require_once 'nguoidung.php';
              break;
            case "them_nguoidung":
              require_once 'them_nguoidung.php';
              break;
            case "sua_nguoidung":
              require_once 'sua_nguoidung.php';
              break;
            case "xoa_nguoidung":
              require_once 'xoa_nguoidung.php';
              break;

            // --- TÁC GIẢ ---
            case "danhsachtacgia":
              require_once 'tacgia.php';
              break;
            case "them_tacgia":
              require_once 'them_tacgia.php';
              break;
            case "sua_tacgia":
              require_once 'sua_tacgia.php';
              break;
            case "xoa_tacgia":
              require_once 'xoa_tacgia.php';
              break;

            // --- LOẠI SÁCH ---
            case "danhsachloaisach":
              require_once 'loaisach.php';
              break;
            case "them_loaisach":
              require_once 'them_loaisach.php';
              break;
            case "sua_loaisach":
              require_once 'sua_loaisach.php';
              break;
            case "xoa_loaisach":
              require_once 'xoa_loaisach.php';
              break;

            // --- SÁCH ---
            case "danhsachsach":
              require_once 'sach.php';
              break;
            case "them_sach":
              require_once 'them_sach.php';
              break;
            case "sua_sach":
              require_once 'sua_sach.php';
              break;
            case "xoa_sach":
              require_once 'xoa_sach.php';
              break;

            // --- BÁN SÁCH ---
            case "danhsachdonhang":
              require_once 'donhang.php';
              break;

            case "them_donhang":
              require_once 'them_donhang.php';
              break;

            case "sua_donhang":
              require_once 'sua_donhang.php';
              break;

            case "xoa_donhang":
              require_once 'xoa_donhang.php';
              break;

            // --- YÊU THÍCH ---
            case "danhsachyeuthich":
              require_once 'yeuthich.php';
              break;
            case "them_yeuthich":
              require_once 'them_yeuthich.php';
              break;
            case "sua_yeuthich":
              require_once 'sua_yeuthich.php';
              break;
            case "xoa_yeuthich":
              require_once 'xoa_yeuthich.php';
              break;

            // --- MẶC ĐỊNH ---
            default:
              require_once 'content.php';
              break;
          }
        } else {
          require_once 'content.php';
        }
        ?>

      </div>

      <div class="footer">© <?php echo date('Y'); ?> • Thiết kế Ultra UI bởi <strong>Binh</strong></div>
    </div>
  </div>

  <!-- JS -->
  <script>
    document.getElementById('toggleSidebar').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('show');
    });
    const darkToggle = document.getElementById('darkModeToggle');
    if (localStorage.getItem('dark') === 'true') document.body.classList.add('dark');
    darkToggle.onclick = () => {
      document.body.classList.toggle('dark');
      localStorage.setItem('dark', document.body.classList.contains('dark'));
      darkToggle.innerHTML = document.body.classList.contains('dark') ?
        "<i class='bx bx-sun'></i>" : "<i class='bx bx-moon'></i>";
    };

    // Active menu
    (function() {
      const url = window.location.href;
      document.querySelectorAll('.sidebar .nav-link').forEach(a => {
        if (url.includes(a.getAttribute('href'))) {
          document.querySelectorAll('.sidebar .nav-link').forEach(x => x.classList.remove('active'));
          a.classList.add('active');
        }
      });
    })();
  </script>
  <!-- ==== TOAST THÔNG BÁO CHUNG ==== -->
  <style>
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      min-width: 260px;
      padding: 12px 18px;
      border-radius: 8px;
      color: #fff;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      opacity: 0;
      transform: translateY(-20px);
      transition: all 0.4s ease;
      z-index: 9999;
    }

    .toast.show {
      opacity: 1;
      transform: translateY(0);
    }

    .toast.success {
      background: linear-gradient(135deg, #4CAF50, #2E7D32);
    }

    .toast.danger {
      background: linear-gradient(135deg, #f44336, #b71c1c);
    }

    .toast.warning {
      background: linear-gradient(135deg, #FF9800, #E65100);
    }
  </style>

  <script>
    function showToast(message, type = 'success') {
      // Tạo khối toast
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.textContent = message;

      document.body.appendChild(toast);
      // Hiện lên mượt
      setTimeout(() => toast.classList.add('show'), 100);
      // Ẩn sau 3s
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
      }, 3000);
    }
  </script>
  <!-- ==== KẾT THÚC TOAST ==== -->

</body>

</html>
<?php ob_end_flush(); ?>