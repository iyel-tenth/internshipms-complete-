<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$student_id = $_SESSION['user_id'];

// Sections (same structure)
$sections = [
    "Before Deployment" => [
        'in_campus_ojt' => 'In-Campus OJT Certificate',
        'pre_ojt_seminar' => 'Pre-OJT Seminar Certificate',
        'medical_cert' => 'Medical Certificate',
    ],
    "During Deployment" => [
        'week1' => 'Week 1 Narrative Report',
        'week2' => 'Week 2 Narrative Report',
        'week3' => 'Week 3 Narrative Report',
        'week4' => 'Week 4 Narrative Report',
        'week5' => 'Week 5 Narrative Report',
    ],
    "After Deployment" => [
        'signed_dtr' => 'Signed DTR',
        'signed_timeframe' => 'Signed Daily Time Frame',
        'student_eval' => 'Student Evaluation Form',
        'hte_eval' => 'HTE Evaluation Form',
    ]
];

// Fetch uploaded files
$student_reports = [];
$stmt = $conn->prepare("SELECT doc_type, file_path FROM student_reports WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $student_reports[$row['doc_type']] = $row['file_path'];
}
$stmt->close();

// UI Renderer (keeps your design)
function renderUploadUI($doc_type, $student_reports) {
    $uploaded = isset($student_reports[$doc_type]);
    $file = $uploaded ? basename($student_reports[$doc_type]) : '';

    if ($uploaded) {
        return '
        <div>
            <div style="display:flex; gap:8px; align-items:center;">
                <span class="report-btn" style="background:#28a745; color:white; padding:10px 14px;">
                    <i class="fa fa-check"></i>
                </span>
                <button class="report-btn" type="button" style="background:#ff4d4d; color:white; padding:10px 14px;" onclick="handleDelete(\''.$doc_type.'\')">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <small class="file-note" title="'.htmlspecialchars($file).'">
    		<a href="../'.htmlspecialchars($student_reports[$doc_type]).'" target="_blank" style="text-decoration:none; color:inherit;">
      		  <i class="fa fa-paperclip"></i> '.htmlspecialchars($file).'
   			 </a>
			</small>
        </div>';
    } else {
        return '
        <div style="display:flex; gap:8px; align-items:center;">
            <label class="report-btn" style="margin:0; padding:10px 14px; display:flex; align-items:center; cursor:pointer;">
                <i class="fa fa-upload"></i>
                <input type="file" accept="application/pdf" style="display:none;" onchange="handleFileUpload(this, \''.$doc_type.'\')">
            </label>
        </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/dashboard.css">

<style>
.reports-container {
    padding: 20px;
    background-color: rgba(255, 251, 0, 0.15);
    border-radius: 10px;
    margin-bottom: 20px;
}
.reports-header {
    margin-bottom: 30px;
}
.reports-header h2 {
    margin: 0;
    color: #0A1D56;
    font-size: 1.8rem;
}
.reports-header p {
    margin: 5px 0 0 0;
    color: #666;
}
.horizontal-containers {
    display: flex;
    gap: 20px;
    margin-top: 30px;
}
.report-section {
    flex: 1;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 24px 18px;
    min-height: 420px;
    display: flex;
    flex-direction: column;
}
.report-section h3 {
    margin-top: 0;
    margin-bottom: 12px;
    color: #0A1D56;
}
.report-table {
    width: 100%;
    border-collapse: collapse;
}
.report-table th, .report-table td {
    border-bottom: 1px solid #e0e0e0;
    padding: 10px 8px;
    text-align: left;
}
.report-table th {
    background: #f7f7f7;
    color: #0A1D56;
}
.report-btn {
    background: #ffd700;
    color: #0A1D56;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.report-btn:hover {
    background: #ffe066;
}

.file-note {
    display: block;
    margin-top: 4px;
    font-size: 0.7rem;        /* smaller text */
    color: green;
    max-width: 180px;         /* limits width */
    white-space: nowrap;      /* single line */
    overflow: hidden;
    text-overflow: ellipsis;  /* adds ... */
}

</style>
</head>

<body>

<?php include ('../includes/sidebar_student.php'); ?>

<div class="reports-container">
    <div class="reports-header">
        <h2>Reports</h2>
        <p>View and manage reports related to internship deployment phases.</p>
    </div>

    <div class="horizontal-containers">
        <?php foreach ($sections as $sectionTitle => $docs): ?>
            <div class="report-section">
                <h3><?php echo $sectionTitle; ?></h3>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Interns' Updates</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($docs as $doc_type => $label): ?>
                        <tr>
                            <td><?php echo $label; ?></td>
                            <td><?php echo renderUploadUI($doc_type, $student_reports); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function handleFileUpload(input, docType) {
    const file = input.files[0];
    if (!file) return;

    if (file.type !== "application/pdf") {
        alert("Only PDF files allowed.");
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        alert("Max file size is 10MB.");
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('doc_type', docType);
    formData.append('pdf', file);

    fetch('../phpbackend/upload_report.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || "Upload failed.");
        }
    });
}

function handleDelete(docType) {
    if (!confirm("Delete this file?")) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('doc_type', docType);

    fetch('../phpbackend/upload_report.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || "Delete failed.");
        }
    });
}
</script>

</body>
</html>