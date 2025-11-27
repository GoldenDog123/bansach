<?php
session_start();
include('ketnoi.php');
header('Content-Type: application/json');

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['idnguoidung'])) {
  echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để bình luận', 'requireLogin' => true]);
  exit;
}

if (!isset($_POST['idsach']) || !isset($_POST['noi_dung'])) {
  echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
  exit;
}

$idsach = intval($_POST['idsach']);
$noi_dung = mysqli_real_escape_string($ketnoi, $_POST['noi_dung']);
$idnguoidung = intval($_SESSION['idnguoidung']);

// Kiểm tra input
if (strlen($noi_dung) < 5) {
  echo json_encode(['success' => false, 'message' => 'Nội dung bình luận quá ngắn (tối thiểu 5 ký tự)']);
  exit;
}

if (strlen($noi_dung) > 1000) {
  echo json_encode(['success' => false, 'message' => 'Nội dung bình luận quá dài (tối đa 1000 ký tự)']);
  exit;
}

// Lấy thông tin người dùng
$sql_customer = "SELECT hoten, email FROM nguoidung WHERE idnguoidung = $idnguoidung";
$result_customer = mysqli_query($ketnoi, $sql_customer);
$customer = mysqli_fetch_assoc($result_customer);

if (!$customer) {
  echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng']);
  exit;
}

$ho_ten = $customer['hoten'];
$email = $customer['email'];

// Insert bình luận
$sql = "INSERT INTO binh_luan (idsach, id_khach_hang, ho_ten, email, noi_dung, trang_thai) 
        VALUES ($idsach, $idnguoidung, '$ho_ten', '$email', '$noi_dung', 'approved')";

if (mysqli_query($ketnoi, $sql)) {
  // Lấy bình luận vừa tạo để trả về
  $last_id = mysqli_insert_id($ketnoi);
  $sql_new = "SELECT id_binh_luan, ho_ten, noi_dung, ngay_binh_luan FROM binh_luan WHERE id_binh_luan = $last_id";
  $result_new = mysqli_query($ketnoi, $sql_new);
  $new_comment = mysqli_fetch_assoc($result_new);
  
  echo json_encode([
    'success' => true, 
    'message' => 'Cảm ơn bạn đã bình luận!',
    'comment' => $new_comment
  ]);
} else {
  echo json_encode(['success' => false, 'message' => 'Lỗi: ' . mysqli_error($ketnoi)]);
}
?>
