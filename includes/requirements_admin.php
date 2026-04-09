<?php
// No PHP logic needed unless you want to handle sessions or routing
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Category</title>

    <!-- FONT AWESOME ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- EXTERNAL CSS -->
    <link rel="stylesheet" href="../css/section.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <?php include ('../includes/sidebar_admin.php'); ?>

<div class="wrapper">
    <div class="card-container">
        <h2>Please select a section</h2>
        <p class="subtitle">
            Choose the type of information you want to explore below.
        </p>

        <div class="options">

            <a href="/internshipms/admin/saforms.php" class="option-box">
                <i class="fa-solid fa-file"></i>
                <div class="option-title">Accomplished Forms</div>
            </a>

            <a href="/internshipms/admin/ojtforms.php" class="option-box">
                <i class="fas fa-sticky-note"></i>
                <div class="option-title">Form Templates</div>
            </a>

            <a href="/internshipms/admin/reports.php" class="option-box">
                <i class='fa fa-vcard'></i>
                <div class="option-title">Reports</div>
            </a>

            <a href="dtr_monitoring.php" class="option-box">
                <i class="fa-solid fa-hourglass-end"></i>
                <div class="option-title">DTR Monitoring</div>
            </a>

            <a href="/internshipms/admin/moa_tracking.php" class="option-box">
                <i class="fa-solid fa-timeline"></i>
                <div class="option-title">MOA Tracking</div>
            </a>

        </div>
    </div>
</div>

</body>
</html>
