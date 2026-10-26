<?php
session_start();
require_once __DIR__ . "/connection.php";
if (!mysqli_select_db($conn, "akademik_db")) {
  die("Database akademik_db tidak dapat dibuka.");
}
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
$calculateGrade = static function (float $score): array {
  if ($score >= 85) {
    return ['A', 'Sangat Memuaskan'];
  }
  if ($score >= 75) {
    return ['B', 'Memuaskan'];
  }
  if ($score >= 60) {
    return ['C', 'Cukup Memuaskan'];
  }
  if ($score >= 50) {
    return ['D', 'Kurang Memuaskan'];
  }
  return ['E', 'Tidak Lulus'];
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $postedToken = $_POST['csrf_token'] ?? '';
  $action = $_POST['action'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
    $flashMessage = 'Permintaan tidak valid. Silakan coba lagi.';
    $flashType = 'danger';
  } elseif ($action === 'add') {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nm_lengkap'] ?? '');
    $nilaiAkhir = $_POST['nilai_akhir'] ?? '';
    $filePath = trim($_POST['file_path'] ?? '');

    if ($nim === '' || strlen($nim) > 10 || $nama === '' || strlen($nama) > 100 ||
      !is_numeric($nilaiAkhir) || (float) $nilaiAkhir < 0 || (float) $nilaiAkhir > 100) {
      $flashMessage = 'NIM, nama, atau nilai akhir tidak valid.';
      $flashType = 'danger';
    } else {
      $nilaiAkhir = (float) $nilaiAkhir;
      [$nilaiHuruf, $predikat] = $calculateGrade($nilaiAkhir);
      if ($filePath === '' || !filter_var($filePath, FILTER_VALIDATE_URL)) {
        $filePath = 'https://ui-avatars.com/api/?name=' . rawurlencode($nama) . '&background=random&color=fff&size=128';
      }

      $stmt = mysqli_prepare($conn, "INSERT INTO nilai (nim, nm_lengkap, file_path, nilai_akhir, nilai_huruf, predikat, created_date, modified_date) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
      mysqli_stmt_bind_param($stmt, "sssdss", $nim, $nama, $filePath, $nilaiAkhir, $nilaiHuruf, $predikat);
      if (mysqli_stmt_execute($stmt)) {
        $flashMessage = 'Data mahasiswa berhasil ditambahkan.';
      } else {
        $flashMessage = mysqli_stmt_errno($stmt) === 1062
          ? 'NIM tersebut sudah terdaftar.'
          : 'Data mahasiswa gagal ditambahkan.';
        $flashType = 'danger';
      }
      mysqli_stmt_close($stmt);
    }
  } elseif ($action === 'edit') {
    $nimLama = trim($_POST['nim_lama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nm_lengkap'] ?? '');
    $nilaiAkhir = $_POST['nilai_akhir'] ?? '';
    $filePath = trim($_POST['file_path'] ?? '');

    if ($nimLama === '' || $nim === '' || strlen($nim) > 10 || $nama === '' || strlen($nama) > 100 ||
      !is_numeric($nilaiAkhir) || (float) $nilaiAkhir < 0 || (float) $nilaiAkhir > 100) {
      $flashMessage = 'NIM, nama, atau nilai akhir tidak valid.';
      $flashType = 'danger';
    } else {
      $nilaiAkhir = (float) $nilaiAkhir;
      [$nilaiHuruf, $predikat] = $calculateGrade($nilaiAkhir);
      if ($filePath === '' || !filter_var($filePath, FILTER_VALIDATE_URL)) {
        $filePath = 'https://ui-avatars.com/api/?name=' . rawurlencode($nama) . '&background=random&color=fff&size=128';
      }

      $stmt = mysqli_prepare($conn, "UPDATE nilai SET nim = ?, nm_lengkap = ?, file_path = ?, nilai_akhir = ?, nilai_huruf = ?, predikat = ?, modified_date = NOW() WHERE nim = ?");
      mysqli_stmt_bind_param($stmt, "sssdsss", $nim, $nama, $filePath, $nilaiAkhir, $nilaiHuruf, $predikat, $nimLama);
      if (mysqli_stmt_execute($stmt)) {
        $flashMessage = 'Data mahasiswa berhasil diperbarui.';
      } else {
        $flashMessage = mysqli_stmt_errno($stmt) === 1062
          ? 'NIM tersebut sudah digunakan mahasiswa lain.'
          : 'Data mahasiswa gagal diperbarui.';
        $flashType = 'danger';
      }
      mysqli_stmt_close($stmt);
    }
  } elseif ($action === 'delete') {
    $nim = trim($_POST['nim'] ?? '');
    $stmt = mysqli_prepare($conn, "DELETE FROM nilai WHERE nim = ?");
    mysqli_stmt_bind_param($stmt, "s", $nim);
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
      $flashMessage = 'Data mahasiswa berhasil dihapus.';
    } else {
      $flashMessage = 'Data mahasiswa tidak ditemukan atau gagal dihapus.';
      $flashType = 'danger';
    }
    mysqli_stmt_close($stmt);
  } else {
    $flashMessage = 'Aksi tidak dikenali.';
    $flashType = 'danger';
  }
  $_SESSION['flash_message'] = $flashMessage;
  $_SESSION['flash_type'] = $flashType;
  header('Location: viewdata.php');
  exit;
}

