<?php
ob_start();
session_start();
require_once('ketnoi.php');

$page_layout = $_GET['page_layout'] ?? 'dashboard';

function isActive($layout, $currentLayout)
{
  return $layout === $currentLayout ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | Cửa Hàng Sách Pro</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    /* === TÙY CHỈNH THIẾT KẾ (CSS) === */
    :root {
      --primary: #6b46c1;
      /* Deep Purple */
      --accent: #805ad5;
      /* Lighter Purple */
      --secondary-gradient: linear-gradient(135deg, #a855f7, #ec4899);
      /* Violet to Pink */
      --bg-light: #f7f9fc;
      --bg-dark: #1a202c;
      /* Darker background for more contrast */
      --text-light: #2d3748;
      --text-dark: #edf2f7;
      --sidebar-w: 280px;
      /* Slightly wider sidebar */
      --navbar-h: 75px;
      /* Slightly taller navbar */
      --radius: 1rem;
      /* More pronounced border-radius */
      --card-bg-light: rgba(255, 255, 255, 0.95);
      --card-bg-dark: rgba(26, 32, 44, 0.95);
      /* Darker, slightly transparent */
      --shadow-light: 0 10px 30px rgba(0, 0, 0, 0.08);
      --shadow-dark: 0 10px 30px rgba(0, 0, 0, 0.3);
      --link-hover-light: rgba(107, 70, 193, 0.1);
      --link-hover-dark: rgba(107, 70, 193, 0.2);
    }

    /* Dark Mode */
    body.dark {
      --bg-light: var(--bg-dark);
      --text-light: var(--text-dark);
      --card-bg-light: var(--card-bg-dark);
      --shadow-light: var(--shadow-dark);
      --link-hover-light: var(--link-hover-dark);
    }

    /* Reset */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      /* Sử dụng Poppins font */
      background: var(--bg-light);
      color: var(--text-light);
      transition: background 0.5s ease, color 0.5s ease;
      overflow-x: hidden;
      min-height: 100vh;
    }

    /* Preloader */
    #preloader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--bg-light);
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: opacity 0.5s ease;
    }

    #preloader.hidden {
      opacity: 0;
      visibility: hidden;
    }

    .spinner-grow {
      color: var(--primary);
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
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .navbar .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      transition: all 0.3s ease;
    }

    .navbar .brand:hover {
      transform: translateX(5px);
    }

    .navbar .brand img {
      width: 48px;
      /* Slightly larger */
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(255, 255, 255, 0.7);
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .navbar .title {
      font-weight: 700;
      font-size: 22px;
      /* Larger title */
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .navbar .subtitle {
      font-size: 13px;
      opacity: 0.85;
    }

    .navbar .actions {
      display: flex;
      align-items: center;
      gap: 20px;
      /* More spacing */
    }

    /* Search input style */
    .navbar input[type="search"] {
      border: none;
      background: rgba(255, 255, 255, 0.25);
      color: white;
      padding: 8px 15px !important;
      padding-right: 45px !important;
      border-radius: 50px !important;
      transition: all 0.3s ease;
    }

    .navbar input[type="search"]::placeholder {
      color: rgba(255, 255, 255, 0.8);
    }

    .navbar input[type="search"]:focus {
      box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.5);
      background: rgba(255, 255, 255, 0.4);
      color: white;
      transform: scale(1.02);
    }

    .navbar .bx-search {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.3rem;
    }

    /* User Dropdown */
    .navbar .dropdown-toggle::after {
      display: none;
      /* Ẩn mũi tên mặc định của Bootstrap */
    }

    .navbar .dropdown-menu {
      border-radius: var(--radius);
      box-shadow: var(--shadow-light);
      background-color: var(--card-bg-light);
      border: none;
    }

    .navbar .dropdown-item {
      color: var(--text-light);
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .navbar .dropdown-item:hover {
      background-color: var(--link-hover-light);
      color: var(--primary);
      transform: translateX(3px);
    }

    .navbar .dropdown-item i {
      font-size: 1.1rem;
    }

    .navbar .dropdown-item.text-danger:hover {
      color: #dc3545 !important;
    }

    body.dark .navbar .dropdown-item:hover {
      background-color: var(--link-hover-dark);
      color: var(--text-dark);
      /* giữ màu trắng khi dark mode */
    }


    /* Sidebar */
    .sidebar {
      position: fixed;
      top: var(--navbar-h);
      left: 0;
      width: var(--sidebar-w);
      height: calc(100vh - var(--navbar-h));
      background: var(--card-bg-light);
      backdrop-filter: blur(18px);
      /* Stronger blur */
      box-shadow: 6px 0 20px rgba(0, 0, 0, 0.1);
      transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      /* Smooth animation */
      overflow-y: auto;
      padding: 25px 15px;
      /* More padding */
      z-index: 1000;
    }

    body.dark .sidebar {
      background: var(--card-bg-dark);
      box-shadow: 6px 0 20px rgba(0, 0, 0, 0.4);
    }

    /* Scrollbar styling */
    .sidebar::-webkit-scrollbar {
      width: 8px;
    }

    .sidebar::-webkit-scrollbar-track {
      background: var(--bg-light);
    }

    body.dark .sidebar::-webkit-scrollbar-track {
      background: var(--bg-dark);
    }

    .sidebar::-webkit-scrollbar-thumb {
      background-color: var(--accent);
      border-radius: 10px;
      border: 2px solid var(--bg-light);
    }

    body.dark .sidebar::-webkit-scrollbar-thumb {
      border: 2px solid var(--bg-dark);
    }

    /* Sidebar Links & Category */
    .sidebar .nav-category {
      font-size: 0.8rem;
      /* 13px */
      font-weight: 700;
      color: #90a4ae;
      /* A bit darker grey */
      padding: 15px 15px 8px 15px;
      /* Increased padding */
      text-transform: uppercase;
      letter-spacing: 1px;
      /* More letter spacing */
    }

    .sidebar .nav-divider {
      border: 0;
      border-top: 1px solid rgba(0, 0, 0, 0.15);
      /* Darker divider */
      margin: 15px 0;
      /* More spacing */
      opacity: 0.6;
    }

    body.dark .nav-divider {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar .nav-link {
      display: flex;
      align-items: center;
      gap: 15px;
      /* More spacing between icon and text */
      padding: 14px 18px;
      /* Generous padding */
      border-radius: var(--radius);
      text-decoration: none;
      color: var(--text-light);
      font-weight: 500;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      /* Smooth hover transition */
    }

    .sidebar .nav-link i {
      font-size: 1.5rem;
      /* Larger icons */
      color: var(--primary);
      width: 30px;
      /* Cố định độ rộng icon */
      text-align: center;
    }

    .sidebar .nav-link:hover {
      background: var(--link-hover-light);
      color: var(--primary);
      transform: translateX(5px);
      /* Stronger slide effect */
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      /* Subtle shadow on hover */
    }

    body.dark .sidebar .nav-link:hover {
      background: var(--link-hover-dark);
      color: var(--text-dark);
    }

    .sidebar .nav-link:hover i {
      color: var(--primary);
    }

    body.dark .sidebar .nav-link:hover i {
      color: var(--text-dark);
    }


    .sidebar .nav-link.active {
      background: var(--secondary-gradient);
      /* Rực rỡ hơn */
      color: white;
      box-shadow: 0 8px 20px rgba(168, 85, 247, 0.5);
      /* Nổi bật hơn */
      font-weight: 600;
      transform: translateX(0px);
      /* Đảm bảo không bị dịch chuyển */
    }

    .sidebar .nav-link.active i {
      color: white;
    }

    /* Toggle sidebar for mobile */
    #toggleSidebar {
      cursor: pointer;
      font-size: 28px;
      /* Larger toggle icon */
      transition: transform 0.3s ease;
    }

    #toggleSidebar.hidden {
      opacity: 0;
      /* Ẩn button khi sidebar đang mở */
      pointer-events: none;
    }

    @media(max-width:992px) {
      .sidebar {
        left: calc(-1 * var(--sidebar-w));
      }

      .sidebar.show {
        left: 0;
        box-shadow: 8px 0 25px rgba(0, 0, 0, 0.2);
      }

      .content-area {
        margin-left: 0 !important;
      }
    }

    /* Content Area */
    .content-area {
      margin-left: var(--sidebar-w);
      margin-top: var(--navbar-h);
      padding: 35px;
      /* More padding */
      transition: margin 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      min-height: calc(100vh - var(--navbar-h));
    }

    /* Card styles */
    .card {
      background: var(--card-bg-light);
      border: none;
      border-radius: var(--radius);
      box-shadow: var(--shadow-light);
      padding: 30px;
      /* More padding inside card */
      backdrop-filter: blur(15px);
      transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      overflow: hidden;
      /* Dùng để border-radius hoạt động tốt với các elements con */
    }

    .card:hover {
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      /* More prominent shadow on hover */
      transform: translateY(-5px) scale(1.005);
      /* Slight lift and scale */
    }

    body.dark .card:hover {
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
    }

    .card-header {
      border-bottom: none;
      background-color: transparent !important;
      padding-bottom: 15px;
      margin-bottom: 15px;
      position: relative;
    }

    .card-header::after {
      /* Decorative line */
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background: var(--accent);
      border-radius: 5px;
    }

    body.dark .card-header::after {
      background: var(--primary);
    }

    /* Table styles */
    .table-responsive {
      border-radius: var(--radius);
      /* Rounded corners for table container */
      overflow: hidden;
      box-shadow: var(--shadow-light);
      /* Shadow for table */
    }

    .table-responsive:hover {
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    thead th {
      background: linear-gradient(90deg, var(--primary), var(--accent));
      color: white;
      padding: 15px 12px;
      /* More padding */
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 0.5px;
      font-weight: 600;
    }

    tbody tr {
      background: var(--card-bg-light);
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      /* Subtle row separator */
      transition: all 0.3s ease;
    }

    body.dark tbody tr {
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    tbody tr:last-child {
      border-bottom: none;
    }

    tbody tr:hover {
      transform: scale(1.005);
      /* Slight scale on hover */
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      /* Light shadow on hover */
      background: var(--link-hover-light) !important;
    }

    body.dark tbody tr:hover {
      background: var(--link-hover-dark) !important;
    }

    td {
      padding: 14px 12px;
      /* More padding for cells */
      vertical-align: middle;
      background-color: transparent !important;
      color: var(--text-light);
    }

    body.dark td {
      color: var(--text-dark);
    }

    .badge {
      padding: 0.6em 0.8em;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.75rem;
      text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    }

    /* Footer */
    .footer {
      text-align: center;
      margin-top: 50px;
      padding: 20px;
      font-size: 14px;
      color: #718096;
      /* Darker grey */
      opacity: 0.8;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    body.dark .footer {
      border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* Dark mode button */
    .toggle-dark {
      cursor: pointer;
      background: rgba(255, 255, 255, 0.2);
      border: none;
      border-radius: 50%;
      width: 40px;
      /* Larger button */
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.3rem;
      /* Larger icon */
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .toggle-dark:hover {
      background: rgba(255, 255, 255, 0.4);
      transform: rotate(15deg) scale(1.05);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Modal */
    .modal {
      z-index: 2000;
    }

    .modal-content {
      border-radius: var(--radius);
      box-shadow: var(--shadow-light);
      background: var(--card-bg-light);
      backdrop-filter: blur(10px);
      border: none;
    }

    body.dark .modal-content {
      background: var(--card-bg-dark);
    }

    .modal-header {
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
      color: var(--text-light);
    }

    body.dark .modal-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-dark);
    }

    .modal-footer {
      border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    body.dark .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }


    /* Toast Notification */
    #toastContainer {
      position: fixed;
      top: 90px;
      /* Đặt thấp hơn navbar một chút */
      right: 25px;
      z-index: 2100;
      display: flex;
      flex-direction: column;
      gap: 10px;
      /* Khoảng cách giữa các toast */
    }

    .toast {
      opacity: 0;
      /* Bắt đầu ẩn */
      transform: translateX(100%);
      /* Bắt đầu từ bên phải */
      transition: opacity 0.4s ease-out, transform 0.4s ease-out;
      border-radius: var(--radius);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
      padding: 12px 15px;
      font-weight: 500;
    }

    .toast.show {
      opacity: 1;
      transform: translateX(0);
    }

    .toast-body i {
      font-size: 1.2rem;
    }
  </style>
</head>

<body>

  <div id="preloader" class="hidden">
    <div class="spinner-grow text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <div id="toastContainer"></div>

  <nav class="navbar">
    <div class="brand">
      <i id="toggleSidebar" class='bx bx-menu'></i>
      <img src="assets/images/bansach.png" alt="logo">
      <div>
        <div class="title">ADMIN DASHBOARD</div>
        <div class="subtitle">Hệ thống quản lý sách thông minh</div>
      </div>
    </div>
    <div class="actions">
      <form class="d-none d-lg-block" style="position:relative;">
        <input type="search" class="form-control form-control-sm" placeholder="Tìm kiếm nhanh...">
        <i class='bx bx-search position-absolute top-50 end-0 translate-middle-y me-3'></i>
      </form>
      <button class="toggle-dark" id="darkModeToggle"><i class='bx bx-moon'></i></button>
      <div class="dropdown">
        <a href="#" data-bs-toggle="dropdown" class="text-white text-decoration-none d-flex align-items-center">
          <i class='bx bx-user-circle fs-2'></i> <span class="ms-2 fw-semibold d-none d-md-inline">Binh Admin</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li>
            <h6 class="dropdown-header">Chào mừng, Binh!</h6>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item" href="#"><i class='bx bx-user-pin me-2'></i>Hồ sơ của tôi</a></li>
          <li><a class="dropdown-item" href="#"><i class='bx bx-cog me-2'></i>Cài đặt hệ thống</a></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class='bx bx-log-out me-2'></i>Đăng xuất</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <nav class="sidebar" id="sidebar">
    <ul class="nav flex-column">

      <div class="nav-category">Tổng quan</div>
      <li>
        <a href="index.php?page_layout=dashboard" class="nav-link <?= isActive('dashboard', $page_layout) ?>">
          <i class='bx bx-tachometer'></i> <span>Dashboard</span>
        </a>
      </li>

      <hr class="nav-divider">

      <div class="nav-category">Quản lý Sản phẩm</div>
      <li>
        <a href="index.php?page_layout=danhsachsach" class="nav-link <?= isActive('danhsachsach', $page_layout) ?>">
          <i class='bx bx-book'></i><span>Quản Lý Sách</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=danhsachdanhmuc" class="nav-link <?= isActive('danhsachdanhmuc', $page_layout) ?>">
          <i class='bx bx-category'></i><span>Quản Lý Danh Mục</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=danhsachtacgia" class="nav-link <?= isActive('danhsachtacgia', $page_layout) ?>">
          <i class='bx bx-user-voice'></i><span>Quản Lý Tác giả</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=danhsachkhuyenmai" class="nav-link <?= isActive('danhsachkhuyenmai', $page_layout) ?>">
          <i class='bx bx-gift'></i><span>Quản lý Khuyến mãi</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=danhsachnhapkho" class="nav-link <?= isActive('danhsachnhapkho', $page_layout) ?>">
          <i class='bx bx-box'></i><span>Quản Lý Nhập kho</span>
        </a>
      </li>

      <hr class="nav-divider">

      <div class="nav-category">Quản lý Giao dịch</div>
      <li>
        <a href="index.php?page_layout=danhsachdonhang" class="nav-link <?= isActive('danhsachdonhang', $page_layout) ?>">
          <i class='bx bx-receipt'></i><span>Quản lý Đơn hàng</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=danhsachgiaohang" class="nav-link <?= isActive('danhsachgiaohang', $page_layout) ?>">
          <i class='bx bx-package'></i><span>Quản lý Vận chuyển</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=danhsachthanhtoan" class="nav-link <?= isActive('danhsachthanhtoan', $page_layout) ?>">
          <i class='bx bx-credit-card'></i><span>Quản lý Thanh toán</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=danhsachdanhgia" class="nav-link <?= isActive('danhsachdanhgia', $page_layout) ?>">
          <i class='bx bx-star'></i><span>Quản Lý Đánh giá</span>
        </a>
      </li>

      <hr class="nav-divider">

      <div class="nav-category">Hệ thống & Báo cáo</div>
      <li>
        <a href="index.php?page_layout=danhsachnguoidung" class="nav-link <?= isActive('danhsachnguoidung', $page_layout) ?>">
          <i class='bx bx-group'></i><span>Quản Lý Người dùng</span>
        </a>
      </li>
      <li>
        <a href="index.php?page_layout=thongke" class="nav-link <?= isActive('thongke', $page_layout) ?>">
          <i class='bx bx-bar-chart-alt-2'></i><span>Thống kê & Báo cáo</span>
        </a>
      </li>
    </ul>
  </nav>

  <div class="content-area">
    <div class="container-fluid">
        
        <div class="card">
        <?php
        // Lấy biến page_layout từ URL, nếu không có thì mặc định là 'dashboard' (hoặc 'content.php' theo logic cũ)
        $page_layout = $_GET['page_layout'] ?? 'content'; 

        if (isset($_GET["page_layout"])) {
            switch ($_GET["page_layout"]) {
                
                // --- DASHBOARD ---
                case 'dashboard':
                    require_once 'dashboard.php';
                    break;
                    
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
                case "danhsachdanhmuc":
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

                // --- QUẢN LÝ ĐƠN HÀNG ---
                case "danhsachdonhang":
                    require_once 'danhsachdonhang.php';
                    break;
                case "capnhat_donhang":
                    require_once 'capnhat_donhang.php';
                    break;
                case "chitietdonhang":
                    require_once 'chitietdonhang.php';
                    break;
                case "sua_donhang":
                    require_once 'sua_donhang.php';
                    break;
                case "xoa_donhang":
                    require_once 'xoa_donhang.php';
                    break;

                // --- QUẢN LÝ GIAO HÀNG ---
                case 'danhsachgiaohang':
                    require_once 'giaohang.php';
                    break;
                case 'chitiet_donhang':
                    require_once 'chitiet_donhang.php';
                    break;

                // --- QUẢN LÝ THANH TOÁN ---
                case "danhsachthanhtoan":
                    require_once 'danhsachthanhtoan.php';
                    break;
                case "capnhat_thanhtoan":
                    require_once 'capnhat_thanhtoan.php';
                    break;

                // --- QUẢN LÝ NHẬP KHO ---
                case 'danhsachnhapkho':
                    require_once 'phieunhap.php';
                    break;
                case 'them_phieunhap':
                    require_once 'them_phieunhap.php';
                    break;
                case 'chitiet_phieunhap':
                    require_once 'chitiet_phieunhap.php';
                    break;

                // --- THỐNG KÊ & BÁO CÁO ---
                case "thongke":
                    require_once 'thongke.php';
                    break;

                // KHUYẾN MÃI
                case 'danhsachkhuyenmai':
                    require_once 'khuyenmai.php';
                    break;
                case 'them_khuyenmai':
                    require_once 'them_khuyenmai.php';
                    break;
                case 'sua_khuyenmai':
                    require_once 'sua_khuyenmai.php';
                    break;
                case 'xoa_khuyenmai':
                    require_once 'xoa_khuyenmai.php';
                    break;

                // --- ĐÁNH GIÁ ---
                case "danhsachdanhgia":
                    require_once 'danhsachdanhgia.php';
                    break;
                case "duyet_danhgia":
                    require_once 'duyet_danhgia.php';
                    break;
                case "xoa_danhgia":
                    require_once 'xoa_danhgia.php';
                    break;

                // --- MẶC ĐỊNH ---
                default:
                    require_once 'dashboard.php';
                    break;
            }
        } else {
            // Nếu không có tham số page_layout, hiển thị trang mặc định
            require_once 'dashboard.php'; 
        }
        ?>
        </div> </div> <div class="footer">
        </div>
</div>

    <div class="footer">
      &copy; 2025 Cửa Hàng Sách. Thiết kế bởi Binh - Phiên bản Pro.
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      const sidebar = $('#sidebar');
      const toggleBtn = $('#toggleSidebar');
      const darkModeToggle = $('#darkModeToggle');
      const body = $('body');
      const preloader = $('#preloader');

      // --- A. Ẩn Preloader khi trang tải xong ---
      /*
      $(window).on('load', function() {
          setTimeout(function() {
              preloader.addClass('hidden');
          }, 400); // Ẩn sau 400ms để hiệu ứng mượt mà
      });
      */


      // --- B. Toggle Sidebar (Cho Mobile) ---
      function checkSidebarStatus() {
        if ($(window).width() <= 992) {
          if (sidebar.hasClass('show')) {
            toggleBtn.html("<i class='bx bx-x'></i>").removeClass('bx-menu').addClass('bx-x');
          } else {
            toggleBtn.html("<i class='bx bx-menu'></i>").removeClass('bx-x').addClass('bx-menu');
          }
        }
      }

      toggleBtn.on('click', function() {
        sidebar.toggleClass('show');
        checkSidebarStatus();
      });

      // Đóng sidebar khi click vào link trên mobile
      $('.sidebar .nav-link').on('click', function() {
        if ($(window).width() <= 992) {
          sidebar.removeClass('show');
          checkSidebarStatus();
        }
      });

      // --- C. Dark Mode Toggle ---
      // Lấy trạng thái từ localStorage
      if (localStorage.getItem('darkMode') === 'enabled') {
        body.addClass('dark');
        darkModeToggle.html("<i class='bx bx-sun'></i>");
      } else {
        darkModeToggle.html("<i class='bx bx-moon'></i>");
      }

      // Xử lý sự kiện click
      darkModeToggle.on('click', function() {
        if (body.hasClass('dark')) {
          body.removeClass('dark');
          localStorage.setItem('darkMode', 'disabled');
          darkModeToggle.html("<i class='bx bx-moon'></i>");
        } else {
          body.addClass('dark');
          localStorage.setItem('darkMode', 'enabled');
          darkModeToggle.html("<i class='bx bx-sun'></i>");
        }
      });

      // --- D. Hàm Show Toast (Nhiều hiệu ứng hơn) ---
      window.showToast = function(message, type = 'info') {
        let icon;
        let bgColorClass;
        let bgStyle;

        switch (type) {
          case 'success':
            icon = 'bx-check-circle';
            bgColorClass = 'bg-success';
            bgStyle = 'linear-gradient(45deg, #48bb78, #38a169)';
            break;
          case 'danger':
            icon = 'bx-error-circle';
            bgColorClass = 'bg-danger';
            bgStyle = 'linear-gradient(45deg, #f56565, #e53e3e)';
            break;
          case 'warning':
            icon = 'bx-error-alt';
            bgColorClass = 'bg-warning text-dark';
            bgStyle = 'linear-gradient(45deg, #f6e05e, #ecc94b)';
            break;
          case 'info':
          default:
            icon = 'bx-info-circle';
            bgColorClass = 'bg-info';
            bgStyle = 'linear-gradient(45deg, #4299e1, #3182ce)';
            break;
        }

        const toastId = 'toast-' + Date.now();
        const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background: ${bgStyle};">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center fw-semibold">
                                <i class='bx ${icon} me-2 fs-5'></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

        $('#toastContainer').append(toastHtml);
        const toastEl = document.getElementById(toastId);

        // Hiển thị toast bằng JS để kích hoạt transition
        setTimeout(() => {
          toastEl.classList.add('show');
        }, 50);

        const newToast = new bootstrap.Toast(toastEl, {
          delay: 4500 // Tăng thời gian hiển thị
        });
        newToast.show();

        // Xóa toast khỏi DOM sau khi ẩn
        toastEl.addEventListener('hidden.bs.toast', function() {
          this.remove();
        });
      };
    });
  </script>
</body>

</html>

<?php
ob_end_flush();
?>