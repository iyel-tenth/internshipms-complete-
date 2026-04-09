<?php
session_start();
include '../db_connect.php';

// Admin access protection (reuse from dashboard)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
	header("Location: ../login.php");
	exit();
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
			   align-items: flex-start;
		   }
			   .report-table {
				   width: 100%;
				   border-collapse: collapse;
				   margin-top: 10px;
			   }
			   .report-table th, .report-table td {
				   border-bottom: 1px solid #e0e0e0;
				   padding: 10px 8px;
				   text-align: left;
			   }
			   .report-table th {
				   background: #f7f7f7;
				   color: #0A1D56;
				   font-size: 1rem;
			   }
			   .report-table tr:last-child td {
				   border-bottom: none;
			   }
			   .report-btn {
				   background: #ffd700;
				   color: #0A1D56;
				   border: none;
				   border-radius: 6px;
				   padding: 14px 0;
				   width: 100%;
				   font-size: 1.08rem;
				   font-weight: 600;
				   cursor: pointer;
				   transition: background 0.2s;
				   margin: 0;
				   box-shadow: 0 1px 4px rgba(0,0,0,0.04);
				   display: block;
			   }
			   .report-btn:hover {
				   background: #ffe066;
			   }
		.report-section h3 {
			margin-top: 0;
			margin-bottom: 12px;
			color: #0A1D56;
			font-size: 1.2rem;
			font-weight: 600;
		}
	</style>
</head>
<body>