$search = trim($_GET['search'] ?? '');
$filterPredikat = $_GET['predikat'] ?? 'all';
$predicates = [
  'Sangat Memuaskan',
  'Memuaskan',
  'Cukup Memuaskan',
  'Kurang Memuaskan',
  'Tidak Lulus',
];
if ($filterPredikat !== 'all' && !in_array($filterPredikat, $predicates, true)) {
  $filterPredikat = 'all';
}

$allowedLimits = [5, 10, 15];
$pageLimit = filter_var($_GET['limit'] ?? 10, FILTER_VALIDATE_INT);
if ($pageLimit === false || !in_array($pageLimit, $allowedLimits, true)) {
  $pageLimit = 10;
}

$sortColumn = $_GET['sort'] ?? 'nilai_akhir';
$sortDirection = strtoupper($_GET['dir'] ?? 'DESC');
$allowedSortColumns = ['nim', 'nm_lengkap', 'nilai_akhir', 'created_date', 'modified_date'];
if (!in_array($sortColumn, $allowedSortColumns, true)) {
  $sortColumn = 'nilai_akhir';
}
if (!in_array($sortDirection, ['ASC', 'DESC'], true)) {
  $sortDirection = 'DESC';
}

$requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$page = $requestedPage !== false && $requestedPage > 0 ? $requestedPage : 1;
$conditions = [];
$parameterTypes = '';
$parameters = [];
if ($search !== '') {
  $conditions[] = 'nm_lengkap LIKE ?';
  $parameters[] = '%' . $search . '%';
  $parameterTypes .= 's';
}
if ($filterPredikat !== 'all') {
  $conditions[] = 'predikat = ?';
  $parameters[] = $filterPredikat;
  $parameterTypes .= 's';
}
$whereSql = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$bindParameters = static function ($statement, $types, &$values) {
  if ($types !== '') {
    $references = [];
    foreach ($values as $key => &$value) {
      $references[$key] = &$value;
    }
    mysqli_stmt_bind_param($statement, $types, ...$references);
  }
};

$countStatement = mysqli_prepare($conn, 'SELECT COUNT(*) FROM nilai' . $whereSql);
$bindParameters($countStatement, $parameterTypes, $parameters);
mysqli_stmt_execute($countStatement);
mysqli_stmt_bind_result($countStatement, $totalRows);
mysqli_stmt_fetch($countStatement);
mysqli_stmt_close($countStatement);

