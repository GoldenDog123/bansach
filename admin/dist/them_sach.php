<?php
// Đảm bảo biến $ketnoi được khởi tạo
require_once('ketnoi.php');

$error = []; // Mảng chứa lỗi

// Logic PHP xử lý form
if (isset($_POST['sbm'])) {
    
    // 1. Lấy và làm sạch dữ liệu đầu vào
    $tensach = mysqli_real_escape_string($ketnoi, $_POST['tensach']);
    $idloaisach = $_POST['idloaisach'];
    $idtacgia = $_POST['idtacgia'];
    $dongia = $_POST['dongia'];
    $soluong = $_POST['soluong'];
    $mota = mysqli_real_escape_string($ketnoi, $_POST['mota']);
    $ngaynhap = date('Y-m-d H:i:s'); 

    // 2. Xử lý Ảnh Bìa
    $hinhanhsach = 'default_book.jpg'; // Tên mặc định nếu không có ảnh
    
    // >>> ĐÃ SỬA: Đường dẫn tương đối TỪ admin/dist/ đến feane/images/ <<<
    // (Thoát 2 cấp từ admin/dist/ ra bansach/, sau đó vào feane/images/)
    $target_dir = "../../feane/images/"; 

    if (isset($_FILES['hinhanhsach']) && $_FILES['hinhanhsach']['error'] == 0) {
        $file_name_original = $_FILES['hinhanhsach']['name'];
        $file_tmp = $_FILES['hinhanhsach']['tmp_name'];
        $file_extension = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));

        // Tạo tên file mới: time() + tên gốc + extension
        $file_new_name = time() . '_' . basename($file_name_original, '.' . $file_extension) . '.' . $file_extension;
        $target_file = $target_dir . $file_new_name;

        // Kiểm tra loại file
        if (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $error['hinhanhsach'] = "Chỉ cho phép file ảnh (jpg, jpeg, png, gif, webp).";
        }

        // Kiểm tra kích thước file (<= 5MB)
        if ($_FILES['hinhanhsach']['size'] > 5000000) {
            $error['hinhanhsach'] = "Kích thước file quá lớn (tối đa 5MB).";
        }
        
        // Tiến hành upload nếu không có lỗi
        if (empty($error)) {
            // Dòng 45: move_uploaded_file() sẽ sử dụng đường dẫn đã sửa
            if (move_uploaded_file($file_tmp, $target_file)) {
                $hinhanhsach = $file_new_name; // Lưu tên file đã đổi vào CSDL
            } else {
                // Đây là nơi lỗi Failed to open stream có thể xuất hiện nếu thư mục không tồn tại
                $error['hinhanhsach'] = "Lỗi khi upload file. Kiểm tra lại thư mục đích: " . $target_dir;
            }
        }
    }

    // 3. Thực thi SQL INSERT nếu không có lỗi
    if (empty($error)) {
        $sql_insert = "INSERT INTO sach (tensach, idloaisach, idtacgia, dongia, soluong, hinhanhsach, mota, ngaynhap) 
                       VALUES ('$tensach', '$idloaisach', '$idtacgia', '$dongia', '$soluong', '$hinhanhsach', '$mota', '$ngaynhap')";

        if (mysqli_query($ketnoi, $sql_insert)) {
            // Hiển thị thông báo và chuyển hướng về trang danh sách
            echo "<script>alert('✅ Thêm sách mới thành công!'); window.location.href='index.php?page_layout=danhsachsach';</script>";
            exit();
        } else {
            $error['db'] = "❌ Lỗi: Không thể thêm sách vào CSDL. " . mysqli_error($ketnoi);
        }
    }
}

// Lấy danh sách Loại Sách và Tác giả để đổ vào Select Box
$loaisach_result = mysqli_query($ketnoi, "SELECT * FROM loaisach ORDER BY tenloaisach ASC");
$tacgia_result = mysqli_query($ketnoi, "SELECT * FROM tacgia ORDER BY tentacgia ASC");
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white"
             style="background: linear-gradient(90deg, var(--primary), var(--accent)); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-plus-circle'></i> THÊM SÁCH MỚI</h4>
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
                                   value="<?= htmlspecialchars($_POST['tensach'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mô Tả Chi Tiết</label>
                            <textarea name="mota" class="form-control" rows="5" placeholder="Nhập mô tả về cuốn sách..."><?= htmlspecialchars($_POST['mota'] ?? '') ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Thể Loại (*)</label>
                                <select name="idloaisach" class="form-select" required>
                                    <option value="">-- Chọn Thể Loại --</option>
                                    <?php while($ls = mysqli_fetch_assoc($loaisach_result)): ?>
                                        <option value="<?= $ls['idloaisach'] ?>" 
                                            <?= (($_POST['idloaisach'] ?? '') == $ls['idloaisach']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ls['tenloaisach']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tác Giả (*)</label>
                                <select name="idtacgia" class="form-select" required>
                                    <option value="">-- Chọn Tác Giả --</option>
                                    <?php while($tg = mysqli_fetch_assoc($tacgia_result)): ?>
                                        <option value="<?= $tg['idtacgia'] ?>"
                                            <?= (($_POST['idtacgia'] ?? '') == $tg['idtacgia']) ? 'selected' : '' ?>>
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
                                   value="<?= htmlspecialchars($_POST['dongia'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Số Lượng Tồn Kho (*)</label>
                            <input type="number" name="soluong" class="form-control" required min="0" placeholder="Số lượng nhập kho"
                                   value="<?= htmlspecialchars($_POST['soluong'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ảnh Bìa Sách (*)</label>
                            <input type="file" name="hinhanhsach" class="form-control" accept="image/*" required>
                            <?php if (isset($error['hinhanhsach'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($error['hinhanhsach']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
                
                <div class="d-flex justify-content-end pt-3 border-top mt-4">
                    <a href="index.php?page_layout=danhsachsach" class="btn btn-secondary me-2 rounded-pill px-4">
                        <i class='bx bx-arrow-back'></i> Quay lại
                    </a>
                    <button type="submit" name="sbm" class="btn btn-primary rounded-pill px-4">
                        <i class='bx bx-save'></i> Lưu Sách
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>