<?php include ('../includes/sidebar_admin.php'); ?>

	<div class="reports-container">
		<div class="reports-header">
			<h2>Reports</h2>
			<p>View and manage reports related to internship deployment phases. Use the sections below to access information before, during, and after deployment.</p>
		</div>
		<!-- REPORTS MODAL -->
		<div id="reportsModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; overflow:auto; background:rgba(0,0,0,0.3);">
			<div class="modal-content" style="background:#fff; margin:5% auto; padding:20px; border-radius:10px; max-width:500px; position:relative;">
				<span class="modal-close" onclick="closeReportsModal()" style="position:absolute; top:10px; right:18px; font-size:2rem; cursor:pointer;">&times;</span>
				<div class="modal-header" id="reportsModalTitle" style="font-size:1.3rem; font-weight:600; margin-bottom:10px;">Report Submissions</div>
				<div class="modal-body" id="reportsContainer">
					<!-- Fetched content here -->
				</div>
				<button class="close-btn" onclick="closeReportsModal()" style="margin-top:18px; background:#ffd700; color:#0A1D56; border:none; border-radius:6px; padding:10px 24px; font-size:1rem; font-weight:600; cursor:pointer;">Close</button>
			</div>
		</div>

		<script>
		// Attach event listeners to all Inspect buttons
		document.querySelectorAll('.report-btn').forEach(function(btn) {
			btn.addEventListener('click', function(e) {
				// Find the document type from the same row
				const docType = btn.closest('tr').querySelector('td').textContent.trim();
				openReportsModal(docType);
			});
		});

		function openReportsModal(docType) {
			const modal = document.getElementById('reportsModal');
			const modalTitle = document.getElementById('reportsModalTitle');
			const container = document.getElementById('reportsContainer');
			modalTitle.textContent = docType + ' Submissions';
			container.innerHTML = '<div style="text-align:center;">Loading...</div>';
			modal.style.display = 'block';

			// Fetch data from backend
			fetch('../phpbackend/fetch-student-reports.php?doc_type=' + encodeURIComponent(docType))
				.then(res => res.json())
				.then(data => {
					if (data.error) {
						container.innerHTML = '<div style="color:red;">' + data.error + '</div>';
						return;
					}
					if (!data.reports || data.reports.length === 0) {
						container.innerHTML = '<div style="color:#666;">No submissions found.</div>';
						return;
					}
					let html = '<table style="width:100%; border-collapse:collapse;">';
					html += '<tr><th style="text-align:left; padding:6px 4px; border-bottom:1px solid #eee;">Name</th><th style="text-align:left; padding:6px 4px; border-bottom:1px solid #eee;">File Name</th></tr>';
					data.reports.forEach(function(rep) {
						html += `<tr><td style=\"padding:6px 4px; border-bottom:1px solid #f3f3f3;\">${rep.name}</td><td style=\"padding:6px 4px; border-bottom:1px solid #f3f3f3;\">${rep.file_name}</td></tr>`;
					});
					html += '</table>';
					container.innerHTML = html;
				})
				.catch(() => {
					container.innerHTML = '<div style="color:red;">Failed to fetch data.</div>';
				});
		}

		function closeReportsModal() {
			document.getElementById('reportsModal').style.display = 'none';
		}

		// Close modal when clicking outside
		window.addEventListener('click', function(event) {
			const modal = document.getElementById('reportsModal');
			if (event.target === modal) {
				modal.style.display = 'none';
			}
		});
		</script>
		<div class="horizontal-containers">
			<div class="report-section">
				<h3>Before Deployment</h3>
				   <table class="report-table">
					   <thead>
						   <tr>
							   <th>Document</th>
							   <th>Interns' Updates</th>
						   </tr>
					   </thead>
					   <tbody>
						   <tr>
							   <td>In-Campus OJT Certificate</td>
							   <td>
								   <button class="report-btn" data-doc-type="in_campus_ojt">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Pre-OJT Seminar Certificate</td>
							   <td>
								   <button class="report-btn" data-doc-type="pre_ojt_seminar">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Medical Certificate</td>
							   <td>
								   <button class="report-btn" data-doc-type="medical_cert">Inspect</button>
							   </td>
						   </tr>
						   
					   </tbody>
				   </table>
			</div>
			<div class="report-section">
				<h3>During Deployment</h3>
				   <table class="report-table">
					   <thead>
						   <tr>
							   <th>Document</th>
							   <th>Interns' Updates</th>
						   </tr>
					   </thead>
					   <tbody>
						   <tr>
							   <td>Week 1 Narrative Report</td>
							   <td>
								   <button class="report-btn" data-doc-type="week1">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Week 2 Narrative Report</td>
							   <td>
								   <button class="report-btn" data-doc-type="week2">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Week 3 Narrative Report</td>
							   <td>
								   <button class="report-btn" data-doc-type="week3">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Week 4 Narrative Report</td>
							   <td>
								   <button class="report-btn" data-doc-type="week4">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Week 5 Narrative Report</td>
							   <td>
								   <button class="report-btn" data-doc-type="week5">Inspect</button>
							   </td>
						   </tr>
					   </tbody>
				   </table>
			</div>
			<div class="report-section">
				<h3>After Deployment</h3>
				   <table class="report-table">
					   <thead>
						   <tr>
							   <th>Document</th>
							   <th>Interns' Updates</th>
						   </tr>
					   </thead>
					   <tbody>
						   <tr>
							   <td>Signed DTR</td>
							   <td>
								   <button class="report-btn" data-doc-type="signed_dtr">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Signed Daily Time Frame</td>
							   <td>
								   <button class="report-btn" data-doc-type="signed_timeframe">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>Student Evaluation Form</td>
							   <td>
								   <button class="report-btn" data-doc-type="student_eval">Inspect</button>
							   </td>
						   </tr>
						   <tr>
							   <td>HTE Evaluation Form</td>
							   <td>
								   <button class="report-btn" data-doc-type="hte_eval">Inspect</button>
							   </td>
						   </tr>
					   </tbody>
				   </table>
			</div>
		</div>
	</div>

	<!-- REPORTS MODAL -->
	<div id="reportsModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; overflow:auto; background:rgba(0,0,0,0.3);">
	  <div class="modal-content" style="background:#fff; margin:3% auto; padding:20px; border-radius:10px; max-width:800px; max-height:80vh; overflow-y:auto; position:relative;">
	    <span class="modal-close" onclick="closeReportsModal()" style="position:absolute; top:10px; right:18px; font-size:2rem; cursor:pointer;">&times;</span>
	    <div class="modal-header" id="reportsModalTitle" style="font-size:1.1rem; font-weight:600; margin-bottom:10px;">Report Submissions</div>
	    <div class="modal-body" id="reportsContainer">
	      <!-- Fetched content here -->
	    </div>
	    <button class="close-btn" onclick="closeReportsModal()" style="margin-top:18px; background:#ffd700; color:#0A1D56; border:none; border-radius:6px; padding:10px 24px; font-size:0.9rem; font-weight:600; cursor:pointer;">Close</button>
	  </div>
	</div>

	<script>
	// Attach event listeners to all Inspect buttons
	document.querySelectorAll('.report-btn').forEach(function(btn) {
	  btn.addEventListener('click', function(e) {
	    // Get the document type from data attribute
	    const docType = btn.getAttribute('data-doc-type');
	    const displayName = btn.closest('tr').querySelector('td').textContent.trim();
	    openReportsModal(docType, displayName);
	  });
	});

	function openReportsModal(docType, displayName) {
	  const modal = document.getElementById('reportsModal');
	  const modalTitle = document.getElementById('reportsModalTitle');
	  const container = document.getElementById('reportsContainer');
	  modalTitle.textContent = displayName + ' Submissions';
	  container.innerHTML = '<div style="text-align:center;">Loading...</div>';
	  modal.style.display = 'block';

	  // Fetch data from backend
	  fetch('../phpbackend/fetch-student-reports.php?doc_type=' + encodeURIComponent(docType))
	    .then(res => res.json())
	    .then(data => {
	      if (data.error) {
	        container.innerHTML = '<div style="color:red;">' + data.error + '</div>';
	        return;
	      }
	      if (!data.reports || data.reports.length === 0) {
	        container.innerHTML = '<div style="color:#666;">No submissions found.</div>';
	        return;
	      }
	      let html = '<table style="width:100%; border-collapse:collapse; font-size:0.9rem;">';
	      html += '<tr style="background:#f9f9f9;"><th style="text-align:left; padding:8px 6px; border-bottom:1px solid #ddd; font-size:0.85rem;">Name</th><th style="text-align:left; padding:8px 6px; border-bottom:1px solid #ddd; font-size:0.85rem;">Major</th><th style="text-align:left; padding:8px 6px; border-bottom:1px solid #ddd; font-size:0.85rem;">File Name</th></tr>';
	      data.reports.forEach(function(rep) {
	        html += `<tr><td style="padding:8px 6px; border-bottom:1px solid #f3f3f3; word-break:break-word;">${rep.name}</td><td style="padding:8px 6px; border-bottom:1px solid #f3f3f3; word-break:break-word;">${rep.major}</td><td style="padding:8px 6px; border-bottom:1px solid #f3f3f3; word-break:break-word;">${rep.file_name}</td></tr>`;
	      });
	      html += '</table>';
	      container.innerHTML = html;
	    })
	    .catch(() => {
	      container.innerHTML = '<div style="color:red;">Failed to fetch data.</div>';
	    });
	}

	function closeReportsModal() {
	  document.getElementById('reportsModal').style.display = 'none';
	}

	// Close modal when clicking outside
	window.addEventListener('click', function(event) {
	  const modal = document.getElementById('reportsModal');
	  if (event.target === modal) {
	    modal.style.display = 'none';
	  }
	});
	</script>
</body>
</html>
