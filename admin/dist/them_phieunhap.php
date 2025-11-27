<?php
require_once('ketnoi.php');

// 1. Lấy danh sách sách để chọn trong form
$sql_sach = "SELECT idsach, tensach FROM sach ORDER BY tensach ASC";
$query_sach = mysqli_query($ketnoi, $sql_sach);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $ngaynhap = date('Y-m-d H:i:s'); 
    $nhacungcap = mysqli_real_escape_string($ketnoi, $_POST['nhacungcap'] ?? ''); // Thêm cột Nhà cung cấp
    $ghichu = mysqli_real_escape_string($ketnoi, $_POST['ghichu'] ?? '');
    
    $idsach_arr = $_POST['idsach'] ?? [];
    $soluong_arr = $_POST['soluongnhap'] ?? [];
    $gianhap_arr = $_POST['gianhap'] ?? [];
    
    $tongtiennhap = 0;
    $valid_items = [];

    // Lọc và tính toán tổng tiền
    foreach ($idsach_arr as $index => $idsach) {
        $idsach = (int)$idsach;
        $soluongnhap = (int)($soluong_arr[$index] ?? 0);
        $gianhap = (int)($gianhap_arr[$index] ?? 0);

        if ($idsach > 0 && $soluongnhap > 0 && $gianhap >= 0) {
            $tongtiennhap += $soluongnhap * $gianhap;
            $valid_items[] = [
                'idsach' => $idsach,
                'soluongnhap' => $soluongnhap,
                'gianhap' => $gianhap
            ];
        }
    }

    if (empty($valid_items)) {
        echo "<script>showToast('Vui lòng thêm ít nhất một sách hợp lệ!', 'danger');</script>";
    } else {
        // Bắt đầu Transaction để đảm bảo tính toàn vẹn dữ liệu
        mysqli_begin_transaction($ketnoi);
        
        try {
            // 2. Insert vào bảng phieunhap (ĐÃ SỬA: Thêm tongtiennhap và nhacungcap)
            $sql_insert_pn = "INSERT INTO phieunhap (ngaynhap, tongtiennhap, nhacungcap, ghichu) 
                              VALUES ('$ngaynhap', $tongtiennhap, '$nhacungcap', '$ghichu')";
            
            if (!mysqli_query($ketnoi, $sql_insert_pn)) {
                throw new Exception("Lỗi khi thêm Phiếu nhập.");
            }
            
            $idphieunhap = mysqli_insert_id($ketnoi);
            
            // 3. Insert vào bảng phieunhap_chitiet và cập nhật số lượng sách
            foreach ($valid_items as $item) {
                $idsach = $item['idsach'];
                $soluongnhap = $item['soluongnhap'];
                $gianhap = $item['gianhap'];
                
                // Insert chi tiết
                $sql_insert_ct = "INSERT INTO phieunhap_chitiet (idphieunhap, idsach, soluongnhap, gianhap) 
                                  VALUES ($idphieunhap, $idsach, $soluongnhap, $gianhap)";
                if (!mysqli_query($ketnoi, $sql_insert_ct)) {
                    throw new Exception("Lỗi khi thêm Chi tiết phiếu nhập.");
                }
                
                // Cập nhật số lượng tồn kho (sach.soluong = soluong + soluongnhap)
                $sql_update_sach = "UPDATE sach SET soluong = soluong + $soluongnhap WHERE idsach = $idsach";
                if (!mysqli_query($ketnoi, $sql_update_sach)) {
                    throw new Exception("Lỗi khi cập nhật tồn kho.");
                }
            }
            
            // 4. Commit Transaction
            mysqli_commit($ketnoi);
            echo "<script>showToast('Tạo Phiếu nhập PN$idphieunhap thành công!', 'success');</script>";
            header('Location: index.php?page_layout=danhsachnhapkho');
            exit();

        } catch (Exception $e) {
            // Rollback nếu có lỗi
            mysqli_rollback($ketnoi);
            echo "<script>showToast('Lỗi giao dịch: " . $e->getMessage() . " (" . mysqli_error($ketnoi) . ")', 'danger');</script>";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <h2 class="mb-4 text-success"><i class='bx bx-plus-circle'></i> Tạo Phiếu Nhập Kho Mới</h2>
        <form method="POST" id="phieuNhapForm">
            
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white fw-bold">Thông tin chung</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nhacungcap" class="form-label fw-bold">Nhà Cung Cấp (Tùy chọn)</label>
                        <input type="text" class="form-control" id="nhacungcap" name="nhacungcap"> </div>
                    <div class="mb-3">
                        <label for="ghichu" class="form-label fw-bold">Ghi chú (Tùy chọn)</label>
                        <textarea class="form-control" id="ghichu" name="ghichu" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white fw-bold d-flex justify-content-between align-items-center">
                    Chi tiết Sách Nhập
                    <button type="button" class="btn btn-sm btn-light" id="addItemBtn">
                        <i class='bx bx-plus-medical'></i> Thêm Sách
                    </button>
                </div>
                <div class="card-body">
                    <div id="itemsContainer">
                        <div class="row item-row mb-3 gx-2" data-index="0">
                            <div class="col-md-5">
                                <label class="form-label">Chọn Sách</label>
                                <select class="form-select" name="idsach[]" required>
                                    <option value="">-- Chọn Sách --</option>
                                    <?php 
                                    mysqli_data_seek($query_sach, 0); 
                                    $sach_options = ''; 
                                    while($sach = mysqli_fetch_assoc($query_sach)): 
                                        $option = "<option value='{$sach['idsach']}'>".htmlspecialchars($sach['tensach'])."</option>";
                                        $sach_options .= $option;
                                        echo $option;
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Số lượng nhập</label>
                                <input type="number" class="form-control" name="soluongnhap[]" min="1" value="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Giá nhập (VNĐ)</label>
                                <input type="number" class="form-control" name="gianhap[]" min="0" value="0" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger w-100 remove-item-btn"><i class='bx bx-trash'></i></button>
                            </div>
                        </div>
                    </div>
                    <p class="text-danger mt-3" id="error-message" style="display: none;">Vui lòng thêm ít nhất một sách hợp lệ.</p>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?page_layout=danhsachnhapkho" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Hủy / Quay lại</a>
                <button type="submit" class="btn btn-success"><i class='bx bx-save'></i> Lưu Phiếu Nhập</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');
        const errorMessage = document.getElementById('error-message');
        
        const sachOptions = `<?php echo addslashes($sach_options); ?>`;
        
        function updateRemoveButtons() {
            const rows = itemsContainer.querySelectorAll('.item-row');
            rows.forEach(row => {
                const removeBtn = row.querySelector('.remove-item-btn');
                if (removeBtn) {
                    removeBtn.disabled = rows.length === 1; 
                    removeBtn.onclick = function() {
                        if (rows.length > 1) {
                            row.remove();
                            updateRemoveButtons();
                        }
                    };
                }
            });
        }
        
        const itemTemplate = `
            <div class="row item-row mb-3 gx-2">
                <div class="col-md-5">
                    <label class="form-label">Chọn Sách</label>
                    <select class="form-select" name="idsach[]" required>
                        <option value="">-- Chọn Sách --</option>
                        ${sachOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số lượng nhập</label>
                    <input type="number" class="form-control" name="soluongnhap[]" min="1" value="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giá nhập (VNĐ)</label>
                    <input type="number" class="form-control" name="gianhap[]" min="0" value="0" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger w-100 remove-item-btn"><i class='bx bx-trash'></i></button>
                </div>
            </div>
        `;

        addItemBtn.addEventListener('click', function() {
            itemsContainer.insertAdjacentHTML('beforeend', itemTemplate);
            updateRemoveButtons();
        });

        document.getElementById('phieuNhapForm').addEventListener('submit', function(e) {
            const rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length === 0) {
                errorMessage.style.display = 'block';
                e.preventDefault();
            } else {
                errorMessage.style.display = 'none';
            }
        });
        
        updateRemoveButtons();
    });
</script>