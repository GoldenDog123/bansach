<?php
session_start();
require_once('ketnoi.php');
header('Content-Type: application/json');

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu action']);
    exit;
}

$action = $_POST['action'];

// Áp dụng coupon
if ($action === 'apply') {
    $macoupon = strtoupper(trim($_POST['macoupon']));
    $tongtien = floatval($_POST['tongtien']);
    
    if (empty($macoupon)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã coupon']);
        exit;
    }
    
    // Kiểm tra coupon
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT * FROM coupon 
            WHERE macoupon = '$macoupon' 
            AND soluong > 0
            AND (ngaybatdau IS NULL OR ngaybatdau <= '$now')
            AND (ngayketthuc IS NULL OR ngayketthuc >= '$now')";
    
    $result = mysqli_query($ketnoi, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $coupon = mysqli_fetch_assoc($result);
        
        // Tính giá trị giảm
        if ($coupon['loaigiam'] == 'percent') {
            $giam = $tongtien * ($coupon['giatri'] / 100);
        } else {
            $giam = $coupon['giatri'];
        }
        
        // Đảm bảo không giảm quá tổng tiền
        if ($giam > $tongtien) {
            $giam = $tongtien;
        }
        
        $tongtien_sau = $tongtien - $giam;
        
        // Lưu vào session
        $_SESSION['applied_coupon'] = $coupon;
        
        echo json_encode([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'coupon' => [
                'macoupon' => $coupon['macoupon'],
                'giatri' => $coupon['giatri'],
                'loaigiam' => $coupon['loaigiam'],
                'giam' => $giam,
                'tongtien_goc' => $tongtien,
                'tongtien_sau' => $tongtien_sau
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn']);
    }
}

// Xóa coupon
elseif ($action === 'remove') {
    unset($_SESSION['applied_coupon']);
    echo json_encode(['success' => true, 'message' => 'Đã xóa mã giảm giá']);
}

// Sử dụng coupon (giảm số lượng sau khi thanh toán thành công)
elseif ($action === 'use') {
    if (isset($_SESSION['applied_coupon'])) {
        $coupon = $_SESSION['applied_coupon'];
        $idcoupon = $coupon['idcoupon'];
        
        // Giảm số lượng
        $sql = "UPDATE coupon SET soluong = soluong - 1 WHERE idcoupon = $idcoupon AND soluong > 0";
        
        if (mysqli_query($ketnoi, $sql)) {
            unset($_SESSION['applied_coupon']);
            echo json_encode(['success' => true, 'message' => 'Đã sử dụng mã giảm giá']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi sử dụng mã giảm giá']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Không có mã giảm giá nào được áp dụng']);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}
?>
