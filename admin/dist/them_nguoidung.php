<?php
require_once('ketnoi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Lấy dữ liệu người dùng (hoten, sdt, vaitro)
    $hoten = mysqli_real_escape_string($ketnoi, $_POST['hoten'] ?? '');
    $email = mysqli_real_escape_string($ketnoi, $_POST['email'] ?? '');
    $matkhau = $_POST['matkhau'] ?? ''; 
    $sdt = mysqli_real_escape_string($ketnoi, $_POST['sdt'] ?? '');
    $vaitro = mysqli_real_escape_string($ketnoi, $_POST['vaitro'] ?? 'hoc_sinh');

    // 2. Lấy dữ liệu địa chỉ đầu tiên
    $diachi = mysqli_real_escape_string($ketnoi, $_POST['diachi'] ?? '');
    $hoten_dc = mysqli_real_escape_string($ketnoi, $_POST['hoten_dc'] ?? $hoten);
    $sdt_dc = mysqli_real_escape_string($ketnoi, $_POST['sdt_dc'] ?? $sdt);
    
    if (empty($hoten) || empty($email) || empty($matkhau)) {
        echo "<script>showToast('Vui lòng nhập đầy đủ Họ tên, Email và Mật khẩu!', 'warning');</script>";
    } else {
        $sql_check = "SELECT idnguoidung FROM nguoidung WHERE email = '$email'";
        $query_check = mysqli_query($ketnoi, $sql_check);
        
        if (mysqli_num_rows($query_check) > 0) {
            echo "<script>showToast('Email đã được sử dụng!', 'danger');</script>";
        } else {
            $matkhau_hash = password_hash($matkhau, PASSWORD_DEFAULT);
            
            // Bắt đầu Transaction
            mysqli_begin_transaction($ketnoi); 
            try {
                // A. Insert vào bảng nguoidung
                $sql_insert_nd = "INSERT INTO nguoidung (hoten, email, matkhau, sdt, vaitro) 
                                 VALUES ('$hoten', '$email', '$matkhau_hash', '$sdt', '$vaitro')";
                if (!mysqli_query($ketnoi, $sql_insert_nd)) {
                    throw new Exception("Lỗi khi thêm người dùng cơ bản.");
                }
                
                $idnguoidung = mysqli_insert_id($ketnoi);
                
                // B. Insert địa chỉ đầu tiên (nếu có thông tin)
                if (!empty($diachi)) {
                    $sql_insert_dc = "INSERT INTO diachi (idnguoidung, hoten, sdt, diachi) 
                                      VALUES ($idnguoidung, '$hoten_dc', '$sdt_dc', '$diachi')";
                    if (!mysqli_query($ketnoi, $sql_insert_dc)) {
                        throw new Exception("Lỗi khi thêm địa chỉ.");
                    }
                }
                
                // Commit Transaction
                mysqli_commit($ketnoi);
                echo "<script>showToast('Thêm người dùng thành công!', 'success');</script>";
                header('Location: index.php?page_layout=danhsachnguoidung');
                exit();
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                mysqli_rollback($ketnoi);
                echo "<script>showToast('Lỗi giao dịch: " . $e->getMessage() . " (" . mysqli_error($ketnoi) . ")', 'danger');</script>";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2 class="mb-4 text-primary"><i class='bx bx-plus-circle'></i> Thêm Người dùng Mới</h2>
        <form method="POST">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">Thông tin tài khoản</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hoten" class="form-label fw-bold">Họ Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hoten" name="hoten" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="matkhau" class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="matkhau" name="matkhau" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sdt" class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" class="form-control" id="sdt" name="sdt">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vaitro" class="form-label fw-bold">Vai trò</label>
                        <select class="form-select" id="vaitro" name="vaitro" required>
                            <option value="hoc_sinh" selected>Học sinh/Người dùng (hoc_sinh)</option>
                            <option value="giao_vien">Giáo viên (giao_vien)</option>
                            <option value="admin">Quản trị viên (admin)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white fw-bold">Địa chỉ (Địa chỉ mặc định)</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hoten_dc" class="form-label">Họ Tên người nhận</label>
                            <input type="text" class="form-control" id="hoten_dc" name="hoten_dc" placeholder="Mặc định là Họ Tên ở trên">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sdt_dc" class="form-label">Số điện thoại người nhận</label>
                            <input type="text" class="form-control" id="sdt_dc" name="sdt_dc" placeholder="Mặc định là SĐT ở trên">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="diachi" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="diachi" name="diachi" rows="2" placeholder="Số nhà, tên đường, phường/xã, tỉnh/thành phố"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page_layout=danhsachnguoidung" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Thêm Người dùng</button>
            </div>
        </form>
    </div>
</div>