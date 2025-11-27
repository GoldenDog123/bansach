<?php
require_once('ketnoi.php');

$idloaisach = $_GET['id'] ?? 0;
if (!is_numeric($idloaisach) || $idloaisach <= 0) {
    header('Location: index.php?page_layout=danhsachdanhmuc');
    exit();
}

// Lấy thông tin danh mục hiện tại
$sql_hien_tai = "SELECT tenloaisach FROM loaisach WHERE idloaisach = $idloaisach";
$query_hien_tai = mysqli_query($ketnoi, $sql_hien_tai);
$loaisach_hien_tai = mysqli_fetch_assoc($query_hien_tai);

if (!$loaisach_hien_tai) {
    echo "<script>showToast('Không tìm thấy Danh mục!', 'danger');</script>";
    header('Location: index.php?page_layout=danhsachdanhmuc');
    exit();
}
$ten_hien_tai = $loaisach_hien_tai['tenloaisach'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenloaisach_moi = mysqli_real_escape_string($ketnoi, $_POST['tenloaisach'] ?? '');

    if (empty($tenloaisach_moi)) {
        echo "<script>showToast('Tên danh mục không được để trống!', 'warning');</script>";
    } else {
        // Cập nhật
        $sql_update = "UPDATE loaisach SET tenloaisach = '$tenloaisach_moi' WHERE idloaisach = $idloaisach";
        
        if (mysqli_query($ketnoi, $sql_update)) {
            echo "<script>showToast('Cập nhật danh mục thành công!', 'success');</script>";
            header('Location: index.php?page_layout=danhsachdanhmuc');
            exit();
        } else {
            echo "<script>showToast('Lỗi khi cập nhật danh mục: " . mysqli_error($ketnoi) . "', 'danger');</script>";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="mb-4 text-info"><i class='bx bx-edit'></i> Sửa Danh mục: <?= htmlspecialchars($ten_hien_tai); ?></h2>
        <div class="card shadow">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-4">
                        <label for="tenloaisach" class="form-label fw-bold">Tên Danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tenloaisach" name="tenloaisach" required 
                                value="<?= htmlspecialchars($ten_hien_tai); ?>">
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php?page_layout=danhsachdanhmuc" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                        <button type="submit" class="btn btn-info text-white"><i class='bx bx-save'></i> Lưu Thay Đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>