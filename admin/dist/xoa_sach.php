<?php
// Đảm bảo biến $ketnoi được khởi tạo
require_once('ketnoi.php');

$idsach = $_GET['id'] ?? null; // Lấy ID sách từ URL

if (!$idsach || !is_numeric($idsach)) {
    // Nếu không có ID hoặc ID không hợp lệ, chuyển hướng
    echo "<script>alert('❌ ID sách không hợp lệ!'); window.location.href='index.php?page_layout=danhsachsach';</script>";
    exit();
}

// ----------------------------------------------------
// BƯỚC 1: LẤY TÊN FILE ẢNH CŨ TRƯỚC KHI XÓA
// ----------------------------------------------------
$sql_select_image = "SELECT hinhanhsach FROM sach WHERE idsach = $idsach";
$query_image = mysqli_query($ketnoi, $sql_select_image);

if (mysqli_num_rows($query_image) == 0) {
    echo "<script>alert('❌ Không tìm thấy sách để xóa!'); window.location.href='index.php?page_layout=danhsachsach';</script>";
    exit();
}

$row_image = mysqli_fetch_assoc($query_image);
$ten_anh_cu = $row_image['hinhanhsach'];
$target_dir = "../../feane/images/"; // Đường dẫn tới thư mục ảnh (Đã xác nhận)

// ----------------------------------------------------
// BƯỚC 2: XÓA BẢN GHI KHỎI CƠ SỞ DỮ LIỆU
// ----------------------------------------------------
$sql_delete = "DELETE FROM sach WHERE idsach = $idsach";

if (mysqli_query($ketnoi, $sql_delete)) {
    
    // ----------------------------------------------------
    // BƯỚC 3: XÓA FILE ẢNH VẬT LÝ TRÊN SERVER (nếu tồn tại)
    // ----------------------------------------------------
    if ($ten_anh_cu != 'default_book.jpg') {
        $file_path = $target_dir . $ten_anh_cu;
        
        // Kiểm tra file có tồn tại và xóa nó
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                // Xóa thành công
                // echo "Đã xóa file ảnh: " . $ten_anh_cu;
            } else {
                // Lỗi không xóa được file
                // Ghi log hoặc cảnh báo nhưng vẫn tiếp tục vì CSDL đã được xóa
            }
        }
    }

    // Thông báo thành công và chuyển hướng
    echo "<script>alert('✅ Xóa sách thành công!'); window.location.href='index.php?page_layout=danhsachsach';</script>";
    exit();

} else {
    // Lỗi khi xóa trong CSDL
    echo "<script>alert('❌ Lỗi khi xóa sách khỏi CSDL: " . mysqli_error($ketnoi) . "'); window.location.href='index.php?page_layout=danhsachsach';</script>";
    exit();
}
?>