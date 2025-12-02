<?php
require_once('ketnoi.php');
session_start();

// ===== THÔNG TIN NGƯỜI DÙNG ĐĂNG NHẬP =====
$logged_name = $_SESSION['hoten'] ?? '';
$logged_email = $_SESSION['email'] ?? '';

// ===== THÔNG TIN SÁCH =====
$selected_books = [];
if (isset($_GET['idsach'])) {
  $ids = [(int)$_GET['idsach']];
} elseif (isset($_GET['ids'])) {
  $ids = array_map('intval', explode(',', $_GET['ids']));
} else {
  $ids = [];
}

if (!empty($ids)) {
  $id_str = implode(',', $ids);
  $q = mysqli_query($ketnoi, "
        SELECT sach.idsach, sach.tensach, tacgia.tentacgia, loaisach.tenloaisach 
        FROM sach 
        LEFT JOIN tacgia ON sach.idtacgia = tacgia.idtacgia 
        LEFT JOIN loaisach ON sach.idloaisach = loaisach.idloaisach 
        WHERE sach.idsach IN ($id_str)
    ");
  while ($r = mysqli_fetch_assoc($q)) {
    $selected_books[] = $r;
  }
}

// ====== XỬ LÝ GỬI FORM ======
$message_form = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hoten = trim($_POST['hoten']);
  $email = trim($_POST['email']);
  $book_ids = $_POST['book_ids'] ?? [];
  $quantities = $_POST['soluong'] ?? [];
  $ngaydat = date('Y-m-d');

  if (empty($hoten) || empty($email) || empty($book_ids)) {
    $message_form = '<div class="alert alert-danger">⚠️ Vui lòng nhập đầy đủ thông tin và chọn ít nhất 1 sách.</div>';
  } else {

    // Tìm người dùng
    $stmt_user = mysqli_prepare($ketnoi, "SELECT idnguoidung FROM nguoidung WHERE email=?");
    mysqli_stmt_bind_param($stmt_user, 's', $email);
    mysqli_stmt_execute($stmt_user);
    mysqli_stmt_bind_result($stmt_user, $uid);

    if (mysqli_stmt_fetch($stmt_user)) {
      $idnguoidung = $uid;
    } else {
      $idnguoidung = null;
    }
    mysqli_stmt_close($stmt_user);

    if (!$idnguoidung) {
      $message_form = '<div class="alert alert-danger">❌ Không tìm thấy tài khoản người dùng.</div>';
    } else {

      // 🔥 1) Tạo đơn hàng
      $stmt_don = mysqli_prepare($ketnoi, "
        INSERT INTO donhang (idnguoidung, ngaydat, trangthai)
        VALUES (?, ?, 'cho_xu_ly')
      ");
      mysqli_stmt_bind_param($stmt_don, 'is', $idnguoidung, $ngaydat);
      mysqli_stmt_execute($stmt_don);
      $iddonhang = mysqli_insert_id($ketnoi);
      mysqli_stmt_close($stmt_don);

      // 🔥 2) Thêm chi tiết đơn hàng
      $inserted = 0;

      foreach ($book_ids as $idsach) {
        $sl = max(1, intval($quantities[$idsach] ?? 1));

        $stmt_ct = mysqli_prepare($ketnoi, "
          INSERT INTO donhang_chitiet (iddonhang, idsach, soluong)
          VALUES (?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt_ct, 'iii', $iddonhang, $idsach, $sl);

        if (mysqli_stmt_execute($stmt_ct)) {
          $inserted++;
          // Trừ kho
          mysqli_query($ketnoi, "
            UPDATE sach 
            SET soluong = soluong - $sl 
            WHERE idsach = $idsach AND soluong >= $sl
          ");
        }

        mysqli_stmt_close($stmt_ct);
      }

      if ($inserted > 0) {
        $message_form = '<div class="alert alert-success">✅ Đặt hàng thành công! Mã đơn: ' . $iddonhang . '</div>';
      } else {
        $message_form = '<div class="alert alert-danger">❌ Không thể tạo đơn hàng.</div>';
      }
    }
  }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="shortcut icon" href="images/Book.png" type="image/png">
  <title>Bán sách</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/book.css">
  <link rel="stylesheet" href="css/footer.css">
</head>

<body>
  <?php include 'header.php'; ?>
  <!-- ===== FORM MƯỢN SÁCH ===== -->
  <section class="book_section py-5">
    <div class="container">
      <div class="card p-4 shadow-lg border-0" style="border-radius: 15px;">
        <h3 class="mb-4 text-center text-warning">
          <i class="fa fa-shopping-cart me-2"></i> Xác nhận đơn hàng
        </h3>

        <form method="POST">
          <!-- Họ tên -->
          <div class="form-group mb-3">
            <label>Họ và tên</label>
            <input type="text" name="hoten" class="form-control bg-dark text-white border-secondary"
              value="<?php echo htmlspecialchars($logged_name); ?>"
              placeholder="Nhập họ và tên..." required>
          </div>

          <!-- Email -->
          <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control bg-dark text-white border-secondary"
              value="<?php echo htmlspecialchars($logged_email); ?>"
              placeholder="Nhập email của bạn..." required>
          </div>

          <!-- Ngày mượn -->
          <div class="form-group mb-3">
            <label>Ngày đặt</label>
            <input type="date" name="ngaymuon" class="form-control bg-dark text-white border-secondary"
              value="<?php echo date('Y-m-d'); ?>" readonly>
          </div>

          <!-- Danh sách sách đã chọn -->
          <div class="form-group mb-3">
            <label>📚 Danh sách sách bạn sẽ mua:</label>
            <ul class="book-list list-unstyled bg-dark text-white p-3 rounded">
              <?php if (!empty($selected_books)): ?>
                <?php foreach ($selected_books as $b): ?>
                  <li class="py-2 border-bottom border-secondary">
                    <i class="fa fa-book me-2 text-warning"></i>
                    <b><?php echo htmlspecialchars($b['tensach']); ?></b>
                    — <small><?php echo htmlspecialchars($b['tentacgia']); ?> (<?php echo htmlspecialchars($b['tenloaisach']); ?>)</small>

                    <input type="hidden" name="book_ids[]" value="<?php echo $b['idsach']; ?>">

                    <input type="number"
                      name="soluong[<?php echo $b['idsach']; ?>]"
                      class="form-control bg-dark text-white border-secondary mt-2"
                      value="1" min="1" required>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li class="text-center py-3 text-muted">
                  <i class="fa fa-inbox me-2"></i> Giỏ hàng trống
                </li>
              <?php endif; ?>
            </ul>
          </div>

          <!-- Nút xác nhận -->
          <div class="text-center mt-4">
            <button type="submit" class="btn btn-warning px-5 py-2 fw-bold rounded-pill">
              💳 Thanh toán
            </button>
          </div>
        </form>

        <!-- Hiển thị thông báo -->
        <div class="mt-4">
          <?php echo $message_form; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== MODAL HÓA ĐƠN ===== -->
  <div id="invoiceModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content bg-dark text-white" style="border: 1px solid #ffc107;">
        <div class="modal-header border-warning">
          <h5 class="modal-title text-warning" id="invoiceModalLabel">
            <i class="fa fa-receipt"></i> HÓA ĐƠN THANH TOÁN
          </h5>
          <button type="button" class="btn-close btn-close-white" onclick="closeInvoiceModal()" aria-label="Close"></button>
        </div>

        <div class="modal-body" id="invoiceContent">
          <!-- Nội dung hóa đơn sẽ được thêm bằng JavaScript -->
        </div>

        <div class="modal-footer border-warning">
          <button type="button" class="btn btn-success" id="paymentBtn" onclick="processPayment()">
            <i class="fa fa-credit-card"></i> Thanh toán
          </button>
          <button type="button" class="btn btn-warning" onclick="printInvoice()">
            <i class="fa fa-print"></i> In hóa đơn
          </button>
          <button type="button" class="btn btn-secondary" onclick="closeAndGoHome()">
            <i class="fa fa-times"></i> Đóng
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="js/bootstrap.js"></script>
  <script>
    // Load cart from localStorage on page load
    document.addEventListener('DOMContentLoaded', () => {
      const cart = JSON.parse(localStorage.getItem('cart')) || [];
      
      const form = document.querySelector('form');
      if (!form) return;

      if (cart.length > 0) {
        // Clear existing book fields
        document.querySelectorAll('input[name^="book_ids"]').forEach(el => el.remove());
        document.querySelectorAll('input[name^="soluong"]').forEach(el => el.remove());

        // Add cart items to form
        cart.forEach(item => {
          const bookIdInput = document.createElement('input');
          bookIdInput.type = 'hidden';
          bookIdInput.name = 'book_ids[]';
          bookIdInput.value = item.idsach;
          form.appendChild(bookIdInput);

          const qtyInput = document.createElement('input');
          qtyInput.type = 'hidden';
          qtyInput.name = `soluong[${item.idsach}]`;
          qtyInput.value = item.soluong;
          form.appendChild(qtyInput);
        });

        // Update selected books display
        const bookList = document.querySelector('.book-list');
        if (bookList) {
          let html = '';
          let total = 0;
          
          cart.forEach(item => {
            const itemTotal = item.dongia * item.soluong;
            total += itemTotal;
            
            html += `
              <li class="py-2 border-bottom border-secondary">
                <i class="fa fa-book me-2 text-warning"></i>
                <b>${item.tensach}</b>
                <small class="text-muted">${item.soluong} × ${item.dongia.toLocaleString()}₫ = ${itemTotal.toLocaleString()}₫</small>
              </li>
            `;
          });
          
          html += `
            <li class="py-3 fw-bold text-warning">
              <i class="fa fa-calculator me-2"></i> Tổng cộng: ${total.toLocaleString()}₫
            </li>
          `;
          
          bookList.innerHTML = html;
        }
      }

      // ===== XỬ LÝ FORM THANH TOÁN =====
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        try {
          const response = await fetch('book.php', {
            method: 'POST',
            body: formData
          });

          const html = await response.text();
          console.log('Form submitted, waiting for response...');
          
          // Tìm mã đơn hàng từ response (tìm "Mã đơn: XXXXX")
          const match = html.match(/Mã đơn:\s*(\d+)/);
          
          if (match && match[1]) {
            const iddonhang = match[1];
            console.log('Order ID found:', iddonhang);
            
            // Chờ 300ms rồi hiển thị hóa đơn
            setTimeout(async () => {
              await showInvoice(iddonhang);
              
              // Clear localStorage cart
              localStorage.setItem('cart', JSON.stringify([]));
              window.dispatchEvent(new Event('cartUpdated'));
            }, 300);
          } else {
            console.log('No order ID found in response');
            alert('Lỗi: Không thể tạo đơn hàng');
          }
        } catch (error) {
          console.error('Error:', error);
          alert('Lỗi: ' + error.message);
        }
      });
    });

    // ===== HIỂN THỊ INVOICE MODAL =====
    async function showInvoice(iddonhang) {
      try {
        const response = await fetch('xuly_hoadon.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `iddonhang=${iddonhang}`
        });

        const result = await response.json();
        
        if (result.success) {
          const invoice = result.invoice;
          const html = generateInvoiceHTML(invoice);
          
          document.getElementById('invoiceContent').innerHTML = html;
          
          // Lưu order ID vào nút thanh toán
          const paymentBtn = document.getElementById('paymentBtn');
          paymentBtn.dataset.orderId = iddonhang;
          
          // Hiển thị modal
          const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
          modal.show();
        } else {
          alert('Lỗi: ' + result.message);
        }
      } catch (error) {
        alert('Lỗi: ' + error.message);
      }
    }

    // ===== TẠO HTML HÓA ĐƠN =====
    function generateInvoiceHTML(invoice) {
      const branchInfo = invoice.branch_info;
      let itemsHTML = '';
      
      invoice.items.forEach(item => {
        const subtotal = item.dongia * item.soluong;
        itemsHTML += `
          <tr>
            <td>${item.tensach}</td>
            <td class="text-center">${item.soluong}</td>
            <td class="text-end">${Number(item.dongia).toLocaleString()}₫</td>
            <td class="text-end">${subtotal.toLocaleString()}₫</td>
          </tr>
        `;
      });

      const html = `
        <div style="padding: 30px; background: #0a0a0a; border-radius: 10px;">
          <!-- Header -->
          <div class="text-center mb-4">
            <h4 class="text-warning mb-2"><i class="fa fa-store"></i> BAN SÁCH</h4>
            <p class="text-muted mb-1">${branchInfo.name}</p>
            <p class="text-muted mb-1">📍 ${branchInfo.address}</p>
            <p class="text-muted mb-1">📞 ${branchInfo.phone} | 📧 ${branchInfo.email}</p>
          </div>

          <hr style="border-color: #ffc107;">

          <!-- Thông tin hóa đơn -->
          <div class="row mb-3">
            <div class="col-6">
              <p><strong>Mã hóa đơn:</strong> <span class="text-warning">#${invoice.iddonhang}</span></p>
              <p><strong>👤 Người mua:</strong> ${invoice.hoten}</p>
              <p><strong>📧 Email:</strong> ${invoice.email}</p>
            </div>
            <div class="col-6 text-end">
              <p><strong>📅 Ngày mua:</strong> ${invoice.ngaydat_formatted}</p>
              <p><strong>⏰ Thời gian:</strong> ${invoice.thoigian_formatted}</p>
            </div>
          </div>

          <hr style="border-color: #ffc107;">

          <!-- Bảng chi tiết -->
          <table class="table table-dark table-borderless" style="margin-bottom: 20px;">
            <thead style="border-bottom: 2px solid #ffc107;">
              <tr>
                <th>Tên sách</th>
                <th class="text-center">Số lượng</th>
                <th class="text-end">Giá</th>
                <th class="text-end">Thành tiền</th>
              </tr>
            </thead>
            <tbody>
              ${itemsHTML}
            </tbody>
          </table>

          <!-- Tính toán tổng -->
          <div class="row mb-3" style="font-size: 14px;">
            <div class="col-6"></div>
            <div class="col-6">
              <div class="d-flex justify-content-between mb-2">
                <span>Tổng sản phẩm:</span>
                <strong>${invoice.total_items}</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong>${Number(invoice.subtotal).toLocaleString()}₫</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>VAT (10%):</span>
                <strong>${Number(invoice.vat).toLocaleString()}₫</strong>
              </div>
              <div class="d-flex justify-content-between" style="border-top: 2px solid #ffc107; padding-top: 10px;">
                <span class="text-warning"><strong>TOTAL:</strong></span>
                <strong class="text-warning" style="font-size: 18px;">${Number(invoice.total).toLocaleString()}₫</strong>
              </div>
            </div>
          </div>

          <hr style="border-color: #ffc107;">

          <!-- QR Code -->
          <div class="text-center mt-4">
            <p class="text-muted mb-2"><small>Quét QR để xác nhận thanh toán</small></p>
            <img src="${invoice.qr_code_url}" alt="QR Code" style="width: 200px; height: 200px; border: 2px solid #ffc107; padding: 10px; background: #fff; border-radius: 10px;">
          </div>

          <div class="text-center mt-4 text-muted">
            <p><small>Cảm ơn bạn đã mua hàng tại BAN SÁCH</small></p>
            <p><small>Vui lòng giữ lại hóa đơn này để kiểm tra.</small></p>
          </div>
        </div>
      `;

      return html;
    }

    // ===== ĐÓNG MODAL HÓA ĐƠN =====
    function closeInvoiceModal() {
      const modal = bootstrap.Modal.getInstance(document.getElementById('invoiceModal'));
      if (modal) {
        modal.hide();
      }
    }

    // ===== ĐÓNG MODAL VÀ CHUYỂN HƯỚNG VỀ TRANG CHỦ =====
    function closeAndGoHome() {
      $('#invoiceModal').modal('hide');
      setTimeout(function() {
        window.location.href = 'index.php';
      }, 300);
    }

    // ===== IN HÓA ĐƠN =====
    function printInvoice() {
      const content = document.getElementById('invoiceContent').innerHTML;
      const printWindow = window.open('', '', 'height=800,width=800');
      
      let printHTML = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>In hóa đơn</title>';
      printHTML += '<link rel="stylesheet" href="css/bootstrap.css">';
      printHTML += '<style>';
      printHTML += 'body { background: #1a1a1a; color: #fff; font-family: Arial, sans-serif; }';
      printHTML += '@media print {';
      printHTML += 'body { background: #fff; color: #000; }';
      printHTML += '.text-warning { color: #ffc107 !important; }';
      printHTML += '.text-muted { color: #999; }';
      printHTML += 'hr { border-color: #ddd; }';
      printHTML += 'table { background: #fff; }';
      printHTML += '.table-dark { background: #f5f5f5 !important; color: #000; }';
      printHTML += '.btn-close-white { display: none; }';
      printHTML += '}';
      printHTML += '</style>';
      printHTML += '</head><body>';
      printHTML += content;
      printHTML += '<script>window.print(); window.close();<' + '/script>';
      printHTML += '</body></html>';
      
      printWindow.document.write(printHTML);
      printWindow.document.close();
    }

    // ===== XỬ LÝ THANH TOÁN =====
    async function processPayment() {
      const paymentBtn = document.getElementById('paymentBtn');
      const iddonhang = paymentBtn.dataset.orderId;
      
      if (!iddonhang) {
        alert('Lỗi: Không tìm thấy mã đơn hàng');
        return;
      }

      console.log('Processing payment for order:', iddonhang);

      // Vô hiệu hóa nút khi đang xử lý
      paymentBtn.disabled = true;
      paymentBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';

      try {
        const formData = new URLSearchParams();
        formData.append('iddonhang', iddonhang);
        formData.append('trangthai', 'cho_duyet');
        formData.append('ghichu', 'Thanh toán thành công');

        console.log('Sending request with data:', formData.toString());

        const response = await fetch('xuly_capnhat_trangthai_v3.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: formData.toString()
        });

        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Response text:', responseText);

        let result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          console.error('JSON parse error:', parseError);
          alert('❌ Lỗi: Phản hồi từ server không hợp lệ\n' + responseText);
          paymentBtn.disabled = false;
          paymentBtn.innerHTML = '<i class="fa fa-credit-card"></i> Thanh toán';
          return;
        }
        
        if (result.success) {
          alert('✅ Thanh toán thành công!\nĐơn hàng của bạn đã được cập nhật.');
          paymentBtn.classList.remove('btn-success');
          paymentBtn.classList.add('btn-secondary');
          paymentBtn.innerHTML = '<i class="fa fa-check"></i> Đã thanh toán';
          paymentBtn.disabled = true;
        } else {
          alert('❌ Lỗi: ' + result.message);
          paymentBtn.disabled = false;
          paymentBtn.innerHTML = '<i class="fa fa-credit-card"></i> Thanh toán';
        }
      } catch (error) {
        console.error('Error:', error);
        alert('❌ Lỗi xử lý thanh toán: ' + error.message);
        paymentBtn.disabled = false;
        paymentBtn.innerHTML = '<i class="fa fa-credit-card"></i> Thanh toán';
      }
    }
  </script></body>

</html>