<?php
require_once('ketnoi.php');
session_start();

$iddonhang = intval($_GET['iddonhang']);

// Lấy thông tin đơn hàng
$sql = "SELECT d.*, n.hoten, n.email 
        FROM donhang d
        LEFT JOIN nguoidung n ON d.idnguoidung = n.idnguoidung
        WHERE d.iddonhang = $iddonhang";

$dh = mysqli_fetch_assoc(mysqli_query($ketnoi, $sql));

if (!$dh) {
    die("Đơn hàng không tồn tại!");
}

// Lấy chi tiết đơn hàng
$ct = mysqli_query($ketnoi, "
    SELECT c.*, s.tensach, s.hinhanhsach
    FROM donhang_chitiet c
    LEFT JOIN sach s ON c.idsach = s.idsach
    WHERE c.iddonhang = $iddonhang
");
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Hóa đơn #<?php echo $iddonhang; ?></title>

    <link rel="stylesheet" href="css/bootstrap.css">

    <style>
        body {
            padding: 30px;
            font-family: DejaVu Sans, Arial;
        }

        .invoice-box {
            border: 1px solid #ddd;
            padding: 25px;
            border-radius: 10px;
        }

        .invoice-header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .logo {
            width: 80px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-box">

        <div class="d-flex justify-content-between invoice-header">
            <div>
                <h2>📘 HÓA ĐƠN MUA SÁCH</h2>
                <p>Ngày: <?php echo date('d/m/Y H:i', strtotime($dh['ngaydat'])); ?></p>
            </div>
            <div>
                <img src="images/Book.png" class="logo">
            </div>
        </div>

        <h4>👤 Thông tin khách hàng</h4>
        <p>
            <strong>Họ tên:</strong> <?php echo $dh['hoten']; ?><br>
            <strong>Email:</strong> <?php echo $dh['email']; ?><br>
            <strong>Mã đơn hàng:</strong> #<?php echo $iddonhang; ?><br>
            <strong>Trạng thái:</strong> <?php echo $dh['trangthai']; ?>
        </p>

        <h4 class="mt-4">📦 Chi tiết đơn hàng</h4>

        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Hình ảnh</th>
                    <th>Sách</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $tong = 0;
                while ($r = mysqli_fetch_assoc($ct)):
                    $tong += $r['thanhtien'];
                ?>
                    <tr>
                        <td><img src="images/<?php echo $r['hinhanhsach']; ?>" width="60"></td>
                        <td><?php echo $r['tensach']; ?></td>
                        <td><?php echo number_format($r['dongia']); ?> đ</td>
                        <td><?php echo $r['soluong']; ?></td>
                        <td><?php echo number_format($r['thanhtien']); ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3 class="text-end mt-3">
            Tổng tiền: <span class="text-danger"><?php echo number_format($tong); ?> đ</span>
        </h3>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg">🖨 In hóa đơn</button>
        </div>

    </div>

</body>

</html>