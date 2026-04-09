<?php
/**
 * Student Dashboard - Rejection Feature Documentation
 * 
 * This document outlines how the rejection feature works in the student dashboard
 */

echo "<h1>Student Dashboard - Rejection Feature</h1>";

echo "<h2>1. Admin Rejects an Application</h2>";
echo "<ol>";
echo "<li>Admin goes to admin/internslots.php</li>";
echo "<li>Admin clicks 'Reject' button on a pending application</li>";
echo "<li>Modal appears asking for rejection reason (center-aligned, compact)</li>";
echo "<li>Admin types reason and clicks 'Reject'</li>";
echo "<li>SweetAlert shows loading state while processing</li>";
echo "<li>Success message appears: 'Application rejected successfully! Student has been notified.'</li>";
echo "<li>Page reloads and student is removed from pending list</li>";
echo "</ol>";

echo "<h2>2. Student Sees Rejection in Dashboard</h2>";
echo "<ol>";
echo "<li>Student logs into student/dashboard.php</li>";
echo "<li>Application Status card shows:</li>";
echo "<ul>";
echo "<li><strong>Badge:</strong> ❌ Rejected</li>";
echo "<li><strong>Message:</strong> 'Your application has been rejected. You can apply for another agency below.'</li>";
echo "<li><strong>Rejection Reason:</strong> Displayed in a red-bordered box with the admin's reason</li>";
echo "</ul>";
echo "</ol>";

echo "<h2>3. Student Can Reapply</h2>";
echo "<ol>";
echo "<li>In 'Available Agency Slots' section, rejected students can see all slots</li>";
echo "<li>Notification shows: '🔄 Rejected previously - you can apply to other agencies'</li>";
echo "<li>Student can click on any agency slot to apply</li>";
echo "<li>When reapplying:</li>";
echo "<ul>";
echo "<li>Previous rejection_reason is CLEARED from database</li>";
echo "<li>New application status becomes 'pending'</li>";
echo "<li>Admin can review and approve/reject again</li>";
echo "</ul>";
echo "</ol>";

echo "<h2>Database Changes</h2>";
echo "<ul>";
echo "<li><strong>Column:</strong> rejection_reason (VARCHAR(500)) in student_users table</li>";
echo "<li><strong>Auto-creation:</strong> Automatically created if missing</li>";
echo "<li><strong>When set:</strong> When admin rejects application with reason</li>";
echo "<li><strong>When cleared:</strong> When student reapplies to another agency</li>";
echo "</ul>";

echo "<h2>Files Modified</h2>";
echo "<ul>";
echo "<li><strong>admin/internslots.php</strong> - Rejection modal and logic</li>";
echo "<li><strong>phpbackend/manage-applications.php</strong> - Backend rejection processing</li>";
echo "<li><strong>phpbackend/apply-agency.php</strong> - Clears rejection_reason on reapplication</li>";
echo "<li><strong>student/dashboard.php</strong> - Shows rejection and allows reapplication</li>";
echo "</ul>";

echo "<h2>Key Features</h2>";
echo "<ul>";
echo "<li>✅ Rejection reason is required (admin can't reject without reason)</li>";
echo "<li>✅ Rejection reason is displayed to student with styling</li>";
echo "<li>✅ Rejected students can immediately apply to other agencies</li>";
echo "<li>✅ Previous rejection reason is cleared when reapplying</li>";
echo "<li>✅ Notifications clearly indicate if student can reapply</li>";
echo "<li>✅ Database column auto-creates if missing</li>";
echo "<li>✅ Full error logging for debugging</li>";
echo "</ul>";

echo "<h2>Testing Checklist</h2>";
echo "<ol>";
echo "<li>Go to admin/internslots.php and reject a pending application with a reason</li>";
echo "<li>Check console (F12) for any errors - should see successful response</li>";
echo "<li>Open student dashboard - should see Rejected badge with reason</li>";
echo "<li>Try clicking on available agency slots - should be clickable</li>";
echo "<li>Apply to another agency - application should be pending</li>";
echo "<li>Check that rejection_reason was cleared in database</li>";
echo "</ol>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h1 { color: #0A1D56; border-bottom: 3px solid #FFA500; padding-bottom: 10px; }
h2 { color: #0A1D56; margin-top: 20px; }
ol, ul { line-height: 1.8; }
li { margin: 8px 0; }
code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
</style>
