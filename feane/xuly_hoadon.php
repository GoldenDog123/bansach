<?php
session_start();
require_once('ketnoi.php');
header('Content-Type: application/json');

if (!isset($_POST['iddonhang'])) {
  echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
  exit;
}

$iddonhang = intval($_POST['iddonhang']);

// Lấy thông tin đơn hàng
$sql_don = "SELECT d.iddonhang, d.idnguoidung, d.ngaydat, d.tongtien, n.hoten, n.email 
            FROM donhang d 
            LEFT JOIN nguoidung n ON d.idnguoidung = n.idnguoidung 
            WHERE d.iddonhang = $iddonhang";
$result_don = mysqli_query($ketnoi, $sql_don);
$don = mysqli_fetch_assoc($result_don);

if (!$don) {
  echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
  exit;
}

// Lấy thời gian thực hiện (hiện tại)
$current_datetime = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
$ngaydat_formatted = $current_datetime->format('d/m/Y');
$thoigian_formatted = $current_datetime->format('H:i:s');

// Lấy chi tiết đơn hàng
$sql_ct = "SELECT dc.soluong, s.tensach, s.dongia, tg.tentacgia 
           FROM donhang_chitiet dc
           LEFT JOIN sach s ON dc.idsach = s.idsach
           LEFT JOIN tacgia tg ON s.idtacgia = tg.idtacgia
           WHERE dc.iddonhang = $iddonhang";
$result_ct = mysqli_query($ketnoi, $sql_ct);
$items = [];
$subtotal = 0;

while ($row = mysqli_fetch_assoc($result_ct)) {
  $items[] = $row;
  $subtotal += $row['dongia'] * $row['soluong'];
}

// Tính toán tổng tiền (VAT 10%)
$vat = $subtotal * 0.1;
$total = $subtotal + $vat;

// Cập nhật tongtien vào database nếu chưa có
if (empty($don['tongtien'])) {
  mysqli_query($ketnoi, "UPDATE donhang SET tongtien = $total WHERE iddonhang = $iddonhang");
  $don['tongtien'] = $total;
}

// Tạo dữ liệu QR Code - thông tin hóa đơn
$qr_data = "HoaDon:" . $iddonhang . "|" . 
           "KhachHang:" . $don['hoten'] . "|" . 
           "Email:" . $don['email'] . "|" . 
           "NgayDat:" . $ngaydat_formatted . "|" . 
           "TongTien:" . number_format($total, 0, '', '') . "VND";

// Sử dụng QR Code API từ Google Charts
$qr_code_url = "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($qr_data);

echo json_encode([
  'success' => true,
  'invoice' => [
    'iddonhang' => $don['iddonhang'],
    'hoten' => $don['hoten'],
    'email' => $don['email'],
    'ngaydat' => $don['ngaydat'],
    'ngaydat_formatted' => $ngaydat_formatted,
    'thoigian_formatted' => $thoigian_formatted,
    'items' => $items,
    'total_items' => count($items),
    'subtotal' => $subtotal,
    'vat' => $vat,
    'total' => $total,
    'qr_code_url' => $qr_code_url,
    'branch_info' => [
      'name' => 'BAN SÁCH - Chi nhánh Trung tâm',
      'address' => '60 QL1A, xã Thường Tín, TP. Hà Nội',
      'phone' => '1800 6770',
      'email' => 'contact@ctech.edu.vn'
    ]
  ]
]);
?>
