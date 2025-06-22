<?php 
include("auth-check.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
      <!-- Basic Meta Tags -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pemaleeda Blocks Dashboard</title>
  <meta name="description" content="Manage listings, orders, and reports on the Zinggati Blocks Dashboard — the admin portal for Zambia’s leading online marketplace." />
  <meta name="keywords" content="Zinggati, Zambia marketplace, admin dashboard, inventory management, orders, reports, classifieds, e-commerce Zambia" />
  <meta name="author" content="Zinggati Enterprises Ltd." />
  <meta name="robots" content="index, follow" />

  <!-- Open Graph / Facebook -->
  <meta property="og:title" content="Zinggati Blocks Dashboard" />
  <meta property="og:description" content="Administer your marketplace with Zinggati’s intuitive dashboard for listings, orders, and analytics." />
  <meta property="og:image" content="https://zinggati.com/assets/img/logo.png" />
  <meta property="og:url" content="https://zinggati.com/dashboard" />
  <meta property="og:type" content="website" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Zinggati Blocks Dashboard" />
  <meta name="twitter:description" content="Manage your Zinggati marketplace operations with ease." />
  <meta name="twitter:image" content="https://zinggati.com/assets/img/logo.png" />

  <!-- Favicon -->
  <link rel="icon" href="https://zinggati.com/assets/img/favicon.ico" type="image/x-icon" />

  <!-- Canonical URL -->
  <link rel="canonical" href="https://zinggati.com/dashboard" />

   <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'fr' // Only include French (language code for French)
      }, 'google_translate_element');
    }
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <title>Zinggati Blocks Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header content remains the same -->
      <!-- Header -->
    <header class="custom-header-footer fixed-top">
      <div class="container h-100">
        <div class="d-flex justify-content-between align-items-center h-100">
          <div class="d-flex align-items-center gap-3">
            <button
              class="btn text-white"
              type="button"
              data-bs-toggle="offcanvas"
              data-bs-target="#sidebarMenu"
              aria-controls="sidebarMenu"
              aria-label="Open menu"
            >
              <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-baseline">
              <h2 class="text-white mb-0">Pemaleeda</h2>
              <span class="text-white ms-2" style="font-size: 0.8rem"
                >Blocks</span
              >
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
              <button
                class="btn text-white"
                type="button"
                id="notificationDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifications"
              >
                <i class="fas fa-bell"></i>
              </button>
              <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="notificationDropdown"
              >
                <li>
                  <a class="dropdown-item" href="#">New order received</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#">Inventory low: Cement</a>
                </li>
                <li>
                  <a class="dropdown-item" href="#">Production target met</a>
                </li>
              </ul>
            </div>
            <button
              class="profile-icon rounded-circle d-flex align-items-center justify-content-center bg-white"
              data-bs-toggle="modal"
              data-bs-target="#profileModal"
              aria-label="Profile"
            >
              <i class="fas fa-user text-primary"></i>
            </button>
          </div>
        </div>
      </div>
    </header>
<main class="container main-content"><div class="col-md-9 col-lg-10 p-4">
        <?php
        function activePage($pageName) {
          return (isset($_GET['page']) && $_GET['page'] == $pageName) ? 'active-link' : '';
        }
        ?>