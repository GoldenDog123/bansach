<?php
require_once 'ketnoi.php';

// Lấy danh sách người dùng và sách để hiển thị trong form
$kh_sql = "SELECT idnguoidung, hoten FROM nguoidung ORDER BY hoten";
$kh_q = mysqli_query($ketnoi, $kh_sql);

$sach_sql = "SELECT idsach, tensach, dongia FROM sach ORDER BY tensach";
$sach_q = mysqli_query($ketnoi, $sach_sql);

// Xử lý POST khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idnguoidung = intval($_POST['idnguoidung']);
  $ngaydat = date('Y-m-d H:i:s');
  $trangthai = mysqli_real_escape_string($ketnoi, $_POST['trangthai'] ?? 'moi');
  $items = $_POST['items'] ?? []; // mảng các dòng: [ ['idsach'=>..,'soluong'=>..], ... ]

  // Tính tổng tiền
  $tongtien = 0;
  $sanpham_hople = [];
  foreach ($items as $it) {
    $idsach = intval($it['idsach']);
    $soluong = intval($it['soluong']);
    if ($idsach <= 0 || $soluong <= 0) continue;
    // Lấy dongia hiện tại từ DB (an toàn hơn)
    $r = mysqli_query($ketnoi, "SELECT dongia FROM sach WHERE idsach = $idsach LIMIT 1");
    $row = mysqli_fetch_assoc($r);
    $dongia = $row ? floatval($row['dongia']) : 0;
    $thanhtien = $dongia * $soluong;
    $tongtien += $thanhtien;
    $sanpham_hople[] = [
      'idsach' => $idsach,
      'soluong' => $soluong,
      'dongia' => $dongia,
      'thanhtien' => $thanhtien
    ];
  }

  if (count($sanpham_hople) > 0) {
    // Insert donhang
    $tongtien_sql = mysqli_real_escape_string($ketnoi, $tongtien);
    $q = "INSERT INTO donhang (idnguoidung, ngaydat, tongtien, trangthai) VALUES ($idnguoidung, '$ngaydat', $tongtien_sql, '" . mysqli_real_escape_string($ketnoi, $trangthai) . "')";
    if (mysqli_query($ketnoi, $q)) {
      $iddonhang = mysqli_insert_id($ketnoi);

      // Insert donhang_chitiet
      foreach ($sanpham_hople as $p) {
        $idsach = intval($p['idsach']);
        $soluong = intval($p['soluong']);
        $dongia = floatval($p['dongia']);
        $thanhtien = floatval($p['thanhtien']);
        $ins = "INSERT INTO donhang_chitiet (iddonhang, idsach, soluong, dongia, thanhtien)
                VALUES ($iddonhang, $idsach, $soluong, $dongia, $thanhtien)";
        mysqli_query($ketnoi, $ins);
      }

      // Tạo record giao hàng mặc định
      mysqli_query($ketnoi, "INSERT INTO giaohang (iddonhang, trangthai) VALUES ($iddonhang, 'chua_giao')");

      // Tạo record thanh toán mặc định
      mysqli_query($ketnoi, "INSERT INTO thanhtoan (iddonhang, trangthai) VALUES ($iddonhang, 'chua_thanh_toan')");

      // Chuyển hướng về danh sách
      header("Location: index.php?page_layout=danhsachdonhang");
      exit;
    } else {
      $error = "Lỗi khi lưu đơn hàng: " . mysqli_error($ketnoi);
    }
  } else {
    $error = "Vui lòng chọn ít nhất 1 sách với số lượng hợp lệ.";
  }
}
?>

