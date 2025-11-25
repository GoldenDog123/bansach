<?php
require_once 'ketnoi.php';

$iddonhang = isset($_GET['iddonhang']) ? intval($_GET['iddonhang']) : 0;
if ($iddonhang <= 0) {
  header("Location: index.php?page_layout=danhsachdonhang");
  exit;
}

// Lấy danh sách người dùng và sách
$kh_sql = "SELECT idnguoidung, hoten FROM nguoidung ORDER BY hoten";
$kh_q = mysqli_query($ketnoi, $kh_sql);

$sach_sql = "SELECT idsach, tensach, dongia FROM sach ORDER BY tensach";
$sach_q = mysqli_query($ketnoi, $sach_sql);

// Lấy thông tin đơn hàng và chi tiết
$dh_q = mysqli_query($ketnoi, "SELECT * FROM donhang WHERE iddonhang = $iddonhang LIMIT 1");
$donhang = mysqli_fetch_assoc($dh_q);

$ct_q = mysqli_query($ketnoi, "SELECT dct.*, s.tensach FROM donhang_chitiet dct JOIN sach s ON dct.idsach = s.idsach WHERE dct.iddonhang = $iddonhang");

// POST xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idnguoidung = intval($_POST['idnguoidung']);
  $trangthai = mysqli_real_escape_string($ketnoi, $_POST['trangthai'] ?? 'moi');
  $items = $_POST['items'] ?? [];

  // Tính tổng tiền mới
  $tongtien = 0;
  $sanpham = [];
  foreach ($items as $it) {
    $idsach = intval($it['idsach']);
    $soluong = intval($it['soluong']);
    if ($idsach <= 0 || $soluong <= 0) continue;
    $r = mysqli_query($ketnoi, "SELECT dongia FROM sach WHERE idsach = $idsach LIMIT 1");
    $row = mysqli_fetch_assoc($r);
    $dongia = $row ? floatval($row['dongia']) : 0;
    $thanhtien = $dongia * $soluong;
    $tongtien += $thanhtien;
    $sanpham[] = [
      'idsach' => $idsach,
      'soluong' => $soluong,
      'dongia' => $dongia,
      'thanhtien' => $thanhtien
    ];
  }

  if (count($sanpham) > 0) {
    // Cập nhật donhang
    $qq = "UPDATE donhang SET idnguoidung = $idnguoidung, tongtien = $tongtien, trangthai = '" . mysqli_real_escape_string($ketnoi, $trangthai) . "' WHERE iddonhang = $iddonhang";
    if (mysqli_query($ketnoi, $qq)) {
      // Xóa chi tiết cũ
      mysqli_query($ketnoi, "DELETE FROM donhang_chitiet WHERE iddonhang = $iddonhang");
      // Thêm chi tiết mới
      foreach ($sanpham as $p) {
        $ins = "INSERT INTO donhang_chitiet (iddonhang, idsach, soluong, dongia, thanhtien) VALUES ($iddonhang, {$p['idsach']}, {$p['soluong']}, {$p['dongia']}, {$p['thanhtien']})";
        mysqli_query($ketnoi, $ins);
      }
      header("Location: index.php?page_layout=danhsachdonhang");
      exit;
    } else {
      $error = "Lỗi cập nhật: " . mysqli_error($ketnoi);
    }
  } else {
    $error = "Vui lòng nhập ít nhất 1 sản phẩm hợp lệ.";
  }
}
?>

<style>
  .card {
    border-radius: 12px;
  }
</style>

