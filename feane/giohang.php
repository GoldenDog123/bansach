<?php
require_once('ketnoi.php');
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="css/bootstrap.css">

    <style>
        .cart-item-img {
            width: 70px;
            height: 90px;
            object-fit: cover;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            font-size: 18px;
        }

        .cart-card {
            border-radius: 12px;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5 mb-5">
        <h2 class="mb-4">🛒 Giỏ hàng của bạn</h2>

        <div id="cart-container"></div>

        <hr>

        <!-- Form đặt hàng -->
        <div class="card p-4 shadow cart-card">
            <h4 class="mb-3">Thông tin đặt hàng</h4>

            <div id="checkout-error"></div>

            <?php if (!isset($_SESSION['idnguoidung'])): ?>
                <div class="alert alert-info">
                    Bạn chưa đăng nhập — vui lòng nhập thông tin để đặt hàng.
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Họ tên</label>
                        <input type="text" id="hoten" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control">
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Số điện thoại</label>
                    <input type="text" id="sdt" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Địa chỉ nhận hàng</label>
                    <input type="text" id="diachi" class="form-control">
                </div>
            </div>

            <button id="btnCheckout" class="btn btn-success btn-lg mt-3">
                ✔ Đặt hàng
            </button>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        /* ===========================
   LOAD CART
=========================== */
        function loadCart() {
            $.get('cart_api.php?action=get', function(res) {
                if (res.status !== 'ok') {
                    $("#cart-container").html('<div class="alert alert-danger">' + res.message + '</div>');
                    return;
                }

                let html = '';
                if (res.items.length === 0) {
                    html = '<div class="alert alert-info">Giỏ hàng trống.</div>';
                } else {
                    html += `
                <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tên sách</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                    res.items.forEach(item => {
                        html += `
                <tr>
                    <td><img src="images/${item.hinhanhsach}" class="cart-item-img"></td>
                    <td>${item.tensach}</td>
                    <td>${item.dongia.toLocaleString()} đ</td>

                    <td>
                        <button class="btn btn-outline-secondary qty-btn" onclick="updateQty(${item.idsach}, ${item.soluong-1})">-</button>
                        <span class="mx-2">${item.soluong}</span>
                        <button class="btn btn-outline-secondary qty-btn" onclick="updateQty(${item.idsach}, ${item.soluong+1})">+</button>
                    </td>

                    <td>${item.thanhtien.toLocaleString()} đ</td>

                    <td>
                        <button class="btn btn-danger btn-sm" onclick="removeItem(${item.idsach})">X</button>
                    </td>
                </tr>
                `;
                    });

                    html += `
                    </tbody>
                </table>
                </div>

                <h3 class="text-end mt-3">
                    Tổng tiền: <span class="text-danger">${res.total.toLocaleString()} đ</span>
                </h3>
            `;
                }

                $("#cart-container").html(html);
            }, "json");
        }

        /* ===========================
           UPDATE QUANTITY
        =========================== */
        function updateQty(idsach, qty) {
            if (qty <= 0) {
                removeItem(idsach);
                return;
            }

            $.post('cart_api.php?action=update', {
                idsach: idsach,
                soluong: qty
            }, function() {
                loadCart();
            }, "json");
        }

        /* ===========================
           REMOVE ITEM
        =========================== */
        function removeItem(idsach) {
            $.post('cart_api.php?action=remove', {
                idsach: idsach
            }, function() {
                loadCart();
            }, "json");
        }

        /* ===========================
           CHECKOUT
        =========================== */
        $('#btnCheckout').click(function() {
            let payload = {
                diachi: $('#diachi').val(),
                sdt: $('#sdt').val()
            };

            <?php if (!isset($_SESSION['idnguoidung'])): ?>
                payload.hoten = $('#hoten').val();
                payload.email = $('#email').val();
            <?php endif; ?>

            $.post('cart_api.php?action=checkout', payload, function(res) {
                if (res.status === 'ok') {
                    alert("Đặt hàng thành công!");
                    window.location.href = "lichsu_donhang.php?iddonhang=" + res.iddonhang;
                } else {
                    $('#checkout-error').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            }, "json");
        });

        loadCart();
    </script>

</body>

</html>