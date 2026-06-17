<?php
session_start();
// 1. Proteksi Halaman
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

// 3. Ambil parameter dari URL
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id_anggota = mysqli_real_escape_string($mysqli, $_GET['id']);
    $action     = $_GET['action'];

    if ($action === 'approve') {
        // Update status menjadi approved (Aktif)
        $sql = "UPDATE anggota SET status_akun = 'approved' WHERE id_anggota = '$id_anggota'";
        $msg = "Pendaftaran anggota berhasil disetujui!";
    } 
    elseif ($action === 'reject') {
        // Update status menjadi rejected (Ditolak)
        $sql = "UPDATE anggota SET status_akun = 'rejected' WHERE id_anggota = '$id_anggota'";
        $msg = "Pendaftaran anggota telah ditolak.";
    } 
    elseif ($action === 'delete') {
        // Proteksi: Cek apakah anggota memiliki tanggungan pinjaman aktif sebelum dihapus
        $cek_pinjam = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_anggota = '$id_anggota' AND status = 'dipinjam'");
        $data_cek = mysqli_fetch_assoc($cek_pinjam);
        
        if (($data_cek['total'] ?? 0) > 0) {
            echo "<script>
                    alert('Gagal menghapus! Anggota masih memiliki transaksi peminjaman buku yang belum selesai.');
                    window.location.href = 'anggota.php';
                  </script>";
            exit;
        }

        // Jika aman, lakukan penghapusan permanen
        $sql = "DELETE FROM anggota WHERE id_anggota = '$id_anggota'";
        $msg = "Data anggota berhasil dihapus dari sistem.";
    } 
    else {
        header("Location: anggota.php");
        exit;
    }

    // Jalankan query ke database
    if (mysqli_query($mysqli, $sql)) {
        echo "<script>
                alert('$msg');
                window.location.href = 'anggota.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Gagal memproses data: " . mysqli_error($mysqli) . "');
                window.location.href = 'anggota.php';
              </script>";
        exit;
    }

} else {
    header("Location: anggota.php");
    exit;
}
?>