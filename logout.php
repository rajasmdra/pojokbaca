<?php
// 1. Wajib jalankan session_start() untuk mengenali session yang sedang aktif
session_start();

// 2. Bersihkan semua variabel session yang tersimpan
$_SESSION = array();

// 3. Hapus cookie session di browser agar benar-benar bersih dan aman
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan data session yang ada di server
session_destroy();

// 5. Alihkan pengguna kembali ke halaman utama / form login user
header("Location: index.php");
exit;
?>