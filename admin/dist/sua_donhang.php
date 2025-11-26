<?php
require_once 'ketnoi.php';

// 1. Kiểm tra ID hợp lệ
$iddonhang = isset($_GET['iddonhang']) ? intval($_GET['iddonhang']) : 0;
if ($iddonhang <= 0) {
    header("Location: index.php?page_layout=danhsachdonhang");
    exit;
}

// 2. Lấy dữ liệu danh mục (Khách hàng & Sách) để hiển thị form
// Lấy danh sách khách hàng
$kh_arr = [];
$kh_sql = "SELECT idnguoidung, hoten FROM nguoidung ORDER BY hoten";
$kh_res = mysqli_query($ketnoi, $kh_sql);
while ($row = mysqli_fetch_assoc($kh_res)) {
    $kh_arr[] = $row;
}

// Lấy danh sách sách (Dùng để tạo biến JS)
$sach_map = []; // Mảng mapping id -> thông tin để tra cứu nhanh
$sach_options = []; // Mảng danh sách để loop trong select
$sach_sql = "SELECT idsach, tensach, dongia FROM sach ORDER BY tensach";
$sach_res = mysqli_query($ketnoi, $sach_sql);
while ($row = mysqli_fetch_assoc($sach_res)) {
    $sach_options[] = $row;
    $sach_map[$row['idsach']] = [
        'ten' => $row['tensach'],
        'gia' => (float)$row['dongia']
    ];
}

// 3. Lấy thông tin Đơn hàng hiện tại
$dh_query = mysqli_query($ketnoi, "SELECT * FROM donhang WHERE iddonhang = $iddonhang LIMIT 1");
$donhang = mysqli_fetch_assoc($dh_query);
if (!$donhang) {
    die("Không tìm thấy đơn hàng!");
}

// 4. Lấy chi tiết đơn hàng cũ (để JS load lại vào bảng)
$ct_query = mysqli_query($ketnoi, "SELECT idsach, soluong, dongia FROM donhang_chitiet WHERE iddonhang = $iddonhang");
$chitiet_cu = [];
while ($row = mysqli_fetch_assoc($ct_query)) {
    $chitiet_cu[] = $row;
}

