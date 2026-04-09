<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Sidebar</title>
</head>

<body id="body-pd">

<div class="l-navbar" id="navbar">
    <nav class="nav">
        <div>
            <div class="nav__brand">
                <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                <a href="#" class="nav__logo">Internbuddy</a>
            </div>

            <div class="nav__list">

                <!-- Dashboard -->
                <a href="/internshipms/admin/dashboard.php"
                   class="nav__link <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                    <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Dashboard</span>
                </a>

                <!-- Users -->
                <a href="/internshipms/admin/users.php"
                   class="nav__link <?= ($currentPage == 'users.php') ? 'active' : '' ?>">
                    <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Users</span>
                </a>


                <!-- Requirements -->
                <div class="nav__link collapse 
                    <?= in_array($currentPage, ['requirements_admin.php','ojtforms.php','saforms.php']) ? 'active' : '' ?>">

                    <ion-icon name="list-outline" class="nav__icon"></ion-icon>

                    <a href="/internshipms/includes/requirements_admin.php"
                       class="nav__name"
                       style="color: inherit; text-decoration: none;">
                        Requirements
                    </a>

                    <ion-icon name="chevron-down-outline" class="collapse__link"></ion-icon>

                    <ul class="collapse__menu 
                        <?= in_array($currentPage, ['ojtforms.php','saforms.php']) ? 'showCollapse' : '' ?>">

                        <a href="/internshipms/admin/saforms.php"
                           class="collapse__sublink <?= ($currentPage == 'saforms.php') ? 'active' : '' ?>">
                           AccomplishedForms
                        </a>

                        <a href="/internshipms/admin/ojtforms.php"
                           class="collapse__sublink <?= ($currentPage == 'ojtforms.php') ? 'active' : '' ?>">
                           FormTemplates
                        </a>

                        <a href="/internshipms/admin/reports.php"
                           class="collapse__sublink <?= ($currentPage == 'reports.php') ? 'active' : '' ?>">
                           Reports
                        </a>

                        <a href="/internshipms/includes/dtr_monitoring.php"
                           class="collapse__sublink <?= ($currentPage == 'dtr_monitoring.php') ? 'active' : '' ?>">
                           DTRMonitoring
                        </a>

                         <a href="/internshipms/admin/moa_tracking.php"
                           class="collapse__sublink <?= ($currentPage == 'moa_tracking.php') ? 'active' : '' ?>">
                           MOATracking
                        </a>


                    </ul>
                </div>

                <!-- Activities -->
                <div class="nav__link collapse 
                    <?= in_array($currentPage, ['activities_admin.php','orientations.php']) ? 'active' : '' ?>">

                    <ion-icon name="folder-outline" class="nav__icon"></ion-icon>

                    <a href="/internshipms/includes/activities_admin.php"
                       class="nav__name"
                       style="color: inherit; text-decoration: none;">
                        Activities
                    </a>

                    <ion-icon name="chevron-down-outline" class="collapse__link"></ion-icon>

                    <ul class="collapse__menu 
                        <?= ($currentPage == 'orientations.php') ? 'showCollapse' : '' ?>">

                        <a href="/internshipms/admin/orientations.php"
                           class="collapse__sublink <?= ($currentPage == 'orientations.php') ? 'active' : '' ?>">
                           Orientations
                        </a>

                        <a href="/internshipms/admin/internslots.php"
                           class="collapse__sublink <?= ($currentPage == 'internslots.php') ? 'active' : '' ?>">
                           InternshipSlots
                        </a>
                    </ul>
                </div>
                <!-- Settings -->
                <a href="/internshipms/includes/settings_admin.php"
                   class="nav__link <?= ($currentPage == 'settings_admin.php') ? 'active' : '' ?>">
                    <ion-icon name="settings-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Settings</span>
                </a>

            </div>
        </div>

        <!-- Logout -->
        <a href="../login.php" class="nav__link">
            <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
            <span class="nav__name">Log Out</span>
        </a>

    </nav>
</div>

<br>
<hr>

<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="../js/sidebar.js"></script>

</body>
</html>