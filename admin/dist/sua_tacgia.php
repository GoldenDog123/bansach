<?php
require_once('ketnoi.php');

$idtacgia = $_GET['id'] ?? 0;
if (!is_numeric($idtacgia) || $idtacgia <= 0) {
    header('Location: index.php?page_layout=danhsachtacgia');
    exit();
}

// 1. Lấy thông tin tác giả hiện tại
$sql_hien_tai = "SELECT * FROM tacgia WHERE idtacgia = $idtacgia";
$query_hien_tai = mysqli_query($ketnoi, $sql_hien_tai);
$tacgia_hien_tai = mysqli_fetch_assoc($query_hien_tai);

if (!$tacgia_hien_tai) {
    echo "<script>showToast('Không tìm thấy Tác giả!', 'danger');</script>";
    header('Location: index.php?page_layout=danhsachtacgia');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tentacgia = mysqli_real_escape_string($ketnoi, $_POST['tentacgia'] ?? '');
    $ghichu = mysqli_real_escape_string($ketnoi, $_POST['ghichu'] ?? ''); // ĐÃ SỬA: mota -> ghichu

    if (empty($tentacgia)) {
        echo "<script>showToast('Vui lòng nhập Tên Tác giả!', 'warning');</script>";
    } else {
        // Kiểm tra tên tác giả đã tồn tại với tác giả khác chưa
        $sql_check = "SELECT idtacgia FROM tacgia WHERE tentacgia = '$tentacgia' AND idtacgia != $idtacgia";
        if (mysqli_num_rows(mysqli_query($ketnoi, $sql_check)) > 0) {
            echo "<script>showToast('Tên tác giả đã tồn tại với một tác giả khác!', 'danger');</script>";
        } else {
            $sql_update = "UPDATE tacgia SET 
                           tentacgia = '$tentacgia', 
                           ghichu = '$ghichu' /* ĐÃ SỬA: mota -> ghichu */
                           WHERE idtacgia = $idtacgia";
            
            if (mysqli_query($ketnoi, $sql_update)) {
                echo "<script>showToast('Cập nhật Tác giả thành công!', 'success');</script>";
                header('Location: index.php?page_layout=danhsachtacgia');
                exit();
            } else {
                echo "<script>showToast('Lỗi khi cập nhật tác giả: " . mysqli_error($ketnoi) . "', 'danger');</script>";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="mb-4 text-info"><i class='bx bx-edit'></i> Sửa Tác giả: <?= htmlspecialchars($tacgia_hien_tai['tentacgia']); ?></h2>
        <form method="POST">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white fw-bold">Thông tin Tác giả</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="tentacgia" class="form-label fw-bold">Tên Tác giả <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tentacgia" name="tentacgia" 
                               value="<?= htmlspecialchars($tacgia_hien_tai['tentacgia']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ghichu" class="form-label fw-bold">Ghi chú/Mô tả</label>
                        <textarea class="form-control" id="ghichu" name="ghichu" rows="5" placeholder="Nhập ghi chú hoặc mô tả ngắn về tác giả"><?= htmlspecialchars($tacgia_hien_tai['ghichu']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page_layout=danhsachtacgia" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                <button type="submit" class="btn btn-info text-white"><i class='bx bx-save'></i> Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>