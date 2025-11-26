<?php
require_once 'ketnoi.php';

/* ============================
   LẤY DỮ LIỆU KHÁCH & SÁCH 
=============================== */
$kh_q = mysqli_query($ketnoi, "SELECT idnguoidung, hoten FROM nguoidung ORDER BY hoten");
$sach_q = mysqli_query($ketnoi, "SELECT idsach, tensach, dongia FROM sach ORDER BY tensach");

/* ============================
   XỬ LÝ SUBMIT FORM
=============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $idnguoidung = intval($_POST['idnguoidung']);
  $ngaydat = date("Y-m-d H:i:s");
  $trangthai = $_POST['trangthai'] ?? 'moi';

  $items = $_POST['items'] ?? [];

  // TÍNH TỔNG TIỀN
  $tongtien = 0;
  $hop_le = [];

  foreach ($items as $it) {
    $idsach = intval($it['idsach']);
    $soluong = intval($it['soluong']);

    if ($idsach <= 0 || $soluong <= 0) continue;

    $qr = mysqli_query($ketnoi, "SELECT dongia FROM sach WHERE idsach = $idsach LIMIT 1");
    $rw = mysqli_fetch_assoc($qr);

    $dongia = $rw ? floatval($rw['dongia']) : 0;
    $thanhtien = $dongia * $soluong;

    $tongtien += $thanhtien;

    $hop_le[] = [
      'idsach' => $idsach,
      'soluong' => $soluong,
      'dongia' => $dongia,
      'thanhtien' => $thanhtien
    ];
  }

  if (count($hop_le) > 0) {

    // Lưu đơn hàng
    $sql_add = "
      INSERT INTO donhang (idnguoidung, ngaydat, tongtien, trangthai)
      VALUES ($idnguoidung, '$ngaydat', $tongtien, '$trangthai')
    ";

    if (mysqli_query($ketnoi, $sql_add)) {
      $iddonhang = mysqli_insert_id($ketnoi);

      // Chi tiết sách
      foreach ($hop_le as $p) {
        mysqli_query($ketnoi, "
          INSERT INTO donhang_chitiet 
          (iddonhang, idsach, soluong, dongia, thanhtien)
          VALUES ($iddonhang, {$p['idsach']}, {$p['soluong']}, {$p['dongia']}, {$p['thanhtien']})
        ");
      }

      // Tạo record giao hàng
      mysqli_query($ketnoi, "
        INSERT INTO giaohang (iddonhang, trangthai)
        VALUES ($iddonhang, 'dang_chuan_bi')
      ");

      // Tạo record thanh toán
      mysqli_query($ketnoi, "
        INSERT INTO thanhtoan (iddonhang, trangthai)
        VALUES ($iddonhang, 'chua_thanh_toan')
      ");

      header("Location: index.php?page_layout=danhsachdonhang");
      exit;
    } else {
      $error = "Lỗi khi lưu đơn hàng: " . mysqli_error($ketnoi);
    }
  } else {
    $error = "Bạn phải chọn ít nhất 1 sách hợp lệ.";
  }
}
?>

<style>
  .card { border-radius: 16px; }
  .card-header {
    background: linear-gradient(135deg, #4CAF50, #2E7D32);
    color: #fff;
    padding: 15px 20px;
  }
  .btn-circle {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
  }
</style>

<?php
require_once 'ketnoi.php';

/* ============================
   LẤY DỮ LIỆU KHÁCH & SÁCH
=============================== */
$kh_q = mysqli_query($ketnoi, "SELECT idnguoidung, hoten FROM nguoidung ORDER BY hoten");
$sach_q = mysqli_query($ketnoi, "SELECT idsach, tensach, dongia FROM sach ORDER BY tensach");

/* ============================
   TRẠNG THÁI ĐƠN
=============================== */
$trangthai_options = [
    'cho_duyet' => 'Chờ duyệt',
    'dang_giao' => 'Đang giao',
    'hoan_thanh' => 'Hoàn thành',
    'da_huy' => 'Đã hủy'
];

