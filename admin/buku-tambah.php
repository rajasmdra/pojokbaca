<?php
session_start();
// 1. Proteksi Halaman: Jika tidak ada session login_admin, tendang kembali ke login (index.php)
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// 3. Ambil total pendaftaran anggota baru yang berstatus 'pending' untuk indikator badge di sidebar
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// 4. Ambil master data pendukung untuk diletakkan di elemen select/pilihan form
$query_kategori = mysqli_query($mysqli, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$query_penerbit = mysqli_query($mysqli, "SELECT * FROM penerbit ORDER BY nama_penerbit ASC");
$query_rak      = mysqli_query($mysqli, "SELECT * FROM rak ORDER BY nama_rak ASC");

// 5. Proses penyimpanan data ketika form disubmit
$pesan_error = "";
$pesan_sukses = "";

if (isset($_POST['submit'])) {
    $judul          = mysqli_real_escape_string($mysqli, $_POST['judul']);
    $isbn           = mysqli_real_escape_string($mysqli, $_POST['isbn']);
    $id_kategori    = mysqli_real_escape_string($mysqli, $_POST['id_kategori']);
    $id_penerbit    = mysqli_real_escape_string($mysqli, $_POST['id_penerbit']);
    $penulis        = mysqli_real_escape_string($mysqli, $_POST['penulis']);
    $tahun_terbit   = mysqli_real_escape_string($mysqli, $_POST['tahun_terbit']);
    $jumlah_halaman = mysqli_real_escape_string($mysqli, $_POST['jumlah_halaman']);
    $stok_total     = mysqli_real_escape_string($mysqli, $_POST['stok_total']);
    $id_rak         = mysqli_real_escape_string($mysqli, $_POST['id_rak']);
    $sinopsis       = mysqli_real_escape_string($mysqli, $_POST['sinopsis']);
    
    // Nilai awal stok_tersedia disamakan dengan stok_total saat buku baru diinput
    $stok_tersedia  = $stok_total; 

    // Query insert dimodifikasi tanpa kolom dan nilai cover
    $sql_insert = "INSERT INTO buku (judul, isbn, id_kategori, id_penerbit, penulis, tahun_terbit, jumlah_halaman, stok_total, stok_tersedia, id_rak, sinopsis) 
                   VALUES ('$judul', '$isbn', '$id_kategori', '$id_penerbit', '$penulis', '$tahun_terbit', '$jumlah_halaman', '$stok_total', '$stok_tersedia', '$id_rak', '$sinopsis')";
    
    if (mysqli_query($mysqli, $sql_insert)) {
        // PERBAIKAN: Mengambil ID buku yang baru saja digenerate oleh Auto Increment database
        $id_buku_baru = mysqli_insert_id($mysqli);
        
        $pesan_sukses = "Data buku baru berhasil ditambahkan ke katalog.";
        echo "<script>
                alert('Buku berhasil ditambahkan!');
                window.location.href = 'buku.php?id=" . $id_buku_baru . "';
              </script>";
        exit;
    } else {
        $pesan_error = "Gagal menyimpan data ke database: " . mysqli_error($mysqli);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PojokBaca Admin Workspace">
  <title>PojokBaca | Tambah Koleksi Buku</title>

  <link class="main-stylesheet" rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php" aria-label="PojokBaca dashboard">
          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Administrator</span>
          </span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php">
          <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
          <span class="nav-text">Dashboard</span>
        </a>
        
        <a class="nav-link active" href="katalog.php">
          <span class="nav-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
          <span class="nav-text">Data Buku</span>
        </a>
        
        <a class="nav-link" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
          <span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        
        <a class="nav-link" href="peminjaman.php">
          <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
          <span class="nav-text">Peminjaman</span>
        </a>
        
        <a class="nav-link" href="denda.php">
          <span class="nav-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
          <span class="nav-text">Data Denda</span>
        </a>

        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
            <span class="nav-text">Kelola</span>
          </a>
          <div class="collapse ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small" href="penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>
        
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php">
          <span class="nav-icon"><i class="bi bi-box-arrow-left text-danger" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Logout</span>
        </a>
      </nav>

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
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          
          <div class="mb-3">
            <a href="katalog.php" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
          </div>

          <div class="page-heading">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-plus-circle-fill" aria-hidden="true"></i></span>
              <div>
                <p class="eyebrow mb-1">Manajemen Koleksi</p>
                <h1 class="h3 mb-1">Tambah Buku Baru</h1>
                <p class="text-muted mb-0">Input spesifikasi data detail buku, kelola kategori, serta penempatan data rak.</p>
              </div>
            </div>
          </div>

          <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan_error; ?>
            </div>
          <?php endif; ?>

          <section class="panel p-4">
            <form action="" method="POST">
              <div class="row g-3">
                
                <div class="col-12">
                  <label for="judul" class="form-label fw-medium text-dark">Judul Buku <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="judul" name="judul" required placeholder="Contoh: Belajar PHP MVC untuk Pemula">
                </div>

                <div class="col-12 col-sm-6">
                  <label for="isbn" class="form-label fw-medium text-dark">Nomor ISBN</label>
                  <input type="text" class="form-control" id="isbn" name="isbn" placeholder="Contoh: 978-602-8512-xx-x">
                </div>

                <div class="col-12 col-sm-6">
                  <label for="penulis" class="form-label fw-medium text-dark">Nama Penulis / Pengarang <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="penulis" name="penulis" required placeholder="Contoh: Andi Wijaya">
                </div>

                <div class="col-12 col-sm-4">
                  <label for="id_kategori" class="form-label fw-medium text-dark">Kategori Genre <span class="text-danger">*</span></label>
                  <select class="form-select" id="id_kategori" name="id_kategori" required>
                    <option value="" disabled selected>-- Pilih Kategori --</option>
                    <?php while ($kat = mysqli_fetch_assoc($query_kategori)): ?>
                      <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="col-12 col-sm-4">
                  <label for="id_penerbit" class="form-label fw-medium text-dark">Penerbit Buku <span class="text-danger">*</span></label>
                  <select class="form-select" id="id_penerbit" name="id_penerbit" required>
                    <option value="" disabled selected>-- Pilih Penerbit --</option>
                    <?php while ($pen = mysqli_fetch_assoc($query_penerbit)): ?>
                      <option value="<?= $pen['id_penerbit']; ?>"><?= htmlspecialchars($pen['nama_penerbit']); ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="col-12 col-sm-4">
                  <label for="tahun_terbit" class="form-label fw-medium text-dark">Tahun Terbit <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" required min="1900" max="2030" placeholder="Contoh: 2024">
                </div>

                <div class="col-12 col-sm-4">
                  <label for="jumlah_halaman" class="form-label fw-medium text-dark">Jumlah Halaman <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="jumlah_halaman" name="jumlah_halaman" required min="1" placeholder="Contoh: 350">
                </div>

                <div class="col-12 col-sm-4">
                  <label for="stok_total" class="form-label fw-medium text-dark">Stok Koleksi Fisik <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="stok_total" name="stok_total" required min="1" placeholder="Jumlah Buku">
                </div>

                <div class="col-12 col-sm-4">
                  <label for="id_rak" class="form-label fw-medium text-dark">Alokasi Rak Lokasi <span class="text-danger">*</span></label>
                  <select class="form-select" id="id_rak" name="id_rak" required>
                    <option value="" disabled selected>-- Pilih Lokasi Rak --</option>
                    <?php while ($rak = mysqli_fetch_assoc($query_rak)): ?>
                      <option value="<?= $rak['id_rak']; ?>"><?= htmlspecialchars($rak['nama_rak']); ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label for="sinopsis" class="form-label fw-medium text-dark">Sinopsis / Deskripsi Ringkas Buku</label>
                  <textarea class="form-control" id="sinopsis" name="sinopsis" rows="5" placeholder="Tulis deskripsi atau sinopsis singkat buku di sini..."></textarea>
                </div>

                <div class="col-12 pt-3 border-top">
                  <div class="d-flex justify-content-end gap-2">
                    <a href="katalog.php" class="btn btn-outline-secondary px-4">Batal</a>
                    <button type="submit" name="submit" class="btn btn-primary px-4 shadow-sm">
                      <i class="bi bi-save me-1"></i> Simpan Buku
                    </button>
                  </div>
                </div>

              </div>
            </form>
          </section>

        </div>
      </main>

      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4">
          <span>Copyright 2026 PojokBaca. All rights reserved.</span>
        </div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>