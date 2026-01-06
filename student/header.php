<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'پنل دانشجو'; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { font-family: Tahoma, Arial; background-color: #f4f7f6; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-person-badge me-2"></i> <?php echo $_SESSION['full_name']; ?> (دانشجو)
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="studentNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-house"></i> داشبورد</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_reports.php"><i class="bi bi-file-earmark-text"></i> گزارشات من</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="../logout.php"><i class="bi bi-box-arrow-right"></i> خروج</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
