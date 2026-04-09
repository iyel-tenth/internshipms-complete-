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

                <!-- Home -->
                <a href="/internshipms/student/dashboard.php"
                   class="nav__link <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                    <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Dashboard</span>
                </a>

                <!-- Requirements -->
                <div class="nav__link collapse 
                    <?= in_array($currentPage, ['requirements_student.php','saforms.php']) ? 'active' : '' ?>">

                    <ion-icon name="list-outline" class="nav__icon"></ion-icon>

                    <a href="/internshipms/includes/requirements_student.php"
                       class="nav__name"
                       style="color: inherit; text-decoration: none;">
                        Requirements
                    </a>

                    <ion-icon name="chevron-down-outline" class="collapse__link"></ion-icon>

                    <ul class="collapse__menu 
                        <?= in_array($currentPage, ['saforms.php']) ? 'showCollapse' : '' ?>">

                        <a href="/internshipms/student/saforms.php"
                           class="collapse__sublink <?= ($currentPage == 'saforms.php') ? 'active' : '' ?>">
                           AccomplishedForms
                        </a>

                        <a href="/internshipms/includes/dtr_student.php"
                           class="collapse__sublink <?= ($currentPage == 'dtr_student.php') ? 'active' : '' ?>">
                           DTR
                        </a>

                        <a href="/internshipms/student/moaTracking_student.php"
                           class="collapse__sublink <?= ($currentPage == 'moaTracking_student.php') ? 'active' : '' ?>">
                           MOATracking
                        </a>

                    </ul>
                </div>

                <!-- Activities -->
                <div class="nav__link collapse 
                    <?= in_array($currentPage, ['activities_student.php','orientations.php']) ? 'active' : '' ?>">

                    <ion-icon name="folder-outline" class="nav__icon"></ion-icon>

                    <a href="../includes/activities_student.php"
                       class="nav__name"
                       style="color: inherit; text-decoration: none;">
                        Activities
                    </a>

                    <ion-icon name="chevron-down-outline" class="collapse__link"></ion-icon>

                    <ul class="collapse__menu 
                        <?= ($currentPage == 'orientations.php') ? 'showCollapse' : '' ?>">

                        <a href="/internshipms/student/orientations.php"
                           class="collapse__sublink <?= ($currentPage == 'orientations.php') ? 'active' : '' ?>">
                           Orientations
                        </a>

                        <a href="/internshipms/student/internslots_student.php"
                           class="collapse__sublink <?= ($currentPage == 'internslots_student.php') ? 'active' : '' ?>">
                           InternshipSlots
                        </a>

                    </ul>
                </div>

               <!-- Settings -->
               <a href="/internshipms/includes/settings_student.php"
                  class="nav__link <?= ($currentPage == 'settings_student.php') ? 'active' : '' ?>">
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