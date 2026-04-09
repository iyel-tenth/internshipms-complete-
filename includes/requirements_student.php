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
    
    <?php include ('../includes/sidebar_student.php'); ?>

<div class="wrapper">
    <div class="card-container">
        <h2>Please select a section</h2>
        <p class="subtitle">
            Choose the type of information you want to explore below.
        </p>

        <div class="options">

            <a href="../student/saforms.php" class="option-box">
                <i class="far fa-file-alt"></i>
                <div class="option-title">Forms</div>
            </a>

            <a href="../includes/dtr_student.php" class="option-box">
                <i class="fa-solid fa-hourglass-end"></i>
                <div class="option-title">Daily Time Record</div>
            </a>

            <a href="../student/moaTracking_student.php" class="option-box">
                <i class="fa-solid fa-timeline"></i>
                <div class="option-title">MOA Tracking</div>
            </a>

            <a href="../student/reports_student.php" class="option-box">
                <i class='fa fa-vcard'></i>
                <div class="option-title">Reports</div>
            </a>

        </div>
    </div>
</div>

</body>
</html>