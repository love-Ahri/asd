<?php

// Thêm danh mục mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_category'])) {
    if (!$quyenThemDM) {
        $error_msg = "Bạn không có quyền thêm danh mục.";
    } else {
        $tenDM = trim($_POST['tendm'] ?? '');
        $hinhDang = $_POST['hinhdang'] ?? 'to'; 
        $khoiLuong = $_POST['khoiluong'] ?? 'Nhẹ';
        
        if (empty($tenDM)) {
            $error_msg = "Vui lòng nhập tên danh mục.";
        } else {
            $stmt = $db->prepare("select count(*) from danhmuc where tendm = :tendm");
            $stmt->execute([':tendm' => $tenDM]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = "Tên danh mục đã tồn tại.";
            } else {
                try {
                    $newMaDM = sinhMaTuDong($db, 'danhmuc', 'madm', 'CAT');
                    $today = date('Y-m-d');
                    
                    $stmt = $db->prepare("insert into danhmuc (madm, tendm, hinhdang, khoiluong, ngaytao) 
                                          values (:madm, :tendm, :hinhdang, :khoiluong, :today)");
                    $stmt->execute([
                        ':madm' => $newMaDM,
                        ':tendm' => $tenDM,
                        ':hinhdang' => $hinhDang,
                        ':khoiluong' => $khoiLuong,
                        ':today' => $today
                    ]);
                    
                    ghiLichSu($db, $maNV, "Tạo danh mục mới: $tenDM ($newMaDM) - Hình dáng: $hinhDang, Khối lượng: $khoiLuong");
                    $success_msg = "Thêm danh mục mới thành công.";
                } catch (Exception $e) {
                    $error_msg = "Lỗi thêm danh mục: " . $e->getMessage();
                }
            }
        }
    }
}

// Xóa danh mục 
if (isset($_GET['action_delete_category']) || isset($_POST['action_confirm_cascade_delete'])) {
    if (!$quyenXoaDM) {
        $error_msg = "Bạn không có quyền xóa danh mục.";
    } else {
        $targetMaDM = $_GET['action_delete_category'] ?? $_POST['maDM'] ?? '';
        
        try {
            $stmt = $db->prepare("select tendm from danhmuc where madm = :madm");
            $stmt->execute([':madm' => $targetMaDM]);
            $catName = $stmt->fetchColumn();
                        $stmt = $db->prepare("select count(*) from sanpham where madm = :madm");
            $stmt->execute([':madm' => $targetMaDM]);
            $prodCount = $stmt->fetchColumn();
            $isConfirmed = isset($_POST['action_confirm_cascade_delete']);
            
            if ($prodCount > 0 && !$isConfirmed) {
                $_GET['cascade_warning'] = [
                    'maDM' => $targetMaDM,
                    'name' => $catName,
                    'count' => $prodCount
                ];
            } else {
                $stmt = $db->prepare("delete from danhmuc where madm = :madm");
                $stmt->execute([':madm' => $targetMaDM]);
                
                if ($prodCount > 0) {
                    ghiLichSu($db, $maNV, "Xóa danh mục liên đới: $catName ($targetMaDM) và toàn bộ sản phẩm bên trong.");
                    $success_msg = "Đã xóa danh mục $catName và tất cả sản phẩm trực thuộc.";
                } else {
                    ghiLichSu($db, $maNV, "Xóa danh mục trống: $catName ($targetMaDM)");
                    $success_msg = "Đã xóa danh mục $catName.";
                }
            }
        } catch (Exception $e) {
            $error_msg = "Lỗi khi xử lý xóa danh mục: " . $e->getMessage();
        }
    }
}

// Thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_product'])) {
    if (!$quyenThemSP) {
        $error_msg = "Bạn không có quyền thêm sản phẩm.";
    } else {
        $maSP = trim($_POST['masp'] ?? '');
        $tenSP = trim($_POST['tensp'] ?? '');
        $maDM = $_POST['madm'] ?? '';
        $donGia = floatval($_POST['dongia'] ?? 0);
        $soLuong = intval($_POST['soluong'] ?? 0);
          
        if (empty($maSP) || empty($tenSP) || empty($maDM)) {
            $error_msg = "Vui lòng nhập đầy đủ mã, tên sản phẩm và chọn danh mục.";
        } elseif ($donGia < 0 || $soLuong < 0) {
            $error_msg = "Đơn giá và số lượng phải lớn hơn hoặc bằng 0.";
        } else {
            $stmt = $db->prepare("select count(*) from sanpham where masp = :masp");
            $stmt->execute([':masp' => $maSP]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = "Mã sản phẩm đã tồn tại. Vui lòng nhập mã khác.";
            } else {
                try {
                    $today = date('Y-m-d');
                    $stmt = $db->prepare("insert into sanpham (masp, tensp, madm, dongia, soluong, ngaytao) 
                                          values (:masp, :tensp, :madm, :dongia, :soluong, :today)");
                    $stmt->execute([
                        ':masp' => $maSP,
                        ':tensp' => $tenSP,
                        ':madm' => $maDM,
                        ':dongia' => $donGia,
                        ':soluong' => $soLuong,
                        ':today' => $today
                    ]);
                    
                    ghiLichSu($db, $maNV, "Thêm sản phẩm mới: $tenSP ($maSP) vào danh mục $maDM");
                    $success_msg = "Thêm sản phẩm thành công.";
                } catch (Exception $e) {
                    $error_msg = "Lỗi hệ thống khi thêm sản phẩm: " . $e->getMessage();
                }
            }
        }
    }
}

// Sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit_product'])) {
    if (!$quyenSuaSP) {
        $error_msg = "Bạn không có quyền sửa sản phẩm.";
    } else {
        $maSP = $_POST['masp'] ?? '';
        $tenSP = trim($_POST['tensp'] ?? '');
        $maDM = $_POST['madm'] ?? '';
        $donGia = floatval($_POST['dongia'] ?? 0);
        $soLuong = intval($_POST['soluong'] ?? 0);
        
        if (empty($tenSP) || empty($maDM)) {
            $error_msg = "Vui lòng nhập tên sản phẩm và chọn danh mục.";
        } elseif ($donGia < 0 || $soLuong < 0) {
            $error_msg = "Đơn giá và số lượng phải lớn hơn hoặc bằng 0.";
        } else {
            try {
                $stmt = $db->prepare("update sanpham set tensp = :tensp, madm = :madm, dongia = :dongia, soluong = :soluong where masp = :masp");
                $stmt->execute([
                    ':tensp' => $tenSP,
                    ':madm' => $maDM,
                    ':dongia' => $donGia,
                    ':soluong' => $soLuong,
                    ':masp' => $maSP
                ]);
                
                ghiLichSu($db, $maNV, "Cập nhật sản phẩm $maSP: Tên: $tenSP, Giá: $donGia, SL: $soLuong, Danh mục: $maDM");
                $success_msg = "Cập nhật sản phẩm thành công.";
            } catch (Exception $e) {
                $error_msg = "Lỗi hệ thống cập nhật sản phẩm: " . $e->getMessage();
            }
        }
    }
}

//  Xóa lẻ
if (isset($_GET['action_delete_product'])) {
    if (!$quyenXoaSP) {
        $error_msg = "Bạn không có quyền xóa sản phẩm.";
    } else {
        $targetMaSP = $_GET['action_delete_product'];
        try {
            $stmt = $db->prepare("delete from sanpham where masp = :masp");
            $stmt->execute([':masp' => $targetMaSP]);
            
            ghiLichSu($db, $maNV, "Xóa sản phẩm $targetMaSP");
            $success_msg = "Đã xóa sản phẩm $targetMaSP.";
        } catch (Exception $e) {
            $error_msg = "Lỗi xóa sản phẩm: " . $e->getMessage();
        }
    }
}

