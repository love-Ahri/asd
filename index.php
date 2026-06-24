<?php
session_start();
// cấu hình kết nối cơ sở dữ liệu
$dbFile = __DIR__ . '/database.sqlite';
try{
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage());
}
// dn vs dx

function ghiLichSu($db, $maNV, $hanhDong) {
    try {
        $stmt = $db->prepare("insert into lichsuhoatdong (manv, hanhdong) values (:manv, :hanhdong)");
        $stmt->execute([
            ':manv' => $maNV,
            ':hanhdong' => $hanhDong
        ]);
    } catch (PDOException $e) {
        error_log("Lỗi ghi lịch sử: " . $e->getMessage());
    }
}

if (isset($_GET['logout'])) {
    if (isset($_SESSION['user'])) {
        ghiLichSu($db, $_SESSION['user']['manv'], 'Đăng xuất');
    }
    unset($_SESSION['user']);
    session_destroy();
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['user'])) {
    require_once __DIR__ . '/module/login.php';
    require_once __DIR__ . '/view/form.login.tpl.html';
    exit;
}

function sinhMaTuDong($db, $bang, $cotKhoaChinh, $tienTo, $doDaiSo = 3) {
    $stmt = $db->prepare("select $cotKhoaChinh from $bang where $cotKhoaChinh like :prefix order by $cotKhoaChinh desc limit 1");
    $stmt->execute([':prefix' => $tienTo . '%']);
    $maxRow = $stmt->fetch();
    
    if ($maxRow) {
        $maxId = $maxRow[$cotKhoaChinh];
        $numPart = substr($maxId, strlen($tienTo));
        $nextNum = intval($numPart) + 1;
    } else {
        $nextNum = 1;
    }
    
    $formatLen = ($tienTo === 'CAT') ? 2 : 3;
    return $tienTo . str_pad($nextNum, $formatLen, '0', STR_PAD_LEFT);
}

$currentUser = $_SESSION['user'];
$chucVu = $currentUser['chucvu'];
$maNV = $currentUser['manv'];

// lấy quyền truy cập
$stmtPQ = $db->prepare("select * from phanquyen where manv = :manv");
$stmtPQ->execute([':manv' => $maNV]);
$freshPQ = $stmtPQ->fetch(PDO::FETCH_ASSOC);
if ($freshPQ) {
    foreach ($freshPQ as $key => $val) {
        if ($key !== 'manv') {
            $currentUser[$key] = $val;
        }
    }
    $_SESSION['user'] = $currentUser;
}
$quyenXemSP  = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenxemsp'] ?? 0);
$quyenThemSP = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenthemsp'] ?? 0);
$quyenThemDM = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenthemdm'] ?? 0);
$quyenSuaSP  = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyensuasp'] ?? 0);
$quyenXoaSP  = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenxoasp'] ?? 0);
$quyenXoaDM  = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenxoadm'] ?? 0);
$quyenXemBC  = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenxembc'] ?? 0);
$quyenThemBC = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenthembc'] ?? 0);
$quyenSuaBC  = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyensuabc'] ?? 0);
$quyenXoaBC  = ($chucVu === 'Admin') ? 1 : intval($currentUser['quyenxoabc'] ?? 0);

$success_msg = '';
$error_msg = '';

// tab hiện tại
$activeTab = $_GET['tab'] ?? 'dashboard';
if ($activeTab === 'users' && $chucVu !== 'Admin') {
    $activeTab = 'dashboard';
}
if ($activeTab === 'reports' && !$quyenXemBC) {
    $activeTab = 'dashboard';
}

// chọn tab
switch ($activeTab) {
    case 'dashboard':
        require_once __DIR__ . '/module/dashboard.php';
        break;
    case 'users':
        if ($chucVu === 'Admin') {
            require_once __DIR__ . '/module/users.php';
        }
        break;
    case 'inventory':
        require_once __DIR__ . '/module/inventory.php';
        break;

}
require_once __DIR__ . '/view/index.tpl.html';
?>