<?php
require_once('ketnoi.php');

// Truy vấn lấy người dùng và địa chỉ đầu tiên (hoặc địa chỉ có ID nhỏ nhất)
$sql = "
    SELECT 
        nd.idnguoidung, 
        nd.hoten,
        nd.email, 
        nd.sdt,
        nd.vaitro,
        COALESCE(d.diachi, 'Chưa có địa chỉ') as diachi
    FROM nguoidung nd
    LEFT JOIN diachi d ON nd.idnguoidung = d.idnguoidung AND d.iddiachi = (
        SELECT MIN(iddiachi) FROM diachi WHERE idnguoidung = nd.idnguoidung
    )
    ORDER BY nd.idnguoidung DESC
";

$query = mysqli_query($ketnoi, $sql); 

if (!$query) {
    die("Lỗi truy vấn: " . mysqli_error($ketnoi));
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow border-0" style="border-radius: 16px;">
        <div class="card-header text-white d-flex justify-content-between align-items-center"
             style="background: linear-gradient(90deg, #9333ea, #a855f7); color: #fff;">
            <h4 class="mb-0 fw-bold"><i class='bx bx-group'></i> Quản lý Người dùng</h4>
            <a href="index.php?page_layout=them_nguoidung" class="btn btn-light btn-sm fw-bold shadow-sm">
                <i class='bx bx-plus-circle'></i> Thêm Người dùng Mới
            </a>
        </div>

        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm bg-white">
                    <thead class="text-center align-middle" style="background-color: #f3e8ff;">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th class="text-start">Họ Tên</th>
                            <th style="width: 150px;">Email</th>
                            <th style="width: 120px;">Điện thoại</th>
                            <th class="text-start">Địa chỉ chính</th>
                            <th style="width: 100px;">Vai trò</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php 
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                                $vaitro_class = ($row['vaitro'] == 'admin') ? 'badge bg-danger' : 'badge bg-success';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['idnguoidung']); ?></td>
                                <td class="text-start fw-semibold"><?= htmlspecialchars($row['hoten']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['sdt'] ?? 'N/A'); ?></td>
                                <td class="text-start"><?= htmlspecialchars($row['diachi'] ?? 'Chưa có địa chỉ'); ?></td>
                                <td><span class="<?= $vaitro_class; ?>"><?= htmlspecialchars(ucfirst($row['vaitro'])); ?></span></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="index.php?page_layout=sua_nguoidung&id=<?= $row['idnguoidung'] ?>" 
                                           class="btn btn-info btn-sm text-white"
                                           title="Sửa người dùng">
                                            <i class='bx bx-edit'></i> Sửa
                                        </a>
                                        <a href="index.php?page_layout=xoa_nguoidung&id=<?= $row['idnguoidung'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bạn có chắc chắn muốn XÓA người dùng: <?= htmlspecialchars($row['hoten']); ?>? Hành động này sẽ xóa tất cả địa chỉ và dữ liệu liên quan.');"
                                           title="Xóa người dùng">
                                            <i class='bx bx-trash'></i> Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted fst-italic py-3">
                                    Không có người dùng nào trong hệ thống.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
/* Ẩn overflow-x auto mặc định của table-responsive nếu cần thiết */
.table-responsive {
    overflow-x: auto !important; 
    padding-bottom: 5px; /* Thêm không gian cho thanh cuộn */
}

/* Ép nội dung trong cột địa chỉ bị cắt ngắn và hiển thị dấu ba chấm */
.text-truncate-custom {
    max-width: 250px; 
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block; /* Quan trọng để max-width hoạt động */
}

/* Đảm bảo các tiêu đề bảng không bị xuống dòng */
.table thead th {
    white-space: nowrap;
}
</style>