<?php
session_start();
// 1. Proteksi Halaman: Jika tidak ada session login_admin, tendang kembali ke login (index.php)
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

// 3. Ambil parameter ID Buku dari URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_buku = mysqli_real_escape_string($mysqli, $_GET['id']);

    // 4. [Opsional namun Penting] Cek apakah buku sedang dipinjam oleh anggota
    // Ini untuk mencegah error foreign key atau data relasi yang menggantung (orphan data)
    $query_cek_pinjam = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_buku = '$id_buku' AND status = 'dipinjam'");
    $data_cek = mysqli_fetch_assoc($query_cek_pinjam);
    $sedang_dipinjam = $data_cek['total'] ?? 0;

    if ($sedang_dipinjam > 0) {
        // Jika buku sedang aktif dipinjam, batalkan proses hapus demi integritas data
        echo "<script>
                alert('Gagal menghapus! Buku ini tidak dapat dihapus karena sedang dipinjam oleh anggota.');
                window.location.href = 'buku.php?id=" . $id_buku . "';
              </script>";
        exit;
    }

    // 5. Jalankan Query Delete jika validasi di atas lolos
    $sql_delete = "DELETE FROM buku WHERE id_buku = '$id_buku'";

    if (mysqli_query($mysqli, $sql_delete)) {
        echo "<script>
                alert('Buku berhasil dihapus dari katalog!');
                window.location.href = 'katalog.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Gagal menghapus data dari database: " . mysqli_error($mysqli) . "');
                window.location.href = 'buku.php?id=" . $id_buku . "';
              </script>";
        exit;
    }

} else {
    // Jika tidak ada ID di URL, kembalikan langsung ke katalog
    header("Location: katalog.php");
    exit;
}
?>