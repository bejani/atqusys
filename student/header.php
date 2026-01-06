<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل دانشجو | سامانه هوشمند</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .nav-link-modern {
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            padding: 10px 15px !important;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .nav-link-modern:hover, .nav-link-modern.active {
            color: white;
            background: rgba(255,255,255,0.15);
        }
        .navbar-brand-modern {
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
        }
        @media (max-width: 991.98px) {
            .navbar-modern { padding: 10px 0; }
            .navbar-brand-modern { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-modern sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-modern" href="dashboard.php">
                <i class="bi bi-mortarboard-fill me-2"></i> سامانه هوشمند
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="studentNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="bi bi-grid-fill me-1"></i> داشبورد
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo basename($_SERVER['PHP_SELF']) == 'my_reports.php' ? 'active' : ''; ?>" href="my_reports.php">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i> گزارشات و نمرات
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="btn btn-light btn-modern dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2 fs-5"></i>
                            <span><?php echo $_SESSION['full_name']; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <li><a class="dropdown-item py-2" href="../change_password.php"><i class="bi bi-key me-2"></i> تغییر رمز عبور</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> خروج</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container py-4 py-md-5">
