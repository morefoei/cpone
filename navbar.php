<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
            <img src="/assets/img/logo-ueu-unggul.png" alt="Logo Esa Unggul" height="40" class="me-2 bg-white rounded p-1">
            E-Resume
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="/form/eresume">E-Resume</a></li>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="/dashboard">Dashboard Laporan</a></li>
                
                <li class="nav-item dropdown ms-lg-3 mt-3 mt-lg-0">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center bg-primary rounded-pill px-3 py-2 shadow-sm" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 14px;">👤</span>
                        <span class="fw-bold" style="font-size: 0.95rem;"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Akun Saya') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="profileDropdown" style="border-radius: 12px; min-width: 200px;">
                        <li><a class="dropdown-item py-2 fw-semibold" href="/profile">✏️ Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 fw-semibold text-danger" href="/logout">🚪 Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>