/* ============================
   XỬ LÝ SUBMIT FORM
=============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idnguoidung = intval($_POST['idnguoidung']);
    $ngaydat = date("Y-m-d H:i:s");
    $trangthai = $_POST['trangthai'] ?? 'cho_duyet';
    if (!array_key_exists($trangthai, $trangthai_options)) {
        $trangthai = 'cho_duyet';
    }

    $items = $_POST['items'] ?? [];
    $tongtien = 0;
    $hop_le = [];

    foreach ($items as $it) {
        $idsach = intval($it['idsach']);
        $soluong = intval($it['soluong']);
        if ($idsach <= 0 || $soluong <= 0) continue;

        $qr = mysqli_query($ketnoi, "SELECT dongia FROM sach WHERE idsach = $idsach LIMIT 1");
        $rw = mysqli_fetch_assoc($qr);
        $dongia = $rw ? floatval($rw['dongia']) : 0;
        $thanhtien = $dongia * $soluong;
        $tongtien += $thanhtien;

        $hop_le[] = [
            'idsach' => $idsach,
            'soluong' => $soluong,
            'dongia' => $dongia,
            'thanhtien' => $thanhtien
        ];
    }

    if (count($hop_le) > 0) {
        // Lưu đơn hàng
        $sql_add = "
            INSERT INTO donhang (idnguoidung, ngaydat, tongtien, trangthai)
            VALUES ($idnguoidung, '$ngaydat', $tongtien, '$trangthai')
        ";

        if (mysqli_query($ketnoi, $sql_add)) {
            $iddonhang = mysqli_insert_id($ketnoi);

            // Chi tiết sách
            foreach ($hop_le as $p) {
                mysqli_query($ketnoi, "
                    INSERT INTO donhang_chitiet
                    (iddonhang, idsach, soluong, dongia, thanhtien)
                    VALUES ($iddonhang, {$p['idsach']}, {$p['soluong']}, {$p['dongia']}, {$p['thanhtien']})
                ");
            }

            // Tạo record giao hàng
            mysqli_query($ketnoi, "
                INSERT INTO giaohang (iddonhang, trangthai)
                VALUES ($iddonhang, 'dang_chuan_bi')
            ");

            // Tạo record thanh toán
            mysqli_query($ketnoi, "
                INSERT INTO thanhtoan (iddonhang, trangthai)
                VALUES ($iddonhang, 'chua_thanh_toan')
            ");

            header("Location: index.php?page_layout=danhsachdonhang");
            exit;
        } else {
            $error = "Lỗi khi lưu đơn hàng: " . mysqli_error($ketnoi);
        }
    } else {
        $error = "Bạn phải chọn ít nhất 1 sách hợp lệ.";
    }
}
?>

<style>
.card { border-radius: 16px; }
.card-header {
    background: linear-gradient(135deg, #4CAF50, #2E7D32);
    color: #fff;
    padding: 15px 20px;
}
.btn-circle {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
}
</style>

<div class="container mt-4">
  <div class="card shadow border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class='bx bx-cart-add'></i> Thêm đơn hàng</h5>
      <a href="index.php?page_layout=danhsachdonhang" class="btn btn-light btn-sm">Quay lại</a>
    </div>

```
<div class="card-body p-4">

  <?php if (!empty($error)) { ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php } ?>

  <form method="POST">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Khách hàng</label>
        <select name="idnguoidung" class="form-select" required>
          <option value="">-- Chọn khách hàng --</option>
          <?php while ($kh = mysqli_fetch_assoc($kh_q)) { ?>
            <option value="<?= $kh['idnguoidung'] ?>"><?= htmlspecialchars($kh['hoten']) ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Trạng thái đơn</label>
        <select name="trangthai" class="form-select">
          <?php foreach ($trangthai_options as $key => $label): ?>
            <option value="<?= $key ?>"><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <hr>

    <h5 class="fw-bold mb-3">Danh sách sách</h5>
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="tableItems">
        <thead class="table-light">
          <tr>
            <th width="40%">Sách</th>
            <th width="15%">Đơn giá</th>
            <th width="15%">Số lượng</th>
            <th width="15%">Thành tiền</th>
            <th width="10%"></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between mt-3">
      <button type="button" class="btn btn-success btn-sm" id="addRow">
        <i class="bx bx-plus"></i> Thêm sách
      </button>
      <h5 class="mb-0">Tổng tiền: <span class="text-danger fw-bold" id="totalText">0 ₫</span></h5>
    </div>

    <hr>

    <div class="text-end">
      <button class="btn btn-primary px-4"><i class="bx bx-save"></i> Lưu đơn hàng</button>
    </div>

  </form>
</div>
```

  </div>
</div>

<script>
const sachList = {
<?php
mysqli_data_seek($sach_q, 0);
while ($s = mysqli_fetch_assoc($sach_q)) {
    echo "{$s['idsach']}:{name:'" . addslashes($s['tensach']) . "', price:" . $s['dongia'] . "},";
}
?>
};

let index = 0;

function vnd(x) { return x.toLocaleString("vi-VN") + " ₫"; }

function loadRow(data = {}) {
    const i = index++;
    const row = `
      <tr>
        <td>
          <select class="form-select form-select-sm sel" name="items[${i}][idsach]" required>
            <option value="">-- Chọn sách --</option>
            ${Object.keys(sachList).map(id => `<option value="${id}">${sachList[id].name}</option>`).join('')}
          </select>
        </td>
        <td><input type="text" class="form-control form-control-sm price" readonly></td>
        <td><input type="number" min="1" class="form-control form-control-sm qty" name="items[${i}][soluong]" value="1"></td>
        <td>
          <span class="lineShow">0 ₫</span>
          <input type="hidden" class="line" name="items[${i}][thanhtien]" value="0">
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-danger btn-circle remove"><i class="bx bx-trash"></i></button>
        </td>
      </tr>
    `;

    const tbody = document.querySelector("#tableItems tbody");
    tbody.insertAdjacentHTML("beforeend", row);

    const tr = tbody.lastElementChild;
    const sel = tr.querySelector(".sel");
    const qty = tr.querySelector(".qty");
    const price = tr.querySelector(".price");
    const line = tr.querySelector(".line");
    const lineShow = tr.querySelector(".lineShow");

    sel.addEventListener("change", () => {
        const id = sel.value;
        if (!id) { price.value = ""; line.value = 0; recalc(); return; }
        price.value = vnd(sachList[id].price);
        const tt = qty.value * sachList[id].price;
        line.value = tt;
        lineShow.textContent = vnd(tt);
        recalc();
    });

    qty.addEventListener("input", () => {
        const id = sel.value;
        if (!id) return;
        const tt = qty.value * sachList[id].price;
        line.value = tt;
        lineShow.textContent = vnd(tt);
        recalc();
    });

    tr.querySelector(".remove").addEventListener("click", () => { tr.remove(); recalc(); });
}

function recalc() {
    let total = 0;
    document.querySelectorAll(".line").forEach(l => total += Number(l.value));
    document.getElementById("totalText").textContent = vnd(total);
}

document.getElementById("addRow").addEventListener("click", () => loadRow());
loadRow();
</script>


<!-- JS -->
<script>
  const sachList = {
    <?php
    mysqli_data_seek($sach_q, 0);
    while ($s = mysqli_fetch_assoc($sach_q)) {
      echo "{$s['idsach']}:{name:'" . addslashes($s['tensach']) . "', price:" . $s['dongia'] . "},";
    }
    ?>
  };

  let index = 0;

  function vnd(x) {
    return x.toLocaleString("vi-VN") + " ₫";
  }

  function loadRow(data = {}) {
    const i = index++;
    const row = `
      <tr>
        <td>
          <select class="form-select form-select-sm sel" name="items[${i}][idsach]" required>
            <option value="">-- Chọn sách --</option>
            ${Object.keys(sachList).map(id => 
              `<option value="${id}">${sachList[id].name}</option>`
            ).join('')}
          </select>
        </td>
        <td><input type="text" class="form-control form-control-sm price" readonly></td>
        <td><input type="number" min="1" class="form-control form-control-sm qty" name="items[${i}][soluong]" value="1"></td>
        <td>
          <span class="lineShow">0 ₫</span>
          <input type="hidden" class="line" name="items[${i}][thanhtien]" value="0">
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-danger btn-circle remove"><i class="bx bx-trash"></i></button>
        </td>
      </tr>
    `;

    const tbody = document.querySelector("#tableItems tbody");
    tbody.insertAdjacentHTML("beforeend", row);

    const tr = tbody.lastElementChild;
    const sel = tr.querySelector(".sel");
    const qty = tr.querySelector(".qty");
    const price = tr.querySelector(".price");
    const line = tr.querySelector(".line");
    const lineShow = tr.querySelector(".lineShow");

    sel.addEventListener("change", () => {
      const id = sel.value;
      if (!id) { price.value = ""; line.value = 0; recalc(); return; }

      price.value = vnd(sachList[id].price);
      const tt = qty.value * sachList[id].price;
      line.value = tt;
      lineShow.textContent = vnd(tt);
      recalc();
    });

    qty.addEventListener("input", () => {
      const id = sel.value;
      if (!id) return;

      const tt = qty.value * sachList[id].price;
      line.value = tt;
      lineShow.textContent = vnd(tt);
      recalc();
    });

    tr.querySelector(".remove").addEventListener("click", () => {
      tr.remove();
      recalc();
    });
  }

  function recalc() {
    let total = 0;
    document.querySelectorAll(".line").forEach(l => total += Number(l.value));
    document.getElementById("totalText").textContent = vnd(total);
  }

  document.getElementById("addRow").addEventListener("click", () => loadRow());
  loadRow();
</script>
