<?php
require_once('ketnoi.php');

$sql = "SELECT idloaisach, tenloaisach FROM loaisach ORDER BY idloaisach DESC";
$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #f97316, #fb923c); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-category'></i> Quản lý Danh mục Sách</h4>
            <a href="index.php?page_layout=them_loaisach" class="btn btn-light btn-sm fw-bold shadow-sm">
                <i class='bx bx-plus-circle'></i> Thêm Danh mục Mới
            </a>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #ffedd5;">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th class="text-start">Tên Danh mục</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['idloaisach']); ?></td>
                                <td class="text-start fw-semibold"><?= htmlspecialchars($row['tenloaisach']); ?></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="index.php?page_layout=sua_loaisach&id=<?= $row['idloaisach'] ?>" 
                                           class="btn btn-info btn-sm text-white"
                                           title="Sửa danh mục">
                                            <i class='bx bx-edit'></i> Sửa
                                        </a>
                                        <a href="index.php?page_layout=xoa_loaisach&id=<?= $row['idloaisach'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bạn có chắc chắn muốn XÓA danh mục: <?= htmlspecialchars($row['tenloaisach']); ?>? Hành động này có thể ảnh hưởng đến các sách đang sử dụng danh mục này.');"
                                           title="Xóa danh mục">
                                            <i class='bx bx-trash'></i> Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted fst-italic py-3">
                                    Không có danh mục nào trong hệ thống.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>