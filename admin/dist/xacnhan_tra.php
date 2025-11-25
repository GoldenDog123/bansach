<?php
require_once('ketnoi.php');

if (isset($_POST['idmuon'])) {
  $idmuon = intval($_POST['idmuon']);
  $sql = "UPDATE muonsach SET ngaytra_thucte = NOW() WHERE idmuon = $idmuon";
  mysqli_query($ketnoi, $sql);
}

header('Location: index.php?page_layout=danhsachmuonsach');
exit;
?>
