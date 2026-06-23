<?php
// Đường dẫn tới file cơ sở dữ liệu SQLite và file kịch bản SQL
$dbFile = __DIR__ . '/database.sqlite';
$sqlFile = __DIR__ . '/db.sql';


function initDatabase($db, $sqlFile) {
    if (!file_exists($sqlFile)) {
        throw new Exception("Không tìm thấy cơ sở dữ liệu");
    }
    $sqlContent = file_get_contents($sqlFile);
    $db->exec($sqlContent);
}

try {
    $db = new PDO("sqlite:" . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("PRAGMA foreign_keys = ON;");

    initDatabase($db, $sqlFile);

    echo "Khởi tạo cơ sở dữ liệu thành công ";
} catch (PDOException $e) {
    echo "Lỗi cơ sở dữ liệu: " . $e->getMessage();
} catch (Exception $e) {
    echo "Lỗi hệ thống: " . $e->getMessage();
}
?>