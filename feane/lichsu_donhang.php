<?php
// lichsu_donhang.php
require_once('ketnoi.php');
session_start();

// kiểm tra đăng nhập
if (!isset($_SESSION['idnguoidung']) || !$_SESSION['idnguoidung']) {
    // redirect hoặc hiển thị thông báo
    header('Location: login.php');
    exit;
}

$idnguoidung = intval($_SESSION['idnguoidung']);
$filter_order = isset($_GET['iddonhang']) ? intval($_GET['iddonhang']) : null;

// Nếu admin muốn xem được tất cả, bạn có thể mở thêm điều kiện
// Lấy danh sách đơn hàng của user (hoặc nếu admin lấy tất cả)
$is_admin = false;
$stmtRole = mysqli_prepare($ketnoi, "SELECT vaitro FROM nguoidung WHERE idnguoidung=?");
mysqli_stmt_bind_param($stmtRole, 'i', $idnguoidung);
mysqli_stmt_execute($stmtRole);
mysqli_stmt_bind_result($stmtRole, $vaitro);
if (mysqli_stmt_fetch($stmtRole)) {
    if ($vaitro === 'admin' || $vaitro === 'thuthu') $is_admin = true;
}
mysqli_stmt_close($stmtRole);

// Build orders list
if ($is_admin && $filter_order) {
    $orders_q = mysqli_query($ketnoi, "SELECT * FROM donhang WHERE iddonhang = $filter_order");
} elseif ($is_admin) {
    $orders_q = mysqli_query($ketnoi, "SELECT * FROM donhang ORDER BY ngaydat DESC");
} else {
    $orders_q = mysqli_query($ketnoi, "SELECT * FROM donhang WHERE idnguoidung = $idnguoidung ORDER BY ngaydat DESC");
}
$orders = mysqli_fetch_all($orders_q, MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch sử đơn hàng</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>
        .timeline {
            position: relative;
            padding: 1rem 0;
            list-style: none;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 4px;
            left: 20px;
            background: #e9ecef;
            border-radius: 2px;
        }

        .timeline-item {
            position: relative;
            margin: 0 0 1.5rem 40px;
            padding-left: 1rem;
        }

        .timeline-item .time {
            font-size: .85rem;
            color: #6c757d;
        }

        .badge-status-cho_duyet {
            background: #ffc107;
            color: #000;
        }

        .badge-status-dang_giao {
            background: #17a2b8;
            color: #fff;
        }

        .badge-status-hoan_thanh {
            background: #28a745;
            color: #fff;
        }

        .badge-status-da_huy {
            background: #dc3545;
            color: #fff;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Lịch sử đơn hàng</h2>
            <?php if ($is_admin): ?>
                <form class="form-inline" method="get">
                    <div class="input-group">
                        <input type="number" name="iddonhang" class="form-control" placeholder="Tìm theo id đơn">
                        <div class="input-group-append">
                            <button class="btn btn-primary">Tìm</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">Chưa có đơn hàng nào.</div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="mb-1">Đơn hàng #<?php echo $order['iddonhang']; ?></h5>
                                <small class="text-muted">Ngày đặt: <?php echo $order['ngaydat']; ?></small>
                            </div>
                            <div class="text-right">
                                <span class="badge <?php echo 'badge-status-' . $order['trangthai']; ?> py-2 px-3">
                                    <?php echo str_replace('_', ' ', $order['trangthai']); ?>
                                </span>
                                <div class="text-muted">Tổng: <?php echo number_format($order['tongtien']); ?> đ</div>
                            </div>
                        </div>

                        <hr>

                        <!-- timeline lịch sử trạng thái cho đơn này -->
                        <?php
                        $idd = intval($order['iddonhang']);
                        $stmt = mysqli_prepare($ketnoi, "SELECT trangthai_cu, trangthai_moi, ghichu, ngaycapnhat FROM lichsu_donhang WHERE iddonhang = ? ORDER BY ngaycapnhat ASC");
                        mysqli_stmt_bind_param($stmt, 'i', $idd);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);
                        $hist = mysqli_fetch_all($res, MYSQLI_ASSOC);
                        mysqli_stmt_close($stmt);
                        ?>
                        <?php if (empty($hist)): ?>
                            <p class="text-muted">Chưa có lịch sử thay đổi trạng thái cho đơn này.</p>
                            <div class="small text-muted">Mặc định trạng thái hiện tại: <strong><?php echo $order['trangthai']; ?></strong></div>
                        <?php else: ?>
                            <ul class="timeline">
                                <?php foreach ($hist as $h): ?>
                                    <li class="timeline-item">
                                        <div class="small time"><?php echo $h['ngaycapnhat']; ?></div>
                                        <div class="mt-1">
                                            <span class="badge <?php echo 'badge-status-' . $h['trangthai_moi']; ?>">
                                                <?php echo str_replace('_', ' ', $h['trangthai_moi']); ?>
                                            </span>
                                            <div class="mt-2"><?php echo htmlspecialchars($h['ghichu']); ?></div>
                                            <?php if ($h['trangthai_cu'] !== null): ?>
                                                <div class="text-muted mt-1 small">Từ: <?php echo str_replace('_', ' ', $h['trangthai_cu']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <hr>
                        <!-- Chi tiết đơn hàng -->
                        <h6>Chi tiết đơn</h6>
                        <?php
                        $qct = "SELECT c.*, s.tensach FROM donhang_chitiet c LEFT JOIN sach s ON c.idsach = s.idsach WHERE c.iddonhang = {$order['iddonhang']}";
                        $rct = mysqli_query($ketnoi, $qct);
                        ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên sách</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($rct)): ?>
                                        <tr>
                                            <td><?php echo $row['idsach']; ?></td>
                                            <td><?php echo htmlspecialchars($row['tensach']); ?></td>
                                            <td><?php echo $row['soluong']; ?></td>
                                            <td><?php echo number_format($row['dongia']); ?></td>
                                            <td><?php echo number_format($row['thanhtien']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>

</html>