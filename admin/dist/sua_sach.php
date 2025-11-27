<?php
// Đảm bảo biến $ketnoi được khởi tạo
require_once('ketnoi.php');

$error = [];
$idsach = $_GET['id'] ?? null; // Lấy ID sách từ URL

// ----------------------------------------------------
// PHẦN 1: LẤY DỮ LIỆU SÁCH CẦN SỬA
// ----------------------------------------------------
if (!$idsach || !is_numeric($idsach)) {
    // Nếu không có ID hoặc ID không hợp lệ, chuyển hướng
    echo "<script>alert('❌ ID sách không hợp lệ!'); window.location.href='index.php?page_layout=danhsachsach';</script>";
    exit();
}

$sql_sach_cu = "SELECT * FROM sach WHERE idsach = $idsach";
$query_sach_cu = mysqli_query($ketnoi, $sql_sach_cu);

if (mysqli_num_rows($query_sach_cu) == 0) {
    echo "<script>alert('❌ Không tìm thấy sách!'); window.location.href='index.php?page_layout=danhsachsach';</script>";
    exit();
}

$sach_cu = mysqli_fetch_assoc($query_sach_cu);

// Lấy danh sách Loại Sách và Tác giả để đổ vào Select Box
$loaisach_result = mysqli_query($ketnoi, "SELECT * FROM loaisach ORDER BY tenloaisach ASC");
$tacgia_result = mysqli_query($ketnoi, "SELECT * FROM tacgia ORDER BY tentacgia ASC");


