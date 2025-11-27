<?php
require_once('ketnoi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenloaisach = mysqli_real_escape_string($ketnoi, $_POST['tenloaisach'] ?? '');

    if (empty($tenloaisach)) {
        echo "<script>showToast('Tên danh mục không được để trống!', 'warning');</script>";
    } else {
        // Kiểm tra trùng tên (Tùy chọn)
        $sql_check = "SELECT * FROM loaisach WHERE tenloaisach = '$tenloaisach'";
        $query_check = mysqli_query($ketnoi, $sql_check);
        
        if (mysqli_num_rows($query_check) > 0) {
            echo "<script>showToast('Danh mục này đã tồn tại!', 'danger');</script>";
        } else {
            $sql_insert = "INSERT INTO loaisach (tenloaisach) VALUES ('$tenloaisach')";
            
            if (mysqli_query($ketnoi, $sql_insert)) {
                echo "<script>showToast('Thêm danh mục thành công!', 'success');</script>";
                header('Location: index.php?page_layout=danhsachdanhmuc');
                exit();
            } else {
                echo "<script>showToast('Lỗi khi thêm danh mục: " . mysqli_error($ketnoi) . "', 'danger');</script>";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="mb-4 text-warning"><i class='bx bx-plus-circle'></i> Thêm Danh mục Sách Mới</h2>
        <div class="card shadow">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-4">
                        <label for="tenloaisach" class="form-label fw-bold">Tên Danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tenloaisach" name="tenloaisach" required 
                                placeholder="Ví dụ: Tiểu thuyết, Khoa học, Kinh tế,...">
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php?page_layout=danhsachdanhmuc" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Quay lại</a>
                        <button type="submit" class="btn btn-warning text-white"><i class='bx bx-save'></i> Thêm Danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>