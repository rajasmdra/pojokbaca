<?php
session_start();
// 1. Proteksi Halaman: Pastikan yang masuk adalah admin
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database (naik 1 tingkat ke folder utama config)
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// 3. Ambil total pendaftaran anggota baru yang berstatus 'pending' untuk badge sidebar
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// ==========================================
// 4. PROSES INSERT & UPDATE (AKSI FORM)
// ==========================================
if (isset($_POST['simpan_penerbit'])) {
    $nama_penerbit = mysqli_real_escape_string($mysqli, trim($_POST['nama_penerbit']));
    $id_penerbit   = $_POST['id_penerbit'] ?? '';

    if (empty($nama_penerbit)) {
        $_SESSION['gagal_penerbit'] = "Nama penerbit tidak boleh kosong!";
    } else {
        if (!empty($id_penerbit)) {
            // PROSES UPDATE
            $sql_update = "UPDATE penerbit SET nama_penerbit = '$nama_penerbit' WHERE id_penerbit = '$id_penerbit'";
            if (mysqli_query($mysqli, $sql_update)) {
                $_SESSION['sukses_penerbit'] = "Penerbit berhasil diperbarui menjadi <strong>$nama_penerbit</strong>!";
            } else {
                $_SESSION['gagal_penerbit'] = "Gagal memperbarui penerbit.";
            }
        } else {
            // PROSES INSERT
            $sql_insert = "INSERT INTO penerbit (nama_penerbit) VALUES ('$nama_penerbit')";
            if (mysqli_query($mysqli, $sql_insert)) {
                $_SESSION['sukses_penerbit'] = "Penerbit <strong>$nama_penerbit</strong> berhasil ditambahkan!";
            } else {
                $_SESSION['gagal_penerbit'] = "Gagal menambahkan penerbit.";
            }
        }
    }
    header("Location: penerbit.php");
    exit;
}

// ==========================================
// 5. PROSES DELETE (HAPUS DATA)
// ==========================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];
    
    // Cek terlebih dahulu apakah penerbit sedang digunakan di tabel buku
    $cek_buku = mysqli_query($mysqli, "SELECT id_buku FROM buku WHERE id_penerbit = '$id_hapus' LIMIT 1");
    if (mysqli_num_rows($cek_buku) > 0) {
        $_SESSION['gagal_penerbit'] = "Penerbit gagal dihapus karena masih digunakan oleh data buku.";
    } else {
        $sql_hapus = "DELETE FROM penerbit WHERE id_penerbit = '$id_hapus'";
        if (mysqli_query($mysqli, $sql_hapus)) {
            $_SESSION['sukses_penerbit'] = "Penerbit berhasil dihapus!";
        } else {
            $_SESSION['gagal_penerbit'] = "Gagal menghapus penerbit.";
        }
    }
    header("Location: penerbit.php");
    exit;
}

// ==========================================
// 6. LOGIKA EDIT (AMBIL DATA YANG MAU DIEDIT)
// ==========================================
$edit_mode = false;
$val_id_penerbit = '';
$val_nama_penerbit = '';

if (isset($_GET['aksi']) && $_GET['aksi'] == 'edit' && isset($_GET['id'])) {
    $id_edit = $_GET['id'];
    $query_edit = mysqli_query($mysqli, "SELECT * FROM penerbit WHERE id_penerbit = '$id_edit'");
    if (mysqli_num_rows($query_edit) > 0) {
        $data_edit = mysqli_fetch_assoc($query_edit);
        $edit_mode = true;
        $val_id_penerbit = $data_edit['id_penerbit'];
        $val_nama_penerbit = $data_edit['nama_penerbit'];
    }
}

// ==========================================
// 7. LOGIKA SORTING VARIABEL & FUNGSI ICON (Menggunakan Segitiga Solid Abu-Abu)
// ==========================================
$sort_by    = $_GET['by'] ?? 'nama';
$sort_order = $_GET['order'] ?? 'ASC';

$next_order = (strtoupper($sort_order) == 'ASC') ? 'DESC' : 'ASC';

function getSortIcon($column, $current_by, $current_order) {
    if ($current_by === $column) {
        return (strtoupper($current_order) === 'ASC') 
            ? '<i class="bi bi-caret-down-fill ms-1" style="font-size: 0.8rem;"></i>' 
            : '<i class="bi bi-caret-up-fill ms-1" style="font-size: 0.8rem;"></i>';
    }
    return '<i class="bi bi-caret-down text-muted opacity-25 ms-1" style="font-size: 0.8rem;"></i>';
}

$orderby_sql = "nama_penerbit ASC"; 
if ($sort_by === 'nama') {
    $orderby_sql = (strtoupper($sort_order) === 'DESC') ? "nama_penerbit DESC" : "nama_penerbit ASC";
}

