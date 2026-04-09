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
    <link rel="stylesheet" href="css/role.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include ('includes/sidebar2.php'); ?>

<div class="wrapper">
    <div class="card-container">
        <h2>Please select a section</h2>
        <p class="subtitle">
            Choose the type of information you want to explore below.
        </p>

        <div class="options">

            <a href="/internshipms/student/forms.php" class="option-box">
                <i class="far fa-file-alt"></i>
                <div class="option-title">Forms</div>
            </a>

            <a href="form-templates.php" class="option-box">
                <i class="fas fa-sticky-note"></i>
                <div class="option-title">Form Templates</div>
            </a>

            <a href="job_listings.php" class="option-box">
                <i class="fas fa-briefcase"></i>
                <div class="option-title">Job Listings</div>
            </a>

        </div>
    </div>
</div>

</body>
</html>
