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
    <link rel="stylesheet" href="../css/section_admin.css">
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
            <a href="/internshipms/admin/orientations.php" class="option-box">
                <i class="fas fa-calendar-alt"></i>
                <div class="option-title">Orientation</div>
            </a>

            <a href="/internshipms/admin/internslots.php" class="option-box">
                <i class="fas fa-briefcase"></i>
                <div class="option-title">Internship Slots</div>
            </a>

        </div>
    </div>
</div>

</body>
</html>