<style>
  .card {
    border-radius: 12px;
  }

  .btn-circle {
    width: 36px;
    height: 36px;
    padding: 0;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
</style>

<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class='bx bx-plus'></i> Thêm đơn hàng</h5>
      <a href="index.php?page_layout=danhsachdonhang" class="btn btn-light btn-sm">Quay lại</a>
    </div>
    <div class="card-body">
      <?php if (!empty($error)) { ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
      <?php } ?>

      <form method="POST" id="formDonhang">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Khách hàng</label>
            <select name="idnguoidung" class="form-select" required>
              <option value="">-- Chọn khách hàng --</option>
              <?php while ($kh = mysqli_fetch_assoc($kh_q)) { ?>
                <option value="<?= $kh['idnguoidung']; ?>"><?= htmlspecialchars($kh['hoten']); ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Trạng thái đơn</label>
            <select name="trangthai" class="form-select">
              <option value="moi">Mới</option>
              <option value="dang_xu_ly">Đang xử lý</option>
              <option value="hoan_thanh">Hoàn thành</option>
            </select>
          </div>
        </div>

        <hr>

        <h6>Sản phẩm</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle" id="tableItems">
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
              <!-- JS sẽ thêm dòng -->
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <div>
            <button type="button" class="btn btn-sm btn-light" id="addRow"><i class="bx bx-plus"></i> Thêm sách</button>
          </div>
          <div>
            <strong>Tổng tiền: </strong> <span id="totalText">0 ₫</span>
          </div>
        </div>

        <hr>
        <div class="text-end">
          <button class="btn btn-primary">Lưu đơn hàng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Dữ liệu sách để JS xử lý -->
<script>
  const sachList = {
    <?php
    mysqli_data_seek($sach_q, 0);
    $arr = [];
    while ($s = mysqli_fetch_assoc($sach_q)) {
      $arr[] = $s['idsach'] . ":{'t':'" . addslashes($s['tensach']) . "','p':" . floatval($s['dongia']) . "}";
    }
    echo implode(",", $arr);
    ?>
  };
</script>

<script>
  let rowIndex = 0;

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

  function recalcTotal() {
    let total = 0;
    document.querySelectorAll('#tableItems tbody tr').forEach(tr => {
      const tht = parseFloat(tr.querySelector('.line_total').value) || 0;
      total += tht;
      tr.querySelector('.td_line_total').innerText = formatCurrency(tht);
    });
    document.getElementById('totalText').innerText = formatCurrency(total);
  }

  function addRow(data = {}) {
    const i = rowIndex++; // mỗi dòng có index cố định
    const tbody = document.querySelector('#tableItems tbody');
    const tr = document.createElement('tr');

    tr.innerHTML = `
      <td>
        <select name="items[${i}][idsach]" class="form-select form-select-sm sel_sach" required>
          ${createSachOptions()}
        </select>
      </td>

      <td>
        <input type="text" class="form-control form-control-sm txt_dongia" readonly>
      </td>

      <td>
        <input type="number" min="1" name="items[${i}][soluong]" value="${data.soluong || 1}"
        class="form-control form-control-sm txt_soluong" required>
      </td>

      <td>
        <span class="td_line_total">0 ₫</span>
        <input type="hidden" name="items[${i}][thanhtien]" class="line_total" value="0">
      </td>

      <td>
        <button type="button" class="btn btn-sm btn-danger btn-circle removeRow">
          <i class="bx bx-trash"></i>
        </button>
      </td>
    `;

    tbody.appendChild(tr);

    // Lấy element
    const sel = tr.querySelector('.sel_sach');
    const dongiaInput = tr.querySelector('.txt_dongia');
    const soluongInput = tr.querySelector('.txt_soluong');
    const lineTotal = tr.querySelector('.line_total');

    // Chọn sách → cập nhật đơn giá
    sel.addEventListener('change', function() {
      const id = this.value;
      if (id && sachList[id]) {
        dongiaInput.value = sachList[id].p.toLocaleString('vi-VN');
        const qty = parseInt(soluongInput.value) || 1;
        const lt = sachList[id].p * qty;
        lineTotal.value = lt;
        recalcTotal();
      } else {
        dongiaInput.value = "";
        lineTotal.value = 0;
        recalcTotal();
      }
    });

    // thay đổi số lượng
    soluongInput.addEventListener('input', function() {
      const id = sel.value;
      const qty = parseInt(this.value) || 0;
      if (id && sachList[id]) {
        const lt = sachList[id].p * qty;
        lineTotal.value = lt;
        recalcTotal();
      }
    });

    // nút xóa
    tr.querySelector('.removeRow').addEventListener('click', function() {
      tr.remove();
      recalcTotal();
    });

    // Nếu có dữ liệu preset
    if (data.idsach) {
      sel.value = data.idsach;
      sel.dispatchEvent(new Event('change'));
    }

    recalcTotal();
  }

  document.getElementById('addRow').addEventListener('click', () => addRow());
  addRow(); // thêm 1 dòng mặc định
</script>