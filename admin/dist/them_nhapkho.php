<?php
require_once 'ketnoi.php';

// Lấy danh sách sách
$sach_sql = "SELECT idsach, tensach FROM sach ORDER BY tensach";
$sach_q = mysqli_query($ketnoi, $sach_sql);

// Xử lý thêm nhập kho
if (isset($_POST['them'])) {
    $idsach = $_POST['idsach'];
    $soluong = $_POST['soluong'];
    $nhacungcap = $_POST['nhacungcap']; // Lấy tên NCC từ form

    if (!is_numeric($soluong) || $soluong <= 0) {
        echo "<script>alert('Số lượng phải là số nguyên dương.');</script>";
    } else {
        // BƯỚC 1: TẠO PHIẾU NHẬP MỚI VÀ LẤY ID
        $sql_phieu = "INSERT INTO phieunhap (nhacungcap, ngaynhap) VALUES ('$nhacungcap', NOW())";
        
        if (mysqli_query($ketnoi, $sql_phieu)) {
            $idphieunhap_moi = mysqli_insert_id($ketnoi); // Lấy ID của phiếu nhập vừa tạo

            // BƯỚC 2: THÊM VÀO BẢNG NHAP KHO (Cần cột idphieunhap)
            // Cột soluong_nhap đã được xác định ở lần trước
            $sql_nhap = "INSERT INTO nhapkho (idphieunhap, idsach, soluong_nhap, ngaynhap) 
                         VALUES ($idphieunhap_moi, $idsach, $soluong, NOW())";
            
            if (mysqli_query($ketnoi, $sql_nhap)) {
                // BƯỚC 3: CẬP NHẬT SỐ LƯỢNG SÁCH
                $sql_update = "UPDATE sach SET soluong = soluong + $soluong WHERE idsach = $idsach";
                mysqli_query($ketnoi, $sql_update);

                echo "<script>
                    alert('Nhập kho thành công! (Phiếu nhập ID: $idphieunhap_moi)');
                    window.location='index.php?page_layout=danhsachnhapkho';
                    </script>";
            } else {
                // Xử lý lỗi nếu thêm vào nhapkho thất bại (Cần xóa phieunhap nếu có lỗi)
                echo "<script>alert('Lỗi khi thêm chi tiết nhập kho: " . mysqli_error($ketnoi) . "');</script>";
                // Thêm code để xóa phieunhap vừa tạo nếu muốn giữ tính toàn vẹn dữ liệu
            }
        } else {
            echo "<script>alert('Lỗi khi tạo phiếu nhập: " . mysqli_error($ketnoi) . "');</script>";
        }
    }
}
?>

<h2 class="mb-4">📥 Nhập sách vào kho</h2>

<form method="POST">
    <div class="mb-3">
        <label class="form-label">Chọn sách</label>
        <select name="idsach" class="form-control" required>
            <option value="">-- Chọn sách --</option>
            <?php 
            // Đặt lại con trỏ kết quả về đầu để hiển thị trong form
            mysqli_data_seek($sach_q, 0); 
            while ($row = mysqli_fetch_assoc($sach_q)) : ?>
                <option value="<?= $row['idsach'] ?>"><?= $row['tensach'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Số lượng nhập</label>
        <input type="number" name="soluong" class="form-control" required min="1">
    </div>

    <div class="mb-3">
        <label class="form-label">Tên Nhà Cung Cấp</label>
        <input type="text" name="nhacungcap" class="form-control" required>
    </div>

    <button name="them" class="btn btn-primary">Nhập kho</button>
    <a href="index.php?page_layout=danhsachnhapkho" class="btn btn-secondary">Quay lại</a>
</form>