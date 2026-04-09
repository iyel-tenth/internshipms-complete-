<?php
session_start();
include '../db_connect.php';

/* ===============================
   ADMIN ACCESS PROTECTION
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

/* ===============================
   VERIFY ADMIN EXISTS IN DB
================================ */
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'Admin'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$admin = $result->fetch_assoc();
$admin_name = $admin['first_name'];

/* =========================
   HANDLE AJAX FOR ALL CHART DATA
========================= */
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {

    /* ROLE DISTRIBUTION */
$roleLabels = [];
$roleTotals = [];

/* Count users table */
$roleSql1 = "SELECT role, COUNT(*) as total FROM users GROUP BY role";
$result1 = mysqli_query($conn, $roleSql1);

if ($result1) {
    while ($row = mysqli_fetch_assoc($result1)) {
        $roleLabels[] = ucfirst($row['role']);
        $roleTotals[] = $row['total'];
    }
}

/* Count students separately */
$studentCountResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM student_users");
$studentCount = mysqli_fetch_assoc($studentCountResult);

$roleLabels[] = "Student";
$roleTotals[] = $studentCount['total'];

    /* STUDENT COURSE DISTRIBUTION */
    $studentLabels = [];
    $studentTotals = [];

    $studentSql = "SELECT course_major, COUNT(*) as total
                   FROM student_users
                   GROUP BY course_major";

    $studentResult = mysqli_query($conn, $studentSql);

    if ($studentResult) {
        while ($row = mysqli_fetch_assoc($studentResult)) {
            if (!empty($row['course_major'])) {
                $studentLabels[] = $row['course_major'];
                $studentTotals[] = $row['total'];
            }
        }
    }

    /* ADMIN COURSE DISTRIBUTION */
    $adminLabels = [];
    $adminTotals = [];

    $adminSql = "SELECT course_major, COUNT(*) as total
                 FROM users
                 WHERE role = 'Admin'
                 GROUP BY course_major";

    $adminResult = mysqli_query($conn, $adminSql);

    if ($adminResult) {
        while ($row = mysqli_fetch_assoc($adminResult)) {
            if (!empty($row['course_major'])) {
                $adminLabels[] = $row['course_major'];
                $adminTotals[] = $row['total'];
            }
        }
    }

    /* FORM TEMPLATE STATUS */
    $formStatusLabels = [];
    $formStatusTotals = [];

    $formSql = "SELECT 
                    CASE 
                        WHEN is_published = 1 THEN 'Published'
                        ELSE 'Unpublished'
                    END as status,
                    COUNT(*) as total
                FROM form_templates
                GROUP BY status";

    $formResult = mysqli_query($conn, $formSql);

    if ($formResult) {
        while ($row = mysqli_fetch_assoc($formResult)) {
            $formStatusLabels[] = $row['status'];
            $formStatusTotals[] = $row['total'];
        }
    }

    /* MOA TRACKING STATUS */
    $moaLabels = ['Processed MOA', 'Ongoing MOA'];
    $moaTotals = [0, 0];

    $moaSql = "SELECT 
                    CASE 
                        WHEN step7 = 1 THEN 'Processed'
                        ELSE 'Ongoing'
                    END as status,
                    COUNT(*) as total
                FROM moa_tracking
                GROUP BY status";

    $moaResult = mysqli_query($conn, $moaSql);

    if ($moaResult) {
        while ($row = mysqli_fetch_assoc($moaResult)) {
            if ($row['status'] === 'Processed') {
                $moaTotals[0] = $row['total'];
            } else {
                $moaTotals[1] = $row['total'];
            }
        }
    }

    // FIX: Force clean JSON output 
    header('Content-Type: application/json');


    echo json_encode([
        "roleLabels" => $roleLabels,
        "roleTotals" => $roleTotals,
        "studentLabels" => $studentLabels,
        "studentTotals" => $studentTotals,
        "adminLabels" => $adminLabels,
        "adminTotals" => $adminTotals,
        "formStatusLabels" => $formStatusLabels,
        "formStatusTotals" => $formStatusTotals,
        "moaLabels" => $moaLabels,
        "moaTotals" => $moaTotals
    ]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
    /* ================= DASHBOARD CONTAINER ================= */
    .dashboard-container {
        padding: 20px;
        background-color: rgba(255, 251, 0, 0.33);
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .dashboard-header h2 {
        margin: 0;
        color: #0A1D56;
        font-size: 1.8rem;
    }

    .dashboard-header p {
        margin: 5px 0 0 0;
        color: #666;
    }

    /* ================= STATISTICS CARDS ================= */
    .stats-box {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #0A1D56;
        margin: 0;
    }

    .stat-label {
        color: #midnightblue;
        margin-top: 8px;
        font-size: 0.95rem;
    }

    .stat-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    /* ================= CHARTS WRAPPER ================= */
    .charts-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin: 30px 0;
    }

    .chart-box {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .chart-box:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .chart-title {
        color: #0A1D56;
        font-weight: 600;
        margin-bottom: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    canvas {
        max-height: 300px;
    }

    /* ================= RESPONSIVE DESIGN ================= */
    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .charts-wrapper {
            grid-template-columns: 1fr;
        }

        .stats-box {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .stats-box {
            grid-template-columns: 1fr;
        }

        .stat-value {
            font-size: 2rem;
        }

        .dashboard-header h2 {
            font-size: 1.5rem;
        }
    }
    /* ======================================================== */
    </style>
</head>

<body>

<?php include ('../includes/sidebar_admin.php'); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h2>📊 ADMIN DASHBOARD</h2>
            <p>System Overview and Analytics</p>
            
        </div>
    </div>

    <!-- STATISTICS SECTION -->
    <div class="stats-box">
        <?php
        // Total Users
        $total_users_query = "SELECT COUNT(*) as count FROM users";
        $total_users_result = $conn->query($total_users_query);
        $total_users_row = $total_users_result->fetch_assoc();
        $total_users = $total_users_row['count'];

        // Total Students
        $total_students_query = "SELECT COUNT(*) as count FROM student_users";
        $total_students_result = $conn->query($total_students_query);
        $total_students_row = $total_students_result->fetch_assoc();
        $total_students = $total_students_row['count'];

        // Total Form Templates
        $total_forms_query = "SELECT COUNT(*) as count FROM form_templates";
        $total_forms_result = $conn->query($total_forms_query);
        $total_forms_row = $total_forms_result->fetch_assoc();
        $total_forms = $total_forms_row['count'];

        // Total MOA Tracking
        $total_moa_query = "SELECT COUNT(*) as count FROM moa_tracking";
        $total_moa_result = $conn->query($total_moa_query);
        $total_moa_row = $total_moa_result->fetch_assoc();
        $total_moa = $total_moa_row['count'];
        ?>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <p class="stat-value"><?php echo $total_users; ?></p>
            <p class="stat-label">Total Users</p>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <p class="stat-value"><?php echo $total_students; ?></p>
            <p class="stat-label">Total Students</p>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <p class="stat-value"><?php echo $total_forms; ?></p>
            <p class="stat-label">Form Templates</p>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📑</div>
            <p class="stat-value"><?php echo $total_moa; ?></p>
            <p class="stat-label">MOA Records</p>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <h3 style="color: #0A1D56; margin-top: 40px; margin-bottom: 20px;">📈 Analytics</h3>

    <!-- CHARTS -->
    <div class="charts-wrapper">
        <div class="chart-box">
            <p class="chart-title">User Distribution by Role</p>
            <canvas id="rolePieChart"></canvas>
        </div>

        <div class="chart-box">
            <p class="chart-title">Student Course Distribution</p>
            <canvas id="studentCourseChart"></canvas>
        </div>

        <div class="chart-box">
            <p class="chart-title">Admin Distribution</p>
            <canvas id="adminCourseChart"></canvas>
        </div>

        <div class="chart-box">
            <p class="chart-title">Form Templates Status</p>
            <canvas id="formTemplateChart"></canvas>
        </div>

        <div class="chart-box">
            <p class="chart-title">MOA Tracking Status</p>
            <canvas id="moaChart"></canvas>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {

    var roleChart = new Chart(document.getElementById('rolePieChart'), {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#0A1D56', '#b8860b'],
                borderColor: ['#0A1D56', '#b8860b'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    var studentChart = new Chart(document.getElementById('studentCourseChart'), {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#0A1D56', '#b8860b','#d6e4ff'],
                borderColor:  ['#0A1D56', '#b8860b','#d6e4ff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    var adminChart = new Chart(document.getElementById('adminCourseChart'), {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#0A1D56', '#112b85', '#1e5ba8', '#d6e4ff'],
                borderColor: ['#0A1D56', '#112b85', '#1e5ba8', '#d6e4ff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    var formTemplateChart = new Chart(document.getElementById('formTemplateChart'), {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#0039a4', '#fff56fe4'],
                borderColor: ['#0039a4', '#fff56fe4'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    var moaChart = new Chart(document.getElementById('moaChart'), {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#99cfff', '#f39c12'],
                borderColor: ['#99cfff', '#f39c12'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    function updateCharts() {
        $.ajax({
            url: window.location.pathname,
            type: "GET",
            data: { ajax: 1 },
            dataType: "json",
            success: function(response) {

                roleChart.data.labels = response.roleLabels;
                roleChart.data.datasets[0].data = response.roleTotals;
                roleChart.update();

                studentChart.data.labels = response.studentLabels;
                studentChart.data.datasets[0].data = response.studentTotals;
                studentChart.update();

                adminChart.data.labels = response.adminLabels;
                adminChart.data.datasets[0].data = response.adminTotals;
                adminChart.update();

                formTemplateChart.data.labels = response.formStatusLabels;
                formTemplateChart.data.datasets[0].data = response.formStatusTotals;
                formTemplateChart.update();

                moaChart.data.labels = response.moaLabels;
                moaChart.data.datasets[0].data = response.moaTotals;
                moaChart.update();
            }
        });
    }

    updateCharts();
});
</script>

</body>
</html>