$totalPages = max(1, (int) ceil($totalRows / $pageLimit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageLimit;
$dataStatement = mysqli_prepare($conn, 'SELECT nim, nm_lengkap, file_path, nilai_akhir, nilai_huruf, predikat, created_date, modified_date FROM nilai' . $whereSql . ' ORDER BY ' . $sortColumn . ' ' . $sortDirection . ' LIMIT ? OFFSET ?');
$dataParameters = $parameters;
$dataParameters[] = $pageLimit;
$dataParameters[] = $offset;
$bindParameters($dataStatement, $parameterTypes . 'ii', $dataParameters);
mysqli_stmt_execute($dataStatement);
$result = mysqli_stmt_get_result($dataStatement);
$startRow = $totalRows > 0 ? $offset + 1 : 0;
$endRow = min($offset + $pageLimit, $totalRows);
$paginationUrl = static function (int $targetPage) use ($search, $filterPredikat, $pageLimit, $sortColumn, $sortDirection): string {
  return '?' . http_build_query([
    'search' => $search,
    'predikat' => $filterPredikat,
    'page' => $targetPage,
    'limit' => $pageLimit,
    'sort' => $sortColumn,
    'dir' => $sortDirection,
  ]);
};
?>
<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>AdminLTE 4 | Users</title>

  <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
  <script>
    (() => {
      'use strict';
      const root = document.documentElement;

      // Applications with their own theming opt out of AdminLTE's color mode
      // entirely, here as well as in the bundle.
      if (root.getAttribute('data-lte-color-mode') === 'off') {
        return;
      }

      const STORAGE_KEY = 'lte-theme';
      let stored = null;
      try {
        stored = localStorage.getItem(STORAGE_KEY);
      } catch {
        // localStorage may be unavailable (private mode, sandboxed iframe).
      }
      // Mirror the precedence in color-mode.ts: the visitor's stored choice
      // wins, then a theme this page declared itself, then the OS preference.
      const authored = root.getAttribute('data-bs-theme');
      let resolved = 'light';
      if (stored === 'dark' || stored === 'light') {
        resolved = stored;
      } else if (authored === 'dark' || authored === 'light') {
        resolved = authored;
      } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
        resolved = 'dark';
      }
      root.setAttribute('data-bs-theme', resolved);
      root.style.colorScheme = resolved;
      // Flag values computed here, so the bundle does not mistake them for a
      // theme the page declared and stop following the OS preference.
      if (resolved !== authored) {
        root.setAttribute('data-lte-theme-resolved', '');
      }
    })();
  </script>
  <!--end::Theme Init-->

  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->

  <!--begin::Primary Meta Tags-->
  <meta name="title" content="AdminLTE 4 | Users" />
  <meta name="author" content="ColorlibHQ" />
  <meta name="description"
    content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind." />
  <meta name="keywords"
    content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel" />
  <!--end::Primary Meta Tags-->

  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="./css/adminlte.css" as="style" />
  <!--end::Accessibility Features-->

  <!--begin::Fonts-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
    onload="this.media = 'all'" />
  <!--end::Fonts-->

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="./css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">
    <!--begin::Header-->
    <!--end::Header-->
    <!--begin::Sidebar-->

    <!--end::Sidebar-->
    <!--begin::App Main-->
    <main class="app-main">
      <!--begin::App Content Header-->
      <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Row-->
          <div class="row">
            <div class="col-sm-6">
              <h1 class="mb-0 fs-3">Rekap Nilai Mahasiswa</h1>
            </div>
            <div class="col-sm-6">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Nilai</li>
                </ol>
              </nav>
            </div>
          </div>
          <!--end::Row-->
        </div>
        <!--end::Container-->
      </div>
      <!--end::App Content Header-->
      <!--begin::App Content-->
      <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Row-->
          <div class="row">
            <div class="col-12">
              <!--begin::Card-->
              <div class="card mb-4">
                <?php if ($flashMessage !== null) { ?>
                  <div class="alert alert-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?> m-3 mb-0" role="alert">
                    <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?>
                  </div>
                <?php } ?>
                <!--begin::Card Header-->
                <div class="card-header">
                  <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                      <h3 class="card-title">Directory Nilai</h3>
                    </div>
                    <div class="col-12 col-md-8">
                      <div class="d-flex flex-wrap justify-content-md-end gap-2">
                        <form action="viewdata.php" method="get" class="d-flex flex-wrap gap-2">
                          <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortColumn, ENT_QUOTES, 'UTF-8'); ?>" />
                          <input type="hidden" name="dir" value="<?php echo htmlspecialchars($sortDirection, ENT_QUOTES, 'UTF-8'); ?>" />
                          <input type="hidden" name="limit" value="<?php echo htmlspecialchars((string) $pageLimit, ENT_QUOTES, 'UTF-8'); ?>" />
                          <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text">
                              <i class="bi bi-search" aria-hidden="true"></i>
                            </span>
                            <input type="search" id="user-search" name="search" class="form-control"
                              placeholder="Cari nama mahasiswa" aria-label="Cari berdasarkan nama"
                              value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" style="width: 190px" />
                          </div>
                          <select id="user-role-filter" name="predikat" class="form-select form-select-sm w-auto"
                            aria-label="Filter berdasarkan predikat" onchange="this.form.requestSubmit()">
                            <option value="all" <?php echo $filterPredikat === 'all' ? 'selected' : ''; ?>>Semua Predikat</option>
                            <?php foreach ($predicates as $predicate) { ?>
                              <option value="<?php echo htmlspecialchars($predicate, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $filterPredikat === $predicate ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($predicate, ENT_QUOTES, 'UTF-8'); ?>
                              </option>
                            <?php } ?>
                          </select>
                          <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Teraplikan pencarian dan filter">
                            <i class="bi bi-search" aria-hidden="true"></i>
                          </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                          data-bs-target="#modal-add-user">
                          <i class="bi bi-person-plus-fill me-1" aria-hidden="true"> </i>
                          Tambah Data Nilai
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <!--end::Card Header-->
                <?php
                if (mysqli_num_rows($result) > 0) {
                ?>
                  <!--begin::Card Body-->
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle m-0">
                        <thead>
                          <tr>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>
                              <a class="btn btn-link text-decoration-none p-0 text-body fw-bold d-inline-flex align-items-center gap-1"
                                href="<?php echo htmlspecialchars($paginationUrl(1) . '&sort=nilai_akhir&dir=' . ($sortColumn === 'nilai_akhir' && $sortDirection === 'ASC' ? 'DESC' : 'ASC'), ENT_QUOTES, 'UTF-8'); ?>"
                                aria-label="Urutkan berdasarkan nilai akhir">
                                Nilai Akhir
                                <?php if ($sortColumn === 'nilai_akhir') { ?>
                                  <i class="bi bi-arrow-<?php echo $sortDirection === 'ASC' ? 'up' : 'down'; ?>-short" aria-hidden="true"></i>
                                <?php } else { ?>
                                  <i class="bi bi-arrow-down-up text-muted" aria-hidden="true"></i>
                                <?php } ?>
                              </a>
                            </th>
                            <th>Nilai Huruf</th>
                            <th>Predikat</th>
                            <th>Tanggal Dibuat</th>
                            <th>Tanggal Diubah</th>
                            <th class="text-end">Aksi</th>
                          </tr>
                        </thead>

                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                          $photoPath = $row['file_path'] ?? '';
                          if ($photoPath === '' || !filter_var($photoPath, FILTER_VALIDATE_URL)) {
                            $photoPath = 'https://ui-avatars.com/api/?name=' . rawurlencode($row['nm_lengkap'] ?? 'Mahasiswa') . '&background=random&color=fff&size=128';
                          }
                        ?>
                          <tbody>
                            <tr>
                              <td>
                                <span class="fw-medium"><?php echo htmlspecialchars($row['nim'], ENT_QUOTES, 'UTF-8'); ?></span>
                              </td>
                              <td>
                                <div class="d-flex align-items-center gap-2">
                                  <img src="<?php echo htmlspecialchars($photoPath, ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Foto <?php echo htmlspecialchars($row['nm_lengkap'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="rounded-circle border"
                                    style="width: 32px; height: 32px; object-fit: cover;" />
                                  <span class="fw-medium"><?php echo htmlspecialchars($row['nm_lengkap'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                              </td>
                              <td>
                                <span class="fw-medium"><?php echo htmlspecialchars($row['nilai_akhir'], ENT_QUOTES, 'UTF-8'); ?></span>
                              </td>
                              <td>
                                <span class="fw-medium"><?php echo htmlspecialchars($row['nilai_huruf'], ENT_QUOTES, 'UTF-8'); ?></span>
                              </td>
                              <td>
                                <span class="fw-medium"><?php echo htmlspecialchars($row['predikat'], ENT_QUOTES, 'UTF-8'); ?></span>
                              </td>
                              <td>
                                <span class="fw-medium"><?php echo htmlspecialchars($row['created_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                              </td>
                              <td>
                                <span class="fw-medium"><?php echo htmlspecialchars($row['modified_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                              </td>
                              <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                  <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modal-edit-user"
                                    data-student-nim="<?php echo htmlspecialchars($row['nim'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-student-name="<?php echo htmlspecialchars($row['nm_lengkap'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-student-score="<?php echo htmlspecialchars($row['nilai_akhir'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-student-photo="<?php echo htmlspecialchars($photoPath, ENT_QUOTES, 'UTF-8'); ?>"
                                    aria-label="Edit data <?php echo htmlspecialchars($row['nm_lengkap'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-pencil" aria-hidden="true"> </i>
                                  </button>
                                  <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#modal-delete-user"
                                    data-student-nim="<?php echo htmlspecialchars($row['nim'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-student-name="<?php echo htmlspecialchars($row['nm_lengkap'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-student-score="<?php echo htmlspecialchars($row['nilai_akhir'], ENT_QUOTES, 'UTF-8'); ?>"
                                    aria-label="Hapus data <?php echo htmlspecialchars($row['nm_lengkap'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="bi bi-trash" aria-hidden="true"> </i>
                                  </button>
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        <?php } ?>
                      </table>
                    </div>
                    <!-- /.table-responsive -->
                  <?php
                } else {
                  echo '<div class="card-body">Data mahasiswa tidak ditemukan.</div>';
                }
                mysqli_stmt_close($dataStatement);
                mysqli_close($conn);
                  ?>

                  </div>
                  <!--end::Card Body-->
                  <!--begin::Card Footer-->
                  <div class="card-footer d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2 text-body-secondary fs-7">
                      <span>Menampilkan <?php echo $startRow; ?>–<?php echo $endRow; ?> dari <?php echo $totalRows; ?> mahasiswa</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <label for="records-per-page" class="mb-0 fs-7 text-body-secondary">Tampilkan</label>
                      <select id="records-per-page" class="form-select form-select-sm w-auto" aria-label="Jumlah data per halaman"
                        onchange="window.location='viewdata.php?<?php echo htmlspecialchars(http_build_query(['search' => $search, 'predikat' => $filterPredikat, 'page' => 1, 'limit' => '___LIMIT___', 'sort' => $sortColumn, 'dir' => $sortDirection]), ENT_QUOTES, 'UTF-8'); ?>'.replace('___LIMIT___', this.value);">
                        <?php foreach ($allowedLimits as $limitOption) { ?>
                          <option value="<?php echo $limitOption; ?>" <?php echo $pageLimit === $limitOption ? 'selected' : ''; ?>>
                            <?php echo $limitOption; ?>
                          </option>
                        <?php } ?>
                      </select>
                      <div class="btn-group btn-group-sm" aria-label="Navigasi halaman">
                        <a class="btn btn-outline-secondary <?php echo $page <= 1 ? 'disabled' : ''; ?>"
                          href="<?php echo $page > 1 ? htmlspecialchars($paginationUrl(1), ENT_QUOTES, 'UTF-8') : '#'; ?>"
                          aria-label="Halaman pertama">First</a>
                        <a class="btn btn-outline-secondary <?php echo $page <= 1 ? 'disabled' : ''; ?>"
                          href="<?php echo $page > 1 ? htmlspecialchars($paginationUrl($page - 1), ENT_QUOTES, 'UTF-8') : '#'; ?>"
                          aria-label="Halaman sebelumnya">Previous</a>
                        <a class="btn btn-outline-secondary <?php echo $page >= $totalPages ? 'disabled' : ''; ?>"
                          href="<?php echo $page < $totalPages ? htmlspecialchars($paginationUrl($page + 1), ENT_QUOTES, 'UTF-8') : '#'; ?>"
                          aria-label="Halaman berikutnya">Next</a>
                        <a class="btn btn-outline-secondary <?php echo $page >= $totalPages ? 'disabled' : ''; ?>"
                          href="<?php echo $page < $totalPages ? htmlspecialchars($paginationUrl($totalPages), ENT_QUOTES, 'UTF-8') : '#'; ?>"
                          aria-label="Halaman terakhir">Last</a>
                      </div>
                    </div>
                  </div>
                  <!--end::Card Footer-->
              </div>
              <!--end::Card-->
            </div>
            <!-- /.col -->
          </div>
          <!--end::Row-->

          <!--begin::Add User Modal-->
          <div class="modal fade" id="modal-add-user" tabindex="-1" aria-labelledby="modal-add-user-label"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="viewdata.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
                  <input type="hidden" name="action" value="add" />
                  <div class="modal-header">
                    <h5 class="modal-title" id="modal-add-user-label">Tambah Data Nilai Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="new-student-nim" class="form-label">NIM</label>
                      <input type="text" class="form-control" id="new-student-nim" name="nim"
                        placeholder="Masukkan NIM" maxlength="10" required />
                    </div>
                    <div class="mb-3">
                      <label for="new-student-name" class="form-label">Nama Lengkap</label>
                      <input type="text" class="form-control" id="new-student-name" name="nm_lengkap"
                        placeholder="Masukkan nama lengkap" maxlength="100" required />
                    </div>
                    <div class="mb-3">
                      <label for="new-student-photo" class="form-label">URL Foto</label>
                      <input type="url" class="form-control" id="new-student-photo" name="file_path"
                        placeholder="https://example.com/foto.jpg" />
                    </div>
                    <div class="mb-3">
                      <label for="new-student-score" class="form-label">Nilai Akhir</label>
                      <input type="number" class="form-control" id="new-student-score" name="nilai_akhir"
                        placeholder="Contoh: 85" min="0" max="100" step="0.01" required />
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                      Batal
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!--end::Add User Modal-->

          <!--begin::Edit Student Modal-->
          <div class="modal fade" id="modal-edit-user" tabindex="-1" aria-labelledby="modal-edit-user-label"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="viewdata.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
                  <input type="hidden" name="action" value="edit" />
                  <input type="hidden" name="nim_lama" id="edit-student-original-nim" />
                  <div class="modal-header">
                    <h5 class="modal-title" id="modal-edit-user-label">Edit Data Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="edit-student-nim" class="form-label">NIM</label>
                      <input type="text" class="form-control" id="edit-student-nim" name="nim"
                        maxlength="10" required />
                    </div>
                    <div class="mb-3">
                      <label for="edit-student-name" class="form-label">Nama Lengkap</label>
                      <input type="text" class="form-control" id="edit-student-name" name="nm_lengkap"
                        maxlength="100" required />
                    </div>
                    <div class="mb-3">
                      <label for="edit-student-photo" class="form-label">URL Foto</label>
                      <input type="url" class="form-control" id="edit-student-photo" name="file_path"
                        placeholder="https://example.com/foto.jpg" />
                    </div>
                    <div class="mb-3">
                      <label for="edit-student-score" class="form-label">Nilai Akhir</label>
                      <input type="number" class="form-control" id="edit-student-score" name="nilai_akhir"
                        min="0" max="100" step="0.01" required />
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!--end::Edit Student Modal-->

          <!--begin::Delete User Modal-->
          <div class="modal fade" id="modal-delete-user" tabindex="-1" aria-labelledby="modal-delete-user-label"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="viewdata.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="nim" id="delete-student-id" />
                  <div class="modal-header">
                    <h5 class="modal-title" id="modal-delete-user-label">Hapus Data Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data mahasiswa berikut?</p>
                    <dl class="row mb-0">
                      <dt class="col-4">NIM</dt>
                      <dd class="col-8" id="delete-student-nim"></dd>
                      <dt class="col-4">Nama Lengkap</dt>
                      <dd class="col-8" id="delete-student-name"></dd>
                      <dt class="col-4">Nilai Akhir</dt>
                      <dd class="col-8" id="delete-student-score"></dd>
                    </dl>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                      Batal
                    </button>
                    <button type="submit" class="btn btn-danger">Hapus Data</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!--end::Delete User Modal-->
        </div>
        <!--end::Container-->
      </div>
      <!--end::App Content-->
    </main>
    <!--end::App Main-->
    <!--begin::Footer-->
    <footer class="app-footer">
      <!--begin::To the end-->
      <div class="float-end d-none d-sm-inline">Web Programming Semester 5</div>
      <!--end::To the end-->
      <!--begin::Copyright-->
      <strong>
        Copyright &copy; 2014-2026&nbsp;
        <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
      </strong>
      All rights reserved.
      <!--end::Copyright-->
    </footer>
    <!--end::Footer-->
  </div>
  <!--end::App Wrapper-->
  <!--begin::Script-->
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
  <script src="./js/adminlte.js"></script>
  <!--end::Required Plugin(AdminLTE)-->
  <script>
    document.getElementById('modal-edit-user').addEventListener('show.bs.modal', function(event) {
      const trigger = event.relatedTarget;
      document.getElementById('edit-student-original-nim').value = trigger.dataset.studentNim;
      document.getElementById('edit-student-nim').value = trigger.dataset.studentNim;
      document.getElementById('edit-student-name').value = trigger.dataset.studentName;
      document.getElementById('edit-student-photo').value = trigger.dataset.studentPhoto || '';
      document.getElementById('edit-student-score').value = trigger.dataset.studentScore;
    });

    document.getElementById('modal-delete-user').addEventListener('show.bs.modal', function(event) {
      const trigger = event.relatedTarget;
      document.getElementById('delete-student-nim').textContent = trigger.dataset.studentNim;
      document.getElementById('delete-student-name').textContent = trigger.dataset.studentName;
      document.getElementById('delete-student-score').textContent = trigger.dataset.studentScore;
      document.getElementById('delete-student-id').value = trigger.dataset.studentNim;
    });
  </script>
  <!--begin::OverlayScrollbars Configure-->
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

      // Disable OverlayScrollbars on mobile devices to prevent touch interference
      const isMobile = window.innerWidth <= 992;

      if (
        sidebarWrapper &&
        OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
        !isMobile
      ) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });
  </script>
  <!--end::OverlayScrollbars Configure-->

  <!--begin::Color Mode Toggle-->
  <!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
  <!--end::Color Mode Toggle-->

  <!--end::Script-->
</body>
<!--end::Body-->

</html>