//Xóa nhiều sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete_products'])) {
    if (!$quyenXoaSP) {
        $error_msg = "Bạn không có quyền xóa sản phẩm.";
    } else {
        $selectedProds = $_POST['selected_products'] ?? [];
        if (empty($selectedProds)) {
            $error_msg = "Không có sản phẩm nào được chọn để xóa.";
        } else {
            try {
                $placeholders = implode(',', array_fill(0, count($selectedProds), '?'));
                $stmt = $db->prepare("delete from sanpham where masp in ($placeholders)");
                $stmt->execute(array_values($selectedProds));
                
                ghiLichSu($db, $maNV, "Xóa hàng loạt sản phẩm: " . implode(', ', $selectedProds));
                $success_msg = "Đã xóa vĩnh viễn " . count($selectedProds) . " sản phẩm được chọn.";
            } catch (Exception $e) {
                $error_msg = "Lỗi xóa hàng loạt sản phẩm: " . $e->getMessage();
            }
        }
    }
}


// Danh sách danh mục
$catQuery = "select dm.*, (select count(*) from sanpham where madm = dm.madm) as product_count from danhmuc dm where 1=1";
$catParams = [];

if (!empty($_GET['filter_shape'])) {
    $catQuery .= " and dm.hinhdang = :shape";
    $catParams[':shape'] = $_GET['filter_shape'];
}
if (!empty($_GET['filter_weight'])) {
    $catQuery .= " and dm.khoiluong = :weight";
    $catParams[':weight'] = $_GET['filter_weight'];
}
$catQuery .= " order by dm.madm asc";

$stmtCats = $db->prepare($catQuery);
$stmtCats->execute($catParams);
$categoriesList = $stmtCats->fetchAll();

// Danh mục
$selectedCategory = $_GET['selected_category'] ?? '';
if (empty($selectedCategory) && !empty($categoriesList)) {
    $selectedCategory = $categoriesList[0]['madm'];
}

// Tên danh mục
$selectedCatName = '';
if ($selectedCategory) {
    $stmt = $db->prepare("select tendm from danhmuc where madm = :madm");
    $stmt->execute([':madm' => $selectedCategory]);
    $selectedCatName = $stmt->fetchColumn();
}

// chi tiết sản phẩm
$vProd = null;
if (isset($_GET['view_product']) && $quyenXemSP) {
    $vMaSP = $_GET['view_product'];
    $stmt = $db->prepare("select s.*, d.tendm from sanpham s join danhmuc d on s.madm = d.madm where s.masp = :masp");
    $stmt->execute([':masp' => $vMaSP]);
    $vProd = $stmt->fetch();
}

// Chi tiết sản phẩm để sửa
$eProd = null;
if (isset($_GET['edit_product']) && $quyenSuaSP) {
    $eMaSP = $_GET['edit_product'];
    $stmt = $db->prepare("select * from sanpham where masp = :masp");
    $stmt->execute([':masp' => $eMaSP]);
    $eProd = $stmt->fetch();
}

// Danh sách sản phẩm
$prodsList = [];
if ($selectedCategory && $quyenXemSP) {
    $prodQuery = "select * from sanpham where madm = :madm";
    $prodParams = [':madm' => $selectedCategory];
    
    $search = trim($_GET['search_query'] ?? '');
    if ($search !== '') {
        $prodQuery .= " and (masp like :search or tensp like :search)";
        $prodParams[':search'] = '%' . $search . '%';
    }
    
    $prodQuery .= " order by masp asc";
    $stmtProds = $db->prepare($prodQuery);
    $stmtProds->execute($prodParams);
    $prodsList = $stmtProds->fetchAll();
}

// Tất cả danh mục cho dropdown selector
$allCatsList = $db->query("select madm, tendm from danhmuc order by madm asc")->fetchAll();
?>