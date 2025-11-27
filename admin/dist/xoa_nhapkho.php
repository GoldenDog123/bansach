<?php
require_once 'ketnoi.php';

$id = $_GET['id'];

$sql = "DELETE FROM nhapkho WHERE idnhapkho = $id";
mysqli_query($ketnoi, $sql);

echo "<script>
        alert('Đã xóa mục nhập kho!');
        window.location='index.php?page_layout=danhsachnhapkho';
      </script>";
?>
