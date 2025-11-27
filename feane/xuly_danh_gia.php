<?php
session_start();
include('ketnoi.php');
header('Content-Type: application/json');

// Kiểm tra người dùng đã đăng nhập chưa (tùy chọn - có thể cho anonymous)
// Nếu muốn yêu cầu đăng nhập, hãy uncomment dòng dưới:
// if (!isset($_SESSION['idnguoidung'])) {
//   echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để đánh giá', 'requireLogin' => true]);
//   exit;
// }

if (!isset($_POST['idsach']) || !isset($_POST['diem'])) {
  echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
  exit;
}

$idsach = intval($_POST['idsach']);
$diem = intval($_POST['diem']);

// Kiểm tra diem hợp lệ
if ($diem < 1 || $diem > 5) {
  echo json_encode(['success' => false, 'message' => 'Mức đánh giá không hợp lệ']);
  exit;
}

// Nếu đã login thì lưu với id, nếu chưa thì dùng 0 (anonymous)
$id_khach_hang = isset($_SESSION['idnguoidung']) ? intval($_SESSION['idnguoidung']) : 0;

// Kiểm tra xem user đã đánh giá chưa - nếu có thì update, không thì insert
$sql_check = "SELECT id_danh_gia FROM danh_gia WHERE idsach = $idsach AND id_khach_hang = $id_khach_hang";
$result_check = mysqli_query($ketnoi, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
  // Update
  $sql = "UPDATE danh_gia SET diem_danh_gia = $diem WHERE idsach = $idsach AND id_khach_hang = $id_khach_hang";
  $message = "Cập nhật đánh giá thành công";
} else {
  // Insert
  $sql = "INSERT INTO danh_gia (idsach, id_khach_hang, diem_danh_gia) VALUES ($idsach, $id_khach_hang, $diem)";
  $message = "Cảm ơn bạn đã đánh giá!";
}

if (mysqli_query($ketnoi, $sql)) {
  echo json_encode(['success' => true, 'message' => $message]);
} else {
  echo json_encode(['success' => false, 'message' => 'Lỗi: ' . mysqli_error($ketnoi)]);
}
?>
