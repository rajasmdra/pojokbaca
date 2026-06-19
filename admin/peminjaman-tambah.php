<?php
session_start();
// 1. Proteksi Halaman: Pastikan yang masuk adalah admin
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';
// Ambil ID Admin dari session yang sedang login untuk menyelesaikan masalah Foreign Key
$id_admin_pinjam = $_SESSION['id_admin']; 

// 3. Ambil data anggota yang aktif (approved)
$sql_anggota = "SELECT id_anggota, nama_lengkap, email FROM anggota WHERE status_akun = 'approved' ORDER BY nama_lengkap ASC";
$query_anggota = mysqli_query($mysqli, $sql_anggota);

// 4. Ambil data buku yang stoknya tersedia (> 0)
$sql_buku = "SELECT id_buku, judul, stok_tersedia FROM buku WHERE stok_tersedia > 0 ORDER BY judul ASC";
$query_buku = mysqli_query($mysqli, $sql_buku);

// Hitung Anggota Baru yang berstatus 'pending' (Butuh Persetujuan)
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// 5. Proses Insert Data ketika Form di-submit
$pesan_sukses = "";
$pesan_gagal  = "";

if (isset($_POST['submit'])) {
    $id_anggota       = $_POST['id_anggota'];
    $id_buku          = $_POST['id_buku'];
    $tgl_pinjam       = $_POST['tgl_pinjam'];
    $tgl_jatuh_tempo  = $_POST['tgl_jatuh_tempo'];
    $status           = 'dipinjam';

    // Validasi apakah ID Anggota dan ID Buku benar-benar terpilih dari datalist
    if (empty($id_anggota) || empty($id_buku) || empty($tgl_pinjam) || empty($tgl_jatuh_tempo)) {
        $pesan_gagal = "Semua kolom form wajib diisi! Pastikan Anda memilih anggota dan buku dari daftar pencarian yang tersedia.";
    } else {
        mysqli_begin_transaction($mysqli);

        try {
            // PERBAIKAN DI SINI: Menyertakan kolom 'id_admin_pinjam' ke dalam query SQL
            $sql_insert = "INSERT INTO peminjaman (id_anggota, id_buku, id_admin_pinjam, tgl_pinjam, tgl_jatuh_tempo, status) 
                           VALUES ('$id_anggota', '$id_buku', '$id_admin_pinjam', '$tgl_pinjam', '$tgl_jatuh_tempo', '$status')";
            $insert_proses = mysqli_query($mysqli, $sql_insert);

            // 2. Kurangi stok buku
            $sql_update_stok = "UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id_buku = '$id_buku'";
            $update_proses = mysqli_query($mysqli, $sql_update_stok);

            if ($insert_proses && $update_proses) {
                mysqli_commit($mysqli);
                $_SESSION['sukses_pinjam'] = "Transaksi peminjaman berhasil dicatat!";
                header("Location: peminjaman.php");
                exit;
            } else {
                throw new Exception("Gagal mengeksekusi query sirkulasi.");
            }
        } catch (Exception $e) {
            mysqli_rollback($mysqli);
            $pesan_gagal = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Tambah Peminjaman</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php">
          <span class="brand-icon"><i class="bi bi-book-half"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Administrator</span>
          </span>
        </a>
      </div>
      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
        <a class="nav-link" href="katalog.php"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Data Buku</span></a>
        <a class="nav-link" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people"></i></span><span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link active" href="peminjaman.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Peminjaman</span></a>
        <a class="nav-link" href="denda.php"><span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Data Denda</span></a>
        
        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear"></i></span><span class="nav-text">Kelola</span>
          </a>
          <div class="collapse ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small" href="penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>

        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
      </nav>

      <div class="sidebar-user d-none">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
      </div>
      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
      </div>
    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span><span></span><span></span>
          </button>

          <div class="navbar-actions ms-auto">
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_admin); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          
          <div class="page-heading d-flex align-items-center gap-3">
            <span class="page-icon bg-primary text-white"><i class="bi bi-plus-circle-fill"></i></span>
            <div>
              <p class="eyebrow mb-1">Sirkulasi Perpustakaan</p>
              <h1 class="h3 mb-1">Form Peminjaman Baru</h1>
              <p class="text-muted mb-0">Input data sirkulasi buku dengan dropdown pencarian instan.</p>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-lg-8 col-xl-7">
              
              <?php if (!empty($pesan_gagal)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                  <i class="bi bi-exclamation-octagon-fill me-2"></i> <?= $pesan_gagal; ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <div class="card border-0 shadow-sm rounded-3 p-4">
                <form action="" method="POST" autocomplete="off">
                  
                  <div class="mb-4">
                    <label for="search_anggota" class="form-label fw-semibold">Nama Anggota / Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                      <input type="text" id="search_anggota" class="form-control" placeholder="Ketik nama atau email untuk mencari..." list="list_anggota" required>
                    </div>
                    <input type="hidden" id="id_anggota" name="id_anggota" required>
                    <datalist id="list_anggota">
                      <?php while($row_agt = mysqli_fetch_assoc($query_anggota)): ?>
                        <option data-id="<?= $row_agt['id_anggota']; ?>" value="<?= htmlspecialchars($row_agt['nama_lengkap']); ?> (<?= htmlspecialchars($row_agt['email']); ?>)"></option>
                      <?php endwhile; ?>
                    </datalist>
                    <div class="form-text text-muted">Ketik kata kunci lalu klik opsi nama yang muncul.</div>
                  </div>

                  <div class="mb-4">
                    <label for="search_buku" class="form-label fw-semibold">Judul Buku <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text bg-light text-muted"><i class="bi bi-book"></i></span>
                      <input type="text" id="search_buku" class="form-control" placeholder="Ketik judul buku untuk mencari..." list="list_buku" required>
                    </div>
                    <input type="hidden" id="id_buku" name="id_buku" required>
                    <datalist id="list_buku">
                      <?php while($row_bku = mysqli_fetch_assoc($query_buku)): ?>
                        <option data-id="<?= $row_bku['id_buku']; ?>" value="<?= htmlspecialchars($row_bku['judul']); ?> (Buku Tersedia: <?= $row_bku['stok_tersedia']; ?>)"></option>
                      <?php endwhile; ?>
                    </datalist>
                    <div class="form-text text-muted">Ketik kata kunci judul buku lalu pilih dari daftar tersemat.</div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="tgl_pinjam" class="form-label fw-semibold">Tanggal Pinjam <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="tgl_pinjam" name="tgl_pinjam" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="tgl_jatuh_tempo" class="form-label fw-semibold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo" required>
                      <div class="form-text text-muted">Otomatis diset +7 hari dari tanggal pinjam.</div>
                    </div>
                  </div>

                  <hr class="my-4 opacity-25">

                  <div class="d-flex align-items-center gap-2">
                    <button type="submit" name="submit" class="btn btn-primary px-4 fw-medium">
                      <i class="bi bi-save me-1"></i> Simpan Transaksi
                    </button>
                    <a href="peminjaman.php" class="btn btn-light px-4 border text-secondary">Batal</a>
                  </div>

                </form>
              </div>

            </div>

            <div class="col-lg-4 col-xl-5 mt-4 mt-lg-0">
              <div class="alert alert-info border-0 shadow-sm p-4 rounded-3 h-100">
                <h5 class="alert-heading fw-bold d-flex align-items-center gap-2 mb-3">
                  <i class="bi bi-info-circle-fill"></i> Aturan Sirkulasi
                </h5>
                <ul class="mb-0 ps-3">
                  <li class="mb-2">Stok buku otomatis akan terpotong sejumlah <strong>1 (satu)</strong> eks ketika form ini disimpan.</li>
                  <li class="mb-2">Anggota yang namanya tidak keluar berarti belum divalidasi akunnya (status masih <em>pending/rejected</em>).</li>
                  <li class="mb-2">Secara default, durasi peminjaman diatur selama <strong>7 hari</strong> ke depan.</li>
                  <li>Pastikan kondisi fisik buku diperiksa sebelum diberikan kepada peminjam.</li>
                </ul>
              </div>
            </div>
          </div>

        </div>
      </main>

      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4"><span>Copyright 2026 PojokBaca. All rights reserved.</span></div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
  
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // 1. Logika Sinkronisasi Input Pencarian ke Hidden Input ID Anggota
      const searchAnggota = document.getElementById('search_anggota');
      const idAnggotaHidden = document.getElementById('id_anggota');
      const listAnggota = document.getElementById('list_anggota');

      searchAnggota.addEventListener('input', function() {
        const options = listAnggota.querySelectorAll('option');
        idAnggotaHidden.value = ''; // Reset nilai default
        for (let option of options) {
          if (option.value === searchAnggota.value) {
            idAnggotaHidden.value = option.getAttribute('data-id');
            break;
          }
        }
      });

      // 2. Logika Sinkronisasi Input Pencarian ke Hidden Input ID Buku
      const searchBuku = document.getElementById('search_buku');
      const idBukuHidden = document.getElementById('id_buku');
      const listBuku = document.getElementById('list_buku');

      searchBuku.addEventListener('input', function() {
        const options = listBuku.querySelectorAll('option');
        idBukuHidden.value = ''; // Reset nilai default
        for (let option of options) {
          if (option.value === searchBuku.value) {
            idBukuHidden.value = option.getAttribute('data-id');
            break;
          }
        }
      });

      // 3. Logika Hitung Tanggal Jatuh Tempo Otomatis (+7 Hari)
      const inputPinjam = document.getElementById('tgl_pinjam');
      const inputTempo  = document.getElementById('tgl_jatuh_tempo');

      function hitungJatuhTempo() {
        if (inputPinjam.value) {
          let date = new Date(inputPinjam.value);
          date.setDate(date.getDate() + 7);

          let year  = date.getFullYear();
          let month = String(date.getMonth() + 1).padStart(2, '0');
          let day   = String(date.getDate()).padStart(2, '0');

          inputTempo.value = `${year}-${month}-${day}`;
        }
      }

      hitungJatuhTempo(); // Jalankan saat halaman di-load awal
      inputPinjam.addEventListener('change', hitungJatuhTempo); // Jalankan saat tanggal pinjam diubah
    });
  </script>
</body>
</html>