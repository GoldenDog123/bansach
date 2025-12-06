<?php
require_once('ketnoi.php');

if (!isset($_GET['action'])) {
    header('Location: index.php?page_layout=danhsachdanhgia');
    exit;
}

$action = $_GET['action'];

// Xử lý duyệt bình luận (từ bảng binh_luan)
if (isset($_GET['id_binh_luan'])) {
    $id_binh_luan = intval($_GET['id_binh_luan']);
    
    if ($action === 'duyet') {
        $sql = "UPDATE binh_luan SET trang_thai = 'approved' WHERE id_binh_luan = $id_binh_luan";
        $message = "Đã duyệt đánh giá thành công!";
    } elseif ($action === 'huy') {
        $sql = "UPDATE binh_luan SET trang_thai = 'pending' WHERE id_binh_luan = $id_binh_luan";
        $message = "Đã bỏ duyệt đánh giá!";
    } else {
        header('Location: index.php?page_layout=danhsachdanhgia');
        exit;
    }
}
// Xử lý duyệt đánh giá (từ bảng danh_gia - nếu cần)
elseif (isset($_GET['id'])) {
    $id_danh_gia = intval($_GET['id']);
    
    if ($action === 'duyet') {
        $sql = "UPDATE danh_gia SET trang_thai = 'Duyệt' WHERE id_danh_gia = $id_danh_gia";
        $message = "Đã duyệt đánh giá thành công!";
    } elseif ($action === 'huy') {
        $sql = "UPDATE danh_gia SET trang_thai = 'Chờ duyệt' WHERE id_danh_gia = $id_danh_gia";
        $message = "Đã bỏ duyệt đánh giá!";
    } else {
        header('Location: index.php?page_layout=danhsachdanhgia');
        exit;
    }
} else {
    header('Location: index.php?page_layout=danhsachdanhgia');
    exit;
}

if (mysqli_query($ketnoi, $sql)) {
    echo "<script>alert('$message'); window.location.href='index.php?page_layout=danhsachdanhgia';</script>";
} else {
    echo "<script>alert('Lỗi: " . mysqli_error($ketnoi) . "'); window.location.href='index.php?page_layout=danhsachdanhgia';</script>";
}
?>