// 5. XỬ LÝ POST (CẬP NHẬT)
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idnguoidung = intval($_POST['idnguoidung']);
    $trangthai = $_POST['trangthai'] ?? 'cho_duyet';
    $items = $_POST['items'] ?? []; // Mảng chứa idsach và soluong

    if ($idnguoidung <= 0) {
        $error = "Vui lòng chọn khách hàng.";
    } elseif (empty($items)) {
        $error = "Đơn hàng phải có ít nhất 1 sản phẩm.";
    } else {
        // Bắt đầu Transaction (Quan trọng để đảm bảo dữ liệu không bị lỗi nửa vời)
        mysqli_begin_transaction($ketnoi);
        try {
            $tongtien_moi = 0;
            $insert_data = [];

            // Duyệt qua items để tính toán lại tổng tiền (Bảo mật: Lấy giá từ DB, không lấy từ Client)
            foreach ($items as $item) {
    $s_id  = intval($item['idsach'] ?? 0);
    $s_qty = intval($item['soluong'] ?? 0);

    if ($s_id > 0 && $s_qty > 0 && isset($sach_map[$s_id])) {
        $dongia_thuc = $sach_map[$s_id]['gia'];
        $thanhtien   = $dongia_thuc * $s_qty;
        $tongtien_moi += $thanhtien;

        $insert_data[] = [
            'idsach' => $s_id,
            'soluong' => $s_qty,
            'dongia' => $dongia_thuc,
            'thanhtien' => $thanhtien,
        ];
    }
}


            if (empty($insert_data)) {
                throw new Exception("Dữ liệu sản phẩm không hợp lệ.");
            }

            // B1: Update thông tin chung đơn hàng
            $stmt = mysqli_prepare($ketnoi, "UPDATE donhang SET idnguoidung=?, tongtien=?, trangthai=?, ngaydat=NOW() WHERE iddonhang=?");
            mysqli_stmt_bind_param($stmt, "idsi", $idnguoidung, $tongtien_moi, $trangthai, $iddonhang);
            mysqli_stmt_execute($stmt);

            // B2: Xóa sạch chi tiết cũ
            mysqli_query($ketnoi, "DELETE FROM donhang_chitiet WHERE iddonhang = $iddonhang");

            // B3: Thêm chi tiết mới
            $stmt_ins = mysqli_prepare($ketnoi, "INSERT INTO donhang_chitiet (iddonhang, idsach, soluong, dongia, thanhtien) VALUES (?, ?, ?, ?, ?)");
            foreach ($insert_data as $d) {
                mysqli_stmt_bind_param($stmt_ins, "iiidd", $iddonhang, $d['idsach'], $d['soluong'], $d['dongia'], $d['thanhtien']);
                mysqli_stmt_execute($stmt_ins);
            }

            // Hoàn tất Transaction
            mysqli_commit($ketnoi);
            header("Location: index.php?page_layout=danhsachdonhang");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($ketnoi); // Gặp lỗi thì hoàn tác mọi thay đổi
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<style>
    .card-custom { border: none; box-shadow: 0 0 15px rgba(0,0,0,0.05); border-radius: 10px; }
    .table-custom th { background-color: #f8f9fa; font-weight: 600; }
    .total-display { font-size: 1.2rem; font-weight: bold; color: #d32f2f; }
</style>

<div class="container-fluid py-4">
    <div class="card card-custom">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary"><i class="fas fa-edit me-2"></i>Cập nhật đơn hàng #<?= $iddonhang ?></h5>
                <a href="index.php?page_layout=danhsachdonhang" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
        <div class="card-body">
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" id="formUpdateOrder">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Khách hàng <span class="text-danger">*</span></label>
                        <select name="idnguoidung" class="form-select" required>
                            <option value="">-- Chọn khách hàng --</option>
                            <?php foreach ($kh_arr as $kh): ?>
                                <option value="<?= $kh['idnguoidung'] ?>" <?= $kh['idnguoidung'] == $donhang['idnguoidung'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kh['hoten']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trạng thái</label>
                        <select name="trangthai" class="form-select">
                            <option value="cho_duyet" <?= $donhang['trangthai'] == 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="dang_giao" <?= $donhang['trangthai'] == 'dang_giao' ? 'selected' : '' ?>>Đang giao</option>
                            <option value="hoan_thanh" <?= $donhang['trangthai'] == 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                            <option value="da_huy" <?= $donhang['trangthai'] == 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 border-bottom pb-2">Chi tiết sản phẩm</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-custom align-middle" id="orderTable">
                        <thead>
                            <tr>
                                <th style="width: 40%">Tên sách</th>
                                <th style="width: 15%">Đơn giá</th>
                                <th style="width: 15%">Số lượng</th>
                                <th style="width: 20%" class="text-end">Thành tiền</th>
                                <th style="width: 10%" class="text-center">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                <td class="text-end total-display" id="grandTotal">0 ₫</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()">
                        <i class="fas fa-plus"></i> Thêm sách
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let rowIndex = 0; // 🔥 Mỗi dòng một index KHÔNG trùng nhau

    const bookData = <?= json_encode($sach_map) ?>;
    const bookOptions = <?= json_encode($sach_options) ?>;
    const existingItems = <?= json_encode($chitiet_cu) ?>;

    const fmtMoney = (amount) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    };

    // 🔥 SỬA: createBookSelect giờ nhận rowId
    const createBookSelect = (rowId, selectedId = null) => {
        let html = `<select name="items[${rowId}][idsach]" class="form-select form-select-sm book-select" onchange="updateRow(this)" required>`;
        html += `<option value="">-- Chọn sách --</option>`;

        bookOptions.forEach(book => {
            const sel = (selectedId == book.idsach) ? "selected" : "";
            html += `<option value="${book.idsach}" ${sel}>${book.tensach}</option>`;
        });

        html += `</select>`;
        return html;
    };

    // 🔥 SỬA: addRow tạo 1 rowId duy nhất
    const addRow = (data = null) => {
        const tbody = document.getElementById('tableBody');
        const tr = document.createElement('tr');

        const rowId = rowIndex++; // Mỗi dòng tăng 1

        const idSach = data ? data.idsach : '';
        const qty = data ? data.soluong : 1;
        const price = data ? parseFloat(data.dongia) : 0;
        const total = price * qty;

        tr.innerHTML = `
            <td>${createBookSelect(rowId, idSach)}</td>
            <td>
                <input type="text" class="form-control form-control-sm price-display bg-light" value="${fmtMoney(price)}" readonly>
                <input type="hidden" name="items[${rowId}][dongia]" class="price-raw" value="${price}">
            </td>
            <td>
                <input type="number" name="items[${rowId}][soluong]" class="form-control form-control-sm qty-input"
                       value="${qty}" min="1" oninput="updateRow(this)" required>
            </td>
            <td class="text-end fw-bold row-total">${fmtMoney(total)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        updateGrandTotal();
    };

    const removeRow = (btn) => {
        btn.closest('tr').remove();
        updateGrandTotal();
    };

    const updateRow = (el) => {
        const tr = el.closest('tr');
        const select = tr.querySelector('.book-select');
        const priceDisplay = tr.querySelector('.price-display');
        const priceRaw = tr.querySelector('.price-raw');
        const qtyInput = tr.querySelector('.qty-input');
        const rowTotal = tr.querySelector('.row-total');

        const bookId = select.value;
        let price = 0;

        if (bookId && bookData[bookId]) {
            price = bookData[bookId].gia;
            priceRaw.value = price;
            priceDisplay.value = fmtMoney(price);
        } else {
            priceRaw.value = 0;
            priceDisplay.value = fmtMoney(0);
        }

        const qty = parseInt(qtyInput.value) || 0;
        const total = price * qty;
        rowTotal.innerText = fmtMoney(total);

        updateGrandTotal();
    };

    const updateGrandTotal = () => {
        let total = 0;
        document.querySelectorAll('#tableBody tr').forEach(tr => {
            const price = parseFloat(tr.querySelector('.price-raw').value) || 0;
            const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
            total += price * qty;
        });
        document.getElementById('grandTotal').innerText = fmtMoney(total);
    };

    // 🔥 Load dữ liệu cũ đúng cách
    document.addEventListener("DOMContentLoaded", () => {
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(item => addRow(item));
        } else {
            addRow();
        }
    });
</script>
