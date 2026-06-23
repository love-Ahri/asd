<?php
$totalUsers = $db->query("select count(*) from nhanvien")->fetchColumn();
$totalCats = $db->query("select count(*) from danhmuc")->fetchColumn();
$totalProds = $db->query("select count(*) from sanpham")->fetchColumn();
$totalQty = $db->query("select sum(soluong) from sanpham")->fetchColumn();
$totalReports = $db->query("select count(*) from baocao")->fetchColumn();
?>