<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class='bx bx-edit'></i> Sửa đơn hàng #<?= $iddonhang; ?></h5>
      <a href="index.php?page_layout=danhsachdonhang" class="btn btn-light btn-sm">Quay lại</a>
    </div>
    <div class="card-body">
      <?php if (!empty($error)) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
      <?php } ?>

      <form method="POST" id="formSuaDonhang">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Khách hàng</label>
            <select name="idnguoidung" class="form-select" required>
              <option value="">-- Chọn khách hàng --</option>
              <?php while ($kh = mysqli_fetch_assoc($kh_q)) { ?>
                <option value="<?= $kh['idnguoidung']; ?>" <?= ($kh['idnguoidung'] == $donhang['idnguoidung']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($kh['hoten']); ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Trạng thái đơn</label>
            <select name="trangthai" class="form-select">
              <option value="moi" <?= $donhang['trangthai'] == 'moi' ? 'selected' : '' ?>>Mới</option>
              <option value="dang_xu_ly" <?= $donhang['trangthai'] == 'dang_xu_ly' ? 'selected' : '' ?>>Đang xử lý</option>
              <option value="hoan_thanh" <?= $donhang['trangthai'] == 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
            </select>
          </div>
        </div>

        <hr>

        <h6>Sản phẩm</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle" id="tableItemsEdit">
            <thead>
              <tr>
                <th style="width:45%;">Sách</th>
                <th style="width:15%;">Đơn giá</th>
                <th style="width:15%;">Số lượng</th>
                <th style="width:15%;">Thành tiền</th>
                <th style="width:10%;"></th>
              </tr>
            </thead>
            <tbody>
              <!-- JS sẽ load dữ liệu hiện có -->
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <div>
            <button type="button" class="btn btn-sm btn-light" id="addRowEdit"><i class="bx bx-plus"></i> Thêm sách</button>
          </div>
          <div>
            <strong>Tổng tiền: </strong> <span id="totalTextEdit">0 ₫</span>
          </div>
        </div>

        <hr>
        <div class="text-end">
          <button class="btn btn-primary">Cập nhật đơn hàng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Dữ liệu sách -->
<script>
  const sachList = {
    <?php
    mysqli_data_seek($sach_q, 0);
    $arr = [];
    while ($s = mysqli_fetch_assoc($sach_q)) {
      $arr[] = $s['idsach'] . ":{'t':'" . addslashes($s['tensach']) . "','p':" . floatval($s['dongia']) . "}";
    };
    echo implode(",", $arr);
    ?>
  };
</script>

<script>
  function createSachOptions() {
    let html = '<option value="">-- Chọn sách --</option>';
    for (const id in sachList) {
      const s = sachList[id];
      html += `<option value="${id}">${s.t}</option>`;
    }
    return html;
  }

  function formatCurrency(n) {
    return n.toLocaleString('vi-VN') + ' ₫';
  }

  function recalcTotalEdit() {
    let total = 0;
    document.querySelectorAll('#tableItemsEdit tbody tr').forEach(tr => {
      const tht = parseFloat(tr.querySelector('.line_total').value) || 0;
      total += tht;
      tr.querySelector('.td_line_total').innerText = formatCurrency(tht);
    });
    document.getElementById('totalTextEdit').innerText = formatCurrency(total);
  }

  function addRowEdit(data = {}) {
    const tbody = document.querySelector('#tableItemsEdit tbody');
    const tr = document.createElement('tr');

    tr.innerHTML = `
    <td>
      <select name="items[][idsach]" class="form-select form-select-sm sel_sach" required>
        ${createSachOptions()}
      </select>
    </td>

    <td>
      <input type="text" class="form-control form-control-sm txt_dongia" readonly>
      <input type="hidden" class="dongia_raw" value="0">
    </td>

    <td>
      <input type="number" min="1" name="items[][soluong]" value="${data.soluong||1}" 
           class="form-control form-control-sm txt_soluong" required>
    </td>

    <td>
      <span class="td_line_total">0 ₫</span>
      <input type="hidden" class="line_total" value="0">
    </td>

    <td>
      <button type="button" class="btn btn-sm btn-danger removeRow">
        <i class="bx bx-trash"></i>
      </button>
    </td>
  `;

    tbody.appendChild(tr);

    const sel = tr.querySelector('.sel_sach');
    const dongiaInput = tr.querySelector('.txt_dongia');
    const dongiaRaw = tr.querySelector('.dongia_raw');
    const soluongInput = tr.querySelector('.txt_soluong');
    const line_total_input = tr.querySelector('.line_total');

    // Khi chọn sách
    sel.addEventListener('change', function() {
      const id = this.value;
      if (id && sachList[id]) {
        const price = sachList[id].p;
        dongiaInput.value = price.toLocaleString('vi-VN');
        dongiaRaw.value = price;

        const qty = parseInt(soluongInput.value) || 1;
        const lt = price * qty;
        line_total_input.value = lt;

        recalcTotalEdit();
      }
    });

    // Khi thay đổi số lượng
    soluongInput.addEventListener('input', function() {
      const qty = parseInt(this.value) || 0;
      const price = parseFloat(dongiaRaw.value) || 0;
      const lt = price * qty;

      line_total_input.value = lt;
      recalcTotalEdit();
    });

    // Nút xóa dòng
    tr.querySelector('.removeRow').addEventListener('click', () => {
      tr.remove();
      recalcTotalEdit();
    });

    // Nếu load dữ liệu cũ
    if (data.idsach) {
      sel.value = data.idsach;
      dongiaInput.value = (data.dongia || 0).toLocaleString('vi-VN');
      dongiaRaw.value = data.dongia || 0;
      soluongInput.value = data.soluong;

      line_total_input.value = (data.dongia || 0) * data.soluong;
    }

    recalcTotalEdit();
  }
</script>