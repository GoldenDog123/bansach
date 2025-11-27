<?php
require_once('ketnoi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tentacgia = mysqli_real_escape_string($ketnoi, $_POST['tentacgia'] ?? '');
    $ghichu = mysqli_real_escape_string($ketnoi, $_POST['ghichu'] ?? ''); // ĐÃ SỬA: mota -> ghichu

    if (empty($tentacgia)) {
        echo "<script>showToast('Vui lòng nhập Tên Tác giả!', 'warning');</script>";
    } else {
        // Kiểm tra tác giả đã tồn tại chưa
        $sql_check = "SELECT idtacgia FROM tacgia WHERE tentacgia = '$tentacgia'";
        if (mysqli_num_rows(mysqli_query($ketnoi, $sql_check)) > 0) {
            echo "<script>showToast('Tên tác giả này đã tồn tại!', 'danger');</script>";
        } else {
            $sql_insert = "INSERT INTO tacgia (tentacgia, ghichu) /* ĐÃ SỬA: mota -> ghichu */
                           VALUES ('$tentacgia', '$ghichu')";
            
            if (mysqli_query($ketnoi, $sql_insert)) {
                echo "<script>showToast('Thêm Tác giả thành công!', 'success');</script>";
                header('Location: index.php?page_layout=danhsachtacgia');
                exit();
            } else {
                echo "<script>showToast('Lỗi khi thêm tác giả: " . mysqli_error($ketnoi) . "', 'danger');</script>";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="mb-4 text-primary"><i class='bx bx-plus-circle'></i> Thêm Tác giả Mới</h2>
        <form method="POST">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">Thông tin Tác giả</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="tentacgia" class="form-label fw-bold">Tên Tác giả <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tentacgia" name="tentacgia" required placeholder="VD: Nguyễn Nhật Ánh">
                    </div>
                    <div class="mb-3">
                        <label for="ghichu" class="form-label fw-bold">Ghi chú/Mô tả</label>
                        <textarea class="form-control" id="ghichu" name="ghichu" rows="5" placeholder="Nhập ghi chú hoặc mô tả ngắn về tác giả"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page_layout=danhsachtacgia" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Thêm Tác giả</button>
            </div>
        </form>
    </div>
</div>