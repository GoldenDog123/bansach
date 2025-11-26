<?php
require_once('ketnoi.php');
session_start();

// ---------- CSRF ----------
if (!isset($_GET['csrf']) || $_GET['csrf'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('CSRF token không hợp lệ!'); window.location='index.php?page_layout=danhsachdonhang';</script>";
    exit;
}

// ---------- Lấy ID đơn ----------
if (!isset($_GET['iddonhang']) || !is_numeric($_GET['iddonhang'])) {
    echo "<script>alert('ID đơn không hợp lệ!'); window.location='index.php?page_layout=danhsachdonhang';</script>";
    exit;
}
$iddonhang = (int)$_GET['iddonhang'];

// ---------- Kiểm tra trạng thái hiện tại ----------
$stmt = $ketnoi->prepare("SELECT trangthai FROM donhang WHERE iddonhang=?");
$stmt->bind_param("i", $iddonhang);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "<script>alert('Đơn hàng không tồn tại!'); window.location='index.php?page_layout=danhsachdonhang';</script>";
    exit;
}

if ($row['trangthai'] !== 'cho_duyet') {
    $msg = htmlspecialchars($row['trangthai']);
    echo "<script>alert('Không thể duyệt đơn này. Trạng thái hiện tại: $msg'); window.location='index.php?page_layout=danhsachdonhang';</script>";
    exit;
}

// ---------- Cập nhật trạng thái ----------
$stmt = $ketnoi->prepare("UPDATE donhang SET trangthai='dang_giao' WHERE iddonhang=?");
$stmt->bind_param("i", $iddonhang);
$stmt->execute();
$stmt->close();
$ketnoi->close();

// ---------- Redirect về danh sách ----------
echo "<script>window.location='index.php?page_layout=danhsachdonhang';</script>";
exit;
?>