$query_tabel = mysqli_query($mysqli, "SELECT * FROM penerbit ORDER BY $orderby_sql");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Kelola Penerbit Buku</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link class="ui-theme-icon" rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    /* Menyelaraskan warna link di semua header tabel menjadi abu-abu sesuai permintaan */
    .table th, .table th a {
      color: #6c757d !important; /* Warna text-secondary (abu-abu) */
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }
    .table th a:hover {
      color: #495057 !important;
    }
  </style>
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
        <a class="nav-link" href="peminjaman.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Peminjaman</span></a>
        <a class="nav-link" href="denda.php"><span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Data Denda</span></a>
        
        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle active" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear"></i></span><span class="nav-text">Kelola</span>
          </a>
          <div class="collapse show ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small active" href="penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>

        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
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
          <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-building"></i></span>
              <div>
                <p class="eyebrow mb-1">Data Master</p>
                <h1 class="h3 mb-1">Kelola Penerbit Buku</h1>
                <p class="text-muted mb-0">Atur daftar perusahaan penerbitan yang menerbitkan buku-buku di perpustakaan.</p>
              </div>
            </div>
          </div>

          <?php if (isset($_SESSION['sukses_penerbit'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['sukses_penerbit']; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['sukses_penerbit']); ?>
          <?php endif; ?>

          <?php if (isset($_SESSION['gagal_penerbit'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['gagal_penerbit']; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['gagal_penerbit']); ?>
          <?php endif; ?>

          <div class="row g-4 mt-1">
            <div class="col-12 col-lg-4">
              <section class="panel">
                <div class="panel-header">
                  <h2 class="h5 mb-0 section-title">
                    <i class="bi <?= $edit_mode ? 'bi-pencil-square text-warning' : 'bi-plus-circle text-primary'; ?>"></i>
                    <span><?= $edit_mode ? 'Ubah Penerbit' : 'Tambah Penerbit Baru'; ?></span>
                  </h2>
                </div>
                <div class="p-3 border-top">
                  <form action="penerbit.php" method="POST">
                    <input type="hidden" name="id_penerbit" value="<?= $val_id_penerbit; ?>">
                    
                    <div class="mb-3">
                      <label for="nama_penerbit" class="form-label fw-semibold small text-muted">Nama Penerbit</label>
                      <input type="text" class="form-control" id="nama_penerbit" name="nama_penerbit" placeholder="Contoh: Gramedia, Erlangga, Mizan" value="<?= htmlspecialchars($val_nama_penerbit); ?>" required autocomplete="off">
                    </div>

                    <div class="d-grid gap-2">
                      <button type="submit" name="simpan_penerbit" class="btn <?= $edit_mode ? 'btn-warning' : 'btn-primary'; ?> fw-medium">
                        <i class="bi bi-save me-1"></i> <?= $edit_mode ? 'Perbarui Data' : 'Simpan Penerbit'; ?>
                      </button>
                      <?php if($edit_mode): ?>
                        <a href="penerbit.php?by=<?= $sort_by; ?>&order=<?= $sort_order; ?>" class="btn btn-light border small fw-medium text-secondary">Batal Edit</a>
                      <?php endif; ?>
                    </div>
                  </form>
                </div>
              </section>
            </div>

            <div class="col-12 col-lg-8">
              <section class="panel">
                <div class="panel-header py-3">
                  <div>
                    <h2 class="h5 mb-1 section-title"><i class="bi bi-table"></i><span>Daftar Penerbit</span></h2>
                  </div>
                  <input class="form-control form-control-sm table-search" type="search" placeholder="Cari penerbit..." data-table-search="penerbitTable" style="max-width: 200px;">
                </div>
                
                <div class="table-responsive">
                  <table class="table align-middle mb-0 table-hover" id="penerbitTable">
                    <thead>
                      <tr>
                        <th style="width: 80px;">No.</th>
                        <th class="sortable-header">
                          <a href="penerbit.php?by=nama&order=<?= ($sort_by == 'nama') ? $next_order : 'ASC'; ?>" class="text-decoration-none d-inline-flex align-items-center">
                            Nama Penerbit Buku <?= getSortIcon('nama', $sort_by, $sort_order); ?>
                          </a>
                        </th>
                        <th class="text-end" style="min-width: 140px;">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $no = 1;
                      if (mysqli_num_rows($query_tabel) > 0): 
                        while($row = mysqli_fetch_assoc($query_tabel)):
                      ?>
                        <tr class="<?= ($edit_mode && $val_id_penerbit == $row['id_penerbit']) ? 'table-warning opacity-75' : ''; ?>">
                          <td class="text-muted small"><?= $no++; ?>.</td>
                          <td class="fw-semibold d-flex align-items-center gap-2">
                            <span class="brand-icon"><i class="bi bi-building-fill" aria-hidden="true"></i></span><?= htmlspecialchars($row['nama_penerbit']); ?>
                          </td>
                          <td class="text-end">
                            <a href="penerbit.php?id=<?= $row['id_penerbit']; ?>&aksi=edit&by=<?= $sort_by; ?>&order=<?= $sort_order; ?>" class="btn btn-outline-warning btn-sm px-2 py-1 me-1 fw-medium" title="Ubah Data">
                              <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            <a href="penerbit.php?id=<?= $row['id_penerbit']; ?>&aksi=hapus" class="btn btn-outline-danger btn-sm px-2 py-1 fw-medium" onclick="return confirm('Apakah Anda yakin ingin menghapus penerbit ini? Tindakan ini akan divalidasi sistem terhadap sirkulasi buku.')" title="Hapus Data">
                              <i class="bi bi-trash me-1"></i>Hapus
                            </a>
                          </td>
                        </tr>
                      <?php 
                        endwhile; 
                      else: 
                      ?>
                        <tr>
                          <td colspan="3" class="text-center py-4 text-muted">
                            <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary"></i> Belum ada rekaman penerbit buku yang tersimpan.
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </section>
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
</body>
</html>