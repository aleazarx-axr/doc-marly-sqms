<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin · Nexus</title>

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <!-- Google Font (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet" />

    <style>
        /* minimal custom overrides – only what Bootstrap cannot do */
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6fc;
            overflow-x: hidden;
        }

        #sidebar {
            width: 280px;
            min-height: 100vh;
            background: linear-gradient(145deg, #1f1e4e 0%, #2a296f 100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 10px;
        }

        /* brand / logo */
        .brand-icon {
            background: rgba(255, 255, 255, 0.08);
            padding: 8px 10px;
            border-radius: 14px;
            font-size: 1.8rem;
            color: #a5b4ff;
        }

        /* nav links – use bootstrap classes, but fine-tune gap & hover */
        .nav-link-custom {
            color: rgba(255, 255, 255, 0.75) !important;
            font-weight: 500;
            border-radius: 12px;
            padding: 0.65rem 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 14px;
        }

        .nav-link-custom i {
            font-size: 1.3rem;
            width: 1.8rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            transition: 0.2s;
        }

        .nav-link-custom:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            transform: translateX(4px);
        }

        .nav-link-custom:hover i {
            color: #bcc6ff;
        }

        .nav-link-custom.active {
            background: rgba(255, 255, 255, 0.15) !important;
            color: #fff !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-link-custom.active i {
            color: #d6e0ff;
        }

        /* accordion overrides */
        .accordion-button-custom {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0.65rem 1rem;
            color: rgba(255, 255, 255, 0.75) !important;
            font-weight: 500;
            border-radius: 12px;
            gap: 14px;
        }

        .accordion-button-custom:not(.collapsed) {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #fff !important;
            border-radius: 12px 12px 0 0;
        }

        .accordion-button-custom::after {
            filter: brightness(0) invert(0.8);
            background-size: 1.2rem;
        }

        .accordion-button-custom:not(.collapsed)::after {
            filter: brightness(0) invert(1);
        }

        .accordion-button-custom i {
            font-size: 1.3rem;
            width: 1.8rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
        }

        .accordion-body-custom {
            padding: 0.3rem 0 0.3rem 1.2rem;
        }

        .accordion-body-custom .nav-link-custom {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .accordion-body-custom .nav-link-custom i {
            font-size: 1.1rem;
            width: 1.6rem;
        }

        /* divider */
        .sidebar-divider {
            height: 1px;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.01));
        }

        /* external header */
        .external-header {
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* logout button */
        .logout-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
            border-radius: 40px;
            padding: 0.7rem 1rem;
            font-weight: 500;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logout-btn:hover {
            background: rgba(255, 70, 70, 0.15);
            border-color: rgba(255, 120, 120, 0.25);
            color: #ffb3b3;
        }

        /* mobile toggle */
        .menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1070;
            background: #2a296f;
            border: none;
            color: #fff;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            transition: 0.2s;
        }

        .menu-toggle:hover {
            background: #3f3d8a;
            transform: scale(1.03);
        }

        .menu-toggle i {
            font-size: 1.8rem;
        }

        /* overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(4px);
            z-index: 1055;
        }

        .sidebar-overlay.active {
            display: block;
        }

        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                left: 0;
                top: 0;
                transform: translateX(-100%);
                width: 290px;
                height: 100vh;
                z-index: 1060;
                border-radius: 0 20px 20px 0;
                box-shadow: 8px 0 30px rgba(0, 0, 0, 0.3);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                padding: 1.5rem 1rem !important;
            }

            #sidebar.show {
                transform: translateX(0);
            }

            .menu-toggle {
                display: flex !important;
            }

            .content {
                padding: 80px 1rem 2rem !important;
            }
        }

        /* content cards */
        .stat-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.02);
            transition: 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
        }

        .stat-icon {
            color: #2a296f;
            font-size: 2rem;
        }
    </style>
</head>

<body>

    <!-- Mobile Toggle -->
    <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>
    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="d-flex">

        <!-- ===== SIDEBAR ===== (mostly Bootstrap classes) -->
        <aside id="sidebar" class="text-white p-3 d-flex flex-column">

            <!-- Brand -->
            <div class="d-flex align-items-center gap-3 pb-4 mb-3 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
                <img src="assets/images/docmarly.png" alt="User Icon" class="mb-3" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                <div>
                    <span class="fw-bold fs-5">DOC MARLY</span>
                    <small class="d-block text-white-50" style="font-size:0.6rem;">Smart Queing Management System</small>
                </div>
            </div>

            <!-- Nav -->
            <ul class="nav nav-pills flex-column mb-2">

                <!-- Dashboard -->
                <li class="nav-item mb-1">
                    <a href="/index.php" class="nav-link-custom active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <!-- Service Management accordion -->
                <li class="nav-item mb-1">
                    <div class="accordion accordion-flush" id="serviceMenu">
                        <div class="accordion-item bg-transparent border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button accordion-button-custom collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#services">
                                    <i class="bi bi-briefcase-fill"></i> Service Management
                                </button>
                            </h2>
                            <div id="services" class="accordion-collapse collapse">
                                <div class="accordion-body accordion-body-custom">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a href="/modules/office_services/index.php" class="nav-link-custom">
                                                <i class="bi bi-building"></i> Office Services
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="/modules/field_services/index.php" class="nav-link-custom">
                                                <i class="bi bi-tools"></i> Field Services
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Records -->
                <li class="nav-item mb-1">
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-archive-fill"></i> Records
                    </a>
                </li>

                <!-- Users -->
                <li class="nav-item mb-1">
                    <a href="/modules/users/index.php" class="nav-link-custom">
                        <i class="bi bi-people-fill"></i> User Management
                    </a>
                </li>

                <!-- Devices -->
                <li class="nav-item mb-1">
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-device-hdd-fill"></i> Devices
                    </a>
                </li>

                <!-- Settings -->
                <li class="nav-item mb-1">
                    <a href="#" class="nav-link-custom">
                        <i class="bi bi-sliders2"></i> Settings
                    </a>
                </li>
            </ul>

            <!-- Divider -->
            <hr class="sidebar-divider my-3" />

            <!-- External -->
            <div class="external-header d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-display"></i> EXTERNAL
            </div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item ">
                    <a href="#" class="nav-link-custom bg-warning text-dark">
                        <i class="bi bi-tv-fill text-danger"></i> Live Display
                    </a>
                </li>
            </ul>

            <!-- Spacer + logout -->
            <div class="mt-auto pt-3">
                <form action="/logout.php" method="post">
                    <button class="btn logout-btn w-100" type="submit">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('active');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('show');
            overlay.classList.remove('active');
        }

        // close on resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('active');
            }
        });

        // highlight active link based on current path (simple)
        document.addEventListener('DOMContentLoaded', function() {
            const path = window.location.pathname;
            document.querySelectorAll('.nav-link-custom').forEach(link => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && path.includes(href.replace(/^\/+/, ''))) {
                    document.querySelectorAll('.nav-link-custom').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>

</html>