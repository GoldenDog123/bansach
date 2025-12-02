<?php
// Payment status update handler - v3 clean rebuild
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once('ketnoi.php');
header('Content-Type: application/json; charset=utf-8');

try {
  if (!isset($_SESSION['idnguoidung'])) {
    throw new Exception('Không có session');
  }

  $idnguoidung = intval($_SESSION['idnguoidung']);

  // Get user role
  $sql = "SELECT vaitro FROM nguoidung WHERE idnguoidung = " . $idnguoidung;
  $res = mysqli_query($ketnoi, $sql);
  if (!$res || mysqli_num_rows($res) == 0) {
    throw new Exception('User không tồn tại');
  }
  $user = mysqli_fetch_assoc($res);
  $is_admin = ($user['vaitro'] === 'admin' || $user['vaitro'] === 'thuthu');

  // Check input
  if (!isset($_POST['iddonhang']) || !isset($_POST['trangthai'])) {
    throw new Exception('Thiếu iddonhang hoặc trangthai');
  }

  $iddonhang = intval($_POST['iddonhang']);
  $trangthai = trim($_POST['trangthai']);
  $ghichu = isset($_POST['ghichu']) ? trim($_POST['ghichu']) : '';

  // Validate status
  $valid = ['cho_duyet', 'dang_giao', 'hoan_thanh', 'da_huy'];
  if (!in_array($trangthai, $valid)) {
    throw new Exception('Trạng thái không hợp lệ');
  }

  // Get order
  $sql = "SELECT idnguoidung, trangthai FROM donhang WHERE iddonhang = " . $iddonhang;
  $res = mysqli_query($ketnoi, $sql);
  if (!$res || mysqli_num_rows($res) == 0) {
    throw new Exception('Đơn hàng không tồn tại');
  }
  $order = mysqli_fetch_assoc($res);
  $old_status = $order['trangthai'];

  // Check permission
  if (!$is_admin && $order['idnguoidung'] != $idnguoidung) {
    throw new Exception('Không có quyền');
  }

  // Update order status
  $sql = "UPDATE donhang SET trangthai = '" . mysqli_real_escape_string($ketnoi, $trangthai) . "' WHERE iddonhang = " . $iddonhang;
  if (!mysqli_query($ketnoi, $sql)) {
    throw new Exception('Cập nhật thất bại: ' . mysqli_error($ketnoi));
  }

  // Save history if changed
  if ($old_status !== $trangthai) {
    $now = date('Y-m-d H:i:s');
    if (empty($ghichu)) {
      $ghichu = 'Cập nhật từ ' . $old_status . ' sang ' . $trangthai;
    }
    $ghichu = mysqli_real_escape_string($ketnoi, $ghichu);
    
    $sql = "INSERT INTO lichsu_donhang (iddonhang, ghichu, ngaycapnhat, trangthai) 
            VALUES (" . $iddonhang . ", '" . $ghichu . "', '" . $now . "', '" . $trangthai . "')";
    
    if (!mysqli_query($ketnoi, $sql)) {
      throw new Exception('Lỗi lưu lịch sử: ' . mysqli_error($ketnoi));
    }
  }

  echo json_encode(['success' => true, 'message' => 'OK', 'trangthai' => $trangthai]);

} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
