<?php
$ketnoi= mysqli_connect('localhost','root','','qlthuvien');
if($ketnoi){
    mysqli_query($ketnoi, "SET NAMES 'UTF8'");
    echo "Kết nối thành công";

}
else{
    echo"Kết nối không thành công";
}
?>