<?php
// giohang.php
require_once('ketnoi.php');
session_start();

header('Content-Type: application/json; charset=utf-8');

function json_error($msg)
{
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

function json_success($data = [])
{
    echo json_encode(array_merge(['status' => 'ok'], $data));
    exit;
}

// Helper: read POST/GET action
$action = $_REQUEST['action'] ?? '';

if (!$action) json_error('No action');

switch ($action) {
    case 'add':
        // params: idsach, soluong
        $idsach = intval($_POST['idsach'] ?? 0);
        $soluong = max(1, intval($_POST['soluong'] ?? 1));
        if (!$idsach) json_error('Invalid book id');

        if (isset($_SESSION['idnguoidung']) && $_SESSION['idnguoidung']) {
            // logged-in: use giohang table
            $idnguoidung = intval($_SESSION['idnguoidung']);
            // check existing
            $stmt = mysqli_prepare($ketnoi, "SELECT soluong FROM giohang WHERE idnguoidung=? AND idsach=?");
            mysqli_stmt_bind_param($stmt, 'ii', $idnguoidung, $idsach);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $oldqty);
            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                $newqty = $oldqty + $soluong;
                $stmt2 = mysqli_prepare($ketnoi, "UPDATE giohang SET soluong=? WHERE idnguoidung=? AND idsach=?");
                mysqli_stmt_bind_param($stmt2, 'iii', $newqty, $idnguoidung, $idsach);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            } else {
                mysqli_stmt_close($stmt);
                $stmt3 = mysqli_prepare($ketnoi, "INSERT INTO giohang (idnguoidung, idsach, soluong) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt3, 'iii', $idnguoidung, $idsach, $soluong);
                mysqli_stmt_execute($stmt3);
                mysqli_stmt_close($stmt3);
            }
            json_success(['message' => 'Added to cart']);
        } else {
            // guest: session cart
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$idsach])) {
                $_SESSION['cart'][$idsach] += $soluong;
            } else {
                $_SESSION['cart'][$idsach] = $soluong;
            }
            json_success(['message' => 'Added to cart (session)']);
        }
        break;

    case 'update':
        // params: idsach, soluong
        $idsach = intval($_POST['idsach'] ?? 0);
        $soluong = intval($_POST['soluong'] ?? 0);
        if (!$idsach) json_error('Invalid book id');

        if (isset($_SESSION['idnguoidung']) && $_SESSION['idnguoidung']) {
            $idnguoidung = intval($_SESSION['idnguoidung']);
            if ($soluong > 0) {
                $stmt = mysqli_prepare($ketnoi, "UPDATE giohang SET soluong=? WHERE idnguoidung=? AND idsach=?");
                mysqli_stmt_bind_param($stmt, 'iii', $soluong, $idnguoidung, $idsach);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                json_success(['message' => 'Cart updated']);
            } else {
                $stmt = mysqli_prepare($ketnoi, "DELETE FROM giohang WHERE idnguoidung=? AND idsach=?");
                mysqli_stmt_bind_param($stmt, 'ii', $idnguoidung, $idsach);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                json_success(['message' => 'Removed from cart']);
            }
        } else {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if ($soluong > 0) {
                $_SESSION['cart'][$idsach] = $soluong;
                json_success(['message' => 'Cart updated (session)']);
            } else {
                unset($_SESSION['cart'][$idsach]);
                json_success(['message' => 'Removed from cart (session)']);
            }
        }
        break;

    case 'remove':
        // params: idsach
        $idsach = intval($_POST['idsach'] ?? 0);
        if (!$idsach) json_error('Invalid book id');

        if (isset($_SESSION['idnguoidung']) && $_SESSION['idnguoidung']) {
            $idnguoidung = intval($_SESSION['idnguoidung']);
            $stmt = mysqli_prepare($ketnoi, "DELETE FROM giohang WHERE idnguoidung=? AND idsach=?");
            mysqli_stmt_bind_param($stmt, 'ii', $idnguoidung, $idsach);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            json_success(['message' => 'Removed']);
        } else {
            if (isset($_SESSION['cart'][$idsach])) {
                unset($_SESSION['cart'][$idsach]);
            }
            json_success(['message' => 'Removed (session)']);
        }
        break;

    case 'get':
        // return cart items with book info and totals
        $items = [];
        $total = 0;
        if (isset($_SESSION['idnguoidung']) && $_SESSION['idnguoidung']) {
            $idnguoidung = intval($_SESSION['idnguoidung']);
            $q = "SELECT g.idsach, g.soluong, s.tensach, s.dongia, s.hinhanhsach
                  FROM giohang g
                  JOIN sach s ON g.idsach = s.idsach
                  WHERE g.idnguoidung = $idnguoidung";
            $res = mysqli_query($ketnoi, $q);
            while ($r = mysqli_fetch_assoc($res)) {
                $thanhtien = intval($r['dongia']) * intval($r['soluong']);
                $total += $thanhtien;
                $items[] = [
                    'idsach' => (int)$r['idsach'],
                    'tensach' => $r['tensach'],
                    'soluong' => (int)$r['soluong'],
                    'dongia' => (int)$r['dongia'],
                    'thanhtien' => $thanhtien,
                    'hinhanhsach' => $r['hinhanhsach']
                ];
            }
            json_success(['items' => $items, 'total' => $total]);
        } else {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $ids = array_keys($_SESSION['cart']);
            if (!empty($ids)) {
                $ids_list = implode(',', array_map('intval', $ids));
                $q = "SELECT idsach, tensach, dongia, hinhanhsach FROM sach WHERE idsach IN ($ids_list)";
                $res = mysqli_query($ketnoi, $q);
                while ($r = mysqli_fetch_assoc($res)) {
                    $qty = intval($_SESSION['cart'][$r['idsach']] ?? 0);
                    $thanhtien = intval($r['dongia']) * $qty;
                    $total += $thanhtien;
                    $items[] = [
                        'idsach' => (int)$r['idsach'],
                        'tensach' => $r['tensach'],
                        'soluong' => $qty,
                        'dongia' => (int)$r['dongia'],
                        'thanhtien' => $thanhtien,
                        'hinhanhsach' => $r['hinhanhsach']
                    ];
                }
            }
            json_success(['items' => $items, 'total' => $total]);
        }
        break;

    case 'checkout':
        // Checkout: if not logged in, accept hoten+email to create user (similar to your code)
        $hoten = trim($_POST['hoten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $diachi = trim($_POST['diachi'] ?? null);
        $sdt = trim($_POST['sdt'] ?? null);

        // prepare cart source
        $cart_items = [];
        if (isset($_SESSION['idnguoidung']) && $_SESSION['idnguoidung']) {
            $idnguoidung = intval($_SESSION['idnguoidung']);
            $q = "SELECT g.idsach, g.soluong, s.dongia, s.soluong as kho FROM giohang g JOIN sach s ON g.idsach=s.idsach WHERE g.idnguoidung=$idnguoidung";
            $res = mysqli_query($ketnoi, $q);
            while ($r = mysqli_fetch_assoc($res)) {
                $cart_items[] = $r;
            }
        } else {
            if (empty($hoten) || empty($email)) json_error('Vui lòng nhập họ tên và email để đặt hàng');
            // try create user
            // check email exists
            $stmt = mysqli_prepare($ketnoi, "SELECT idnguoidung FROM nguoidung WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $uid);
            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                $idnguoidung = (int)$uid;
            } else {
                mysqli_stmt_close($stmt);
                $default_pass = password_hash('12345', PASSWORD_DEFAULT);
                $vaitro = 'hoc_sinh';
                $ins = mysqli_prepare($ketnoi, "INSERT INTO nguoidung (hoten, email, matkhau, vaitro) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($ins, 'ssss', $hoten, $email, $default_pass, $vaitro);
                if (!mysqli_stmt_execute($ins)) {
                    mysqli_stmt_close($ins);
                    json_error('Không thể tạo tài khoản khách hàng');
                }
                $idnguoidung = mysqli_insert_id($ketnoi);
                mysqli_stmt_close($ins);
            }
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                json_error('Giỏ hàng trống');
            }
            $ids = array_keys($_SESSION['cart']);
            $ids_list = implode(',', array_map('intval', $ids));
            $q = "SELECT idsach, dongia, soluong as kho FROM sach WHERE idsach IN ($ids_list)";
            $res = mysqli_query($ketnoi, $q);
            while ($r = mysqli_fetch_assoc($res)) {
                $r['soluong'] = intval($_SESSION['cart'][$r['idsach']]);
                $cart_items[] = $r;
            }
        }

        if (empty($cart_items)) json_error('Giỏ hàng trống');

        // Validate stock & compute total
        $total = 0;
        foreach ($cart_items as $it) {
            $want = intval($it['soluong']);
            $kho = intval($it['kho']);
            if ($want <= 0 || $want > $kho) {
                json_error('Số lượng không hợp lệ cho sách id=' . intval($it['idsach']));
            }
            $total += intval($it['dongia']) * $want;
        }

        // Insert order in transaction
        mysqli_begin_transaction($ketnoi);

        try {
            $ngaydat = date('Y-m-d H:i:s');
            $stmt = mysqli_prepare($ketnoi, "INSERT INTO donhang (idnguoidung, tongtien, trangthai, ngaydat, diachi_nhan, sdt_nhan) VALUES (?, ?, 'cho_duyet', ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iisss', $idnguoidung, $total, $ngaydat, $diachi, $sdt);
            if (!mysqli_stmt_execute($stmt)) throw new Exception('Insert donhang failed');
            $iddonhang = mysqli_insert_id($ketnoi);
            mysqli_stmt_close($stmt);

            // Insert chi tiết + update kho
            $stmt_ct = mysqli_prepare($ketnoi, "INSERT INTO donhang_chitiet (iddonhang, idsach, soluong, dongia, thanhtien) VALUES (?, ?, ?, ?, ?)");
            $stmt_upd = mysqli_prepare($ketnoi, "UPDATE sach SET soluong = soluong - ? WHERE idsach = ?");
            foreach ($cart_items as $it) {
                $idsach = intval($it['idsach']);
                $sol = intval($it['soluong']);
                $dongia = intval($it['dongia']);
                $thanhtien = $dongia * $sol;
                mysqli_stmt_bind_param($stmt_ct, 'iiiii', $iddonhang, $idsach, $sol, $dongia, $thanhtien);
                if (!mysqli_stmt_execute($stmt_ct)) throw new Exception('Insert chi tiết failed');
                mysqli_stmt_bind_param($stmt_upd, 'ii', $sol, $idsach);
                if (!mysqli_stmt_execute($stmt_upd)) throw new Exception('Update stock failed');
            }
            mysqli_stmt_close($stmt_ct);
            mysqli_stmt_close($stmt_upd);

            // Clear giohang (db) or session cart
            if (isset($_SESSION['idnguoidung']) && $_SESSION['idnguoidung']) {
                $idnguoidung = intval($_SESSION['idnguoidung']);
                $stmtc = mysqli_prepare($ketnoi, "DELETE FROM giohang WHERE idnguoidung = ?");
                mysqli_stmt_bind_param($stmtc, 'i', $idnguoidung);
                mysqli_stmt_execute($stmtc);
                mysqli_stmt_close($stmtc);
            } else {
                unset($_SESSION['cart']);
            }

            mysqli_commit($ketnoi);
            json_success(['message' => 'Đặt hàng thành công', 'iddonhang' => $iddonhang]);
        } catch (Exception $ex) {
            mysqli_rollback($ketnoi);
            json_error('Lỗi khi đặt hàng: ' . $ex->getMessage());
        }

        break;

    default:
        json_error('Unknown action');
}
