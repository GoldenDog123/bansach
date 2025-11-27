<?php
require_once('ketnoi.php');

$idnguoidung = $_GET['id'] ?? 0;
if (!is_numeric($idnguoidung) || $idnguoidung <= 0) {
    header('Location: index.php?page_layout=danhsachnguoidung');
    exit();
}

// 1. Lấy thông tin người dùng hiện tại
$sql_hien_tai = "SELECT hoten, email, sdt, vaitro FROM nguoidung WHERE idnguoidung = $idnguoidung";
$query_hien_tai = mysqli_query($ketnoi, $sql_hien_tai);
$nguoidung_hien_tai = mysqli_fetch_assoc($query_hien_tai);

if (!$nguoidung_hien_tai) {
    echo "<script>showToast('Không tìm thấy Người dùng!', 'danger');</script>";
    header('Location: index.php?page_layout=danhsachnguoidung');
    exit();
}

// 2. Lấy địa chỉ đầu tiên (để hiển thị và sửa)
$sql_diachi = "SELECT * FROM diachi WHERE idnguoidung = $idnguoidung ORDER BY iddiachi ASC LIMIT 1";
$query_diachi = mysqli_query($ketnoi, $sql_diachi);
$diachi_hien_tai = mysqli_fetch_assoc($query_diachi);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoten = mysqli_real_escape_string($ketnoi, $_POST['hoten'] ?? $nguoidung_hien_tai['hoten']);
    $email = mysqli_real_escape_string($ketnoi, $_POST['email'] ?? $nguoidung_hien_tai['email']);
    $matkhau_moi = $_POST['matkhau_moi'] ?? '';
    $sdt = mysqli_real_escape_string($ketnoi, $_POST['sdt'] ?? $nguoidung_hien_tai['sdt']);
    $vaitro = mysqli_real_escape_string($ketnoi, $_POST['vaitro'] ?? $nguoidung_hien_tai['vaitro']);
    
    // Địa chỉ (nếu có)
    $diachi_chitiet = mysqli_real_escape_string($ketnoi, $_POST['diachi_chitiet'] ?? '');
    $tinh_thanhpho = mysqli_real_escape_string($ketnoi, $_POST['tinh_thanhpho'] ?? '');
    
    mysqli_begin_transaction($ketnoi); 
    try {
        // A. Cập nhật bảng nguoidung
        $sql_update_nd = "UPDATE nguoidung SET 
                        hoten = '$hoten', 
                        email = '$email', 
                        sdt = '$sdt', 
                        vaitro = '$vaitro'";
        
        // Thêm mật khẩu nếu có
        if (!empty($matkhau_moi)) {
            $matkhau_hash = password_hash($matkhau_moi, PASSWORD_DEFAULT);
            $sql_update_nd .= ", matkhau = '$matkhau_hash'";
        }
        $sql_update_nd .= " WHERE idnguoidung = $idnguoidung";
        
        if (!mysqli_query($ketnoi, $sql_update_nd)) {
            throw new Exception("Lỗi khi cập nhật người dùng cơ bản.");
        }
        
        // B. Cập nhật hoặc Thêm địa chỉ đầu tiên
        if ($diachi_hien_tai) {
            // Cập nhật địa chỉ đã có
            $iddiachi = $diachi_hien_tai['iddiachi'];
            $sql_update_dc = "UPDATE diachi SET 
                            diachi_chitiet = '$diachi_chitiet',
                            tinh_thanhpho = '$tinh_thanhpho'
                            WHERE iddiachi = $iddiachi";
            if (!mysqli_query($ketnoi, $sql_update_dc)) {
                throw new Exception("Lỗi khi cập nhật địa chỉ.");
            }
        } elseif (!empty($diachi_chitiet) && !empty($tinh_thanhpho)) {
            // Trường hợp người dùng chưa có địa chỉ, thêm mới
            $sql_insert_dc = "INSERT INTO diachi (idnguoidung, hoten_nguoinhan, sdt_nguoinhan, diachi_chitiet, tinh_thanhpho, loaidiachi) 
                                VALUES ($idnguoidung, '$hoten', '$sdt', '$diachi_chitiet', '$tinh_thanhpho', 'mặc định')";
            if (!mysqli_query($ketnoi, $sql_insert_dc)) {
                throw new Exception("Lỗi khi thêm địa chỉ mới.");
            }
        }

        mysqli_commit($ketnoi);
        echo "<script>showToast('Cập nhật người dùng thành công!', 'success');</script>";
        header('Location: index.php?page_layout=danhsachnguoidung');
        exit();
    } catch (Exception $e) {
        mysqli_rollback($ketnoi);
        echo "<script>showToast('Lỗi giao dịch: " . $e->getMessage() . "', 'danger');</script>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2 class="mb-4 text-info"><i class='bx bx-edit'></i> Sửa Người dùng: <?= htmlspecialchars($nguoidung_hien_tai['hoten']); ?></h2>
        <form method="POST">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">Thông tin tài khoản</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hoten" class="form-label fw-bold">Họ Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hoten" name="hoten" 
                                   value="<?= htmlspecialchars($nguoidung_hien_tai['hoten']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($nguoidung_hien_tai['email']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="matkhau_moi" class="form-label fw-bold">Mật khẩu mới (Để trống nếu không đổi)</label>
                            <input type="password" class="form-control" id="matkhau_moi" name="matkhau_moi" placeholder="Nhập mật khẩu mới">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sdt" class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" class="form-control" id="sdt" name="sdt"
                                   value="<?= htmlspecialchars($nguoidung_hien_tai['sdt']); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vaitro" class="form-label fw-bold">Vai trò</label>
                        <select class="form-select" id="vaitro" name="vaitro" required>
                            <option value="hoc_sinh" <?= ($nguoidung_hien_tai['vaitro'] == 'hoc_sinh' ? 'selected' : '') ?>>Học sinh/Người dùng (hoc_sinh)</option>
                            <option value="giao_vien" <?= ($nguoidung_hien_tai['vaitro'] == 'giao_vien' ? 'selected' : '') ?>>Giáo viên (giao_vien)</option>
                            <option value="admin" <?= ($nguoidung_hien_tai['vaitro'] == 'admin' ? 'selected' : '') ?>>Quản trị viên (admin)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white fw-bold">Địa chỉ đầu tiên</div>
                <div class="card-body">
                    <p class="text-muted fst-italic">Đây là địa chỉ đầu tiên được tìm thấy, bạn có thể chỉnh sửa nó.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tinh_thanhpho" class="form-label">Tỉnh/Thành phố</label>
                            <input type="text" class="form-control" id="tinh_thanhpho" name="tinh_thanhpho"
                                   value="<?= htmlspecialchars($diachi_hien_tai['tinh_thanhpho'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="diachi_chitiet" class="form-label">Địa chỉ chi tiết</label>
                        <textarea class="form-control" id="diachi_chitiet" name="diachi_chitiet" rows="2" placeholder="Số nhà, tên đường, phường/xã"><?= htmlspecialchars($diachi_hien_tai['diachi_chitiet'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page_layout=danhsachnguoidung" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                <button type="submit" class="btn btn-info text-white"><i class='bx bx-save'></i> Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>