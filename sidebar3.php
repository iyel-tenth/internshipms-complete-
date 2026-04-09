<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/sidebar.css">
        <title>Admin | Admin</title>
        
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
                        <a href="#" class="nav__link active">
                            <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Dashboard</span>
                        </a>
                        <a href="#" class="nav__link">
                            <ion-icon name="chatbubbles-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Internbuddy</span>
                        </a>

                        <div  class="nav__link collapse">
                            <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Requirements</span>

                            <ion-icon name="chevron-down-outline" class="collapse__link"></ion-icon>

                            <ul class="collapse__menu">
                                <a href="#" class="collapse__sublink">Forms</a>
                                <a href="#" class="collapse__sublink">DTR</a>
                                <a href="#" class="collapse__sublink">Time</a>
                            </ul>
                        </div>

                        <a href="#" class="nav__link">
                            <ion-icon name="pie-chart-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Analytics</span>
                        </a>
                        
                        <div  class="nav__link collapse">
                            <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Activities</span>

                            <ion-icon name="chevron-down-outline" class="collapse__link"></ion-icon>

                            <ul class="collapse__menu">
                                <a href="#" class="collapse__sublink">Orientation</a>
                                <a href="#" class="collapse__sublink">DTR</a>
                                <a href="#" class="collapse__sublink">Time</a>
                            </ul>
                        </div>

                        
                        <a href="#" class="nav__link">
                            <ion-icon name="settings-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Settings</span>
                        </a>
                    </div>
                </div>

                <a href="#" class="nav__link">
                    <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Log Out</span>
                </a>
            </nav>
        </div>

        <br><hr>

        <br><h1>Welcome,</h1> 
        <h1>@user</h1>
        <p>What would you like to do?</p>
        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/sidebar.js"></script>
    </body>
</html>