// ----------------------------------------------------
// PHẦN 2: XỬ LÝ CẬP NHẬT (KHI SUBMIT FORM)
// ----------------------------------------------------
if (isset($_POST['sbm'])) {
    // 2.1. Lấy và làm sạch dữ liệu
    $tensach_moi = mysqli_real_escape_string($ketnoi, $_POST['tensach']);
    $idloaisach_moi = $_POST['idloaisach'];
    $idtacgia_moi = $_POST['idtacgia'];
    $dongia_moi = $_POST['dongia'];
    $soluong_moi = $_POST['soluong'];
    $mota_moi = mysqli_real_escape_string($ketnoi, $_POST['mota']);
    
    // Giữ lại tên ảnh cũ nếu không có ảnh mới được chọn
    $hinhanhsach_moi = $sach_cu['hinhanhsach']; 
    
    $target_dir = "../../feane/images/"; // Đường dẫn upload ảnh (Đã sửa lỗi)

    // 2.2. Xử lý Upload Ảnh MỚI
    if (isset($_FILES['hinhanhsach']) && $_FILES['hinhanhsach']['error'] == 0 && $_FILES['hinhanhsach']['size'] > 0) {
        
        $file_name_original = $_FILES['hinhanhsach']['name'];
        $file_tmp = $_FILES['hinhanhsach']['tmp_name'];
        $file_extension = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));

        // Tạo tên file mới
        $file_new_name = time() . '_' . basename($file_name_original, '.' . $file_extension) . '.' . $file_extension;
        $target_file = $target_dir . $file_new_name;

        // Kiểm tra loại file và kích thước (Tương tự them_sach)
        if (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $error['hinhanhsach'] = "Chỉ cho phép file ảnh (jpg, jpeg, png, gif, webp).";
        }
        if ($_FILES['hinhanhsach']['size'] > 5000000) {
            $error['hinhanhsach'] = "Kích thước file quá lớn (tối đa 5MB).";
        }
        
        if (empty($error)) {
            // Xóa ảnh cũ (nếu nó không phải là ảnh mặc định)
            if ($sach_cu['hinhanhsach'] != 'default_book.jpg' && file_exists($target_dir . $sach_cu['hinhanhsach'])) {
                 unlink($target_dir . $sach_cu['hinhanhsach']);
            }
            
            // Di chuyển file mới vào thư mục
            if (move_uploaded_file($file_tmp, $target_file)) {
                $hinhanhsach_moi = $file_new_name;
            } else {
                $error['hinhanhsach'] = "Lỗi khi upload file mới.";
            }
        }
    }
    
    // 2.3. Thực thi SQL UPDATE
    if (empty($error)) {
        $sql_update = "UPDATE sach SET 
                        tensach = '$tensach_moi', 
                        idloaisach = '$idloaisach_moi', 
                        idtacgia = '$idtacgia_moi', 
                        dongia = '$dongia_moi', 
                        soluong = '$soluong_moi', 
                        mota = '$mota_moi', 
                        hinhanhsach = '$hinhanhsach_moi' 
                       WHERE idsach = $idsach";

        if (mysqli_query($ketnoi, $sql_update)) {
            echo "<script>alert('✅ Cập nhật sách thành công!'); window.location.href='index.php?page_layout=danhsachsach';</script>";
            exit();
        } else {
            $error['db'] = "❌ Lỗi: Không thể cập nhật sách. " . mysqli_error($ketnoi);
        }
    }
}
// ----------------------------------------------------
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white"
             style="background: linear-gradient(90deg, var(--primary), var(--accent)); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-edit-alt'></i> CHỈNH SỬA SÁCH: <?= htmlspecialchars($sach_cu['tensach']); ?></h4>
        </div>

        <div class="card-body bg-white p-4">
            <?php if (!empty($error) && isset($error['db'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error['db']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" action="">
                <div class="row g-4">
                    
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên Sách (*)</label>
                            <input type="text" name="tensach" class="form-control" required placeholder="Nhập tên sách..." 
                                   value="<?= htmlspecialchars($_POST['tensach'] ?? $sach_cu['tensach']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mô Tả Chi Tiết</label>
                            <textarea name="mota" class="form-control" rows="5" placeholder="Nhập mô tả về cuốn sách..."><?= htmlspecialchars($_POST['mota'] ?? $sach_cu['mota']) ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Thể Loại (*)</label>
                                <select name="idloaisach" class="form-select" required>
                                    <option value="">-- Chọn Thể Loại --</option>
                                    <?php 
                                    $selected_ls = $_POST['idloaisach'] ?? $sach_cu['idloaisach'];
                                    mysqli_data_seek($loaisach_result, 0); // Đặt lại con trỏ
                                    while($ls = mysqli_fetch_assoc($loaisach_result)): ?>
                                        <option value="<?= $ls['idloaisach'] ?>" 
                                            <?= ($selected_ls == $ls['idloaisach']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ls['tenloaisach']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tác Giả (*)</label>
                                <select name="idtacgia" class="form-select" required>
                                    <option value="">-- Chọn Tác Giả --</option>
                                    <?php 
                                    $selected_tg = $_POST['idtacgia'] ?? $sach_cu['idtacgia'];
                                    mysqli_data_seek($tacgia_result, 0); // Đặt lại con trỏ
                                    while($tg = mysqli_fetch_assoc($tacgia_result)): ?>
                                        <option value="<?= $tg['idtacgia'] ?>"
                                            <?= ($selected_tg == $tg['idtacgia']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tg['tentacgia']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Giá Bán (VNĐ) (*)</label>
                            <input type="number" name="dongia" class="form-control" required min="1000" placeholder="Đơn giá"
                                   value="<?= htmlspecialchars($_POST['dongia'] ?? $sach_cu['dongia']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Số Lượng Tồn Kho (*)</label>
                            <input type="number" name="soluong" class="form-control" required min="0" placeholder="Số lượng nhập kho"
                                   value="<?= htmlspecialchars($_POST['soluong'] ?? $sach_cu['soluong']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ảnh Bìa Hiện Tại</label>
                            <div class="mb-2">
                                <img src="../../feane/images/<?= htmlspecialchars($sach_cu['hinhanhsach']); ?>" 
                                     alt="Ảnh bìa hiện tại" 
                                     style="width: 100px; height: 130px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                            </div>
                            
                            <label class="form-label fw-semibold">Chọn Ảnh Mới (Bỏ qua nếu không thay đổi)</label>
                            <input type="file" name="hinhanhsach" class="form-control" accept="image/*">
                            <?php if (isset($error['hinhanhsach'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($error['hinhanhsach']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
                
                <div class="d-flex justify-content-end pt-3 border-top mt-4">
                    <a href="index.php?page_layout=danhsachsach" class="btn btn-secondary me-2 rounded-pill px-4">
                        <i class='bx bx-arrow-back'></i> Hủy
                    </a>
                    <button type="submit" name="sbm" class="btn btn-primary rounded-pill px-4">
                        <i class='bx bx-check-circle'></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>