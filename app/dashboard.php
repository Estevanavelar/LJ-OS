<?php
/**
 * LJ-OS - Dashboard
 */

// Carregar autoloader
require_once __DIR__ . '/../autoload.php';

// Carregar sistema de localização
$localization = LJOS\Utils\Localization::getInstance();

// Aplicar configurações de tema e idioma
$localization->applySettings();

// Verificar se está logado
session_start();
$token = $_SESSION['token'] ?? $_COOKIE['token'] ?? null;

if (!$token) {
    // Se não tem token, redirecionar para login
    header('Location: ../app/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html <?= $localization->getHtmlAttributes() ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $localization->get('app_name') ?> - <?= $localization->get('dashboard') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Temas CSS -->
    <link href="../app/assets/css/themes.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tools"></i> <?= $localization->get('app_name') ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <!-- Controles de Tema -->
                <div class="nav-item me-2">
                    <button class="btn btn-outline-light btn-sm theme-toggle" title="<?= $localization->get('theme') ?>">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
                <div class="nav-item me-2">
                    <button class="btn btn-outline-light btn-sm contrast-toggle" title="<?= $localization->get('contrast') ?>">
                        <i class="fas fa-adjust"></i>
                    </button>
                </div>
                <div class="nav-item me-2">
                    <button class="btn btn-outline-light btn-sm language-toggle" title="<?= $localization->get('language') ?>">
                        <i class="fas fa-flag"></i>
                    </button>
                </div>
                
                <!-- Usuário -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> <?= $localization->get('settings') ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> <?= $localization->get('logout') ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-tachometer-alt"></i> <?= $localization->get('dashboard') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-users"></i> <?= $localization->get('clients') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-car"></i> <?= $localization->get('vehicles') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-tools"></i> <?= $localization->get('services') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-calendar-alt"></i> <?= $localization->get('appointments') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-clipboard-list"></i> <?= $localization->get('orders') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-box"></i> <?= $localization->get('products') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-warehouse"></i> <?= $localization->get('stock') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-line"></i> <?= $localization->get('finance') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-bar"></i> <?= $localization->get('reports') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Conteúdo Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $localization->get('dashboard') ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"><?= $localization->get('export') ?></button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"><?= $localization->get('print') ?></button>
                        </div>
                    </div>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            <?= $localization->get('total_clients') ?>
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">150</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            <?= $localization->get('monthly_revenue') ?>
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">R$ 15.000</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            <?= $localization->get('total_appointments') ?>
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">25</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            <?= $localization->get('total_vehicles') ?>
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">180</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-car fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo Adicional -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><?= $localization->get('recent_activities') ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted"><?= $localization->get('no_records_found') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><?= $localization->get('settings') ?></h5>
                            </div>
                            <div class="card-body">
                                <a href="../app/components/theme-settings.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-cog"></i> <?= $localization->get('settings') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Manager -->
    <script src="../app/assets/js/theme-manager.js"></script>
    
    <script>
        // Função de logout
        function logout() {
            // Limpar tokens
            localStorage.removeItem('auth_token');
            sessionStorage.removeItem('auth_token');
            
            // Redirecionar para login
            window.location.href = '../app/login.php';
        }
        
        // Verificar se está logado
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '../app/login.php';
            }
        });
    </script>
</body>
</html>
