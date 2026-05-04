<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/helpers.php';
renderHead('Payments');
$sid  = (int)$_SESSION['student_id'];
$conn = getDB();
$payments = dbQuery($conn,'SELECT * FROM payment_status_view WHERE student_id=?',[$sid]);
$conn->close();
$totalPaid = array_sum(array_map(fn($p)=>$p['status']==='Paid'?$p['amount']:0,$payments));
$pending   = array_sum(array_map(fn($p)=>$p['status']==='Pending'?$p['amount']:0,$payments));
renderSidebar('payments');
?>
<div class="main-content">
  <div class="page-header"><h1>Payment History</h1><p>Fee payment records and receipts</p></div>
  <div class="stats-grid-3">
    <div class="stat-card"><div class="stat-value green">Rs.<?= number_format($totalPaid) ?></div><div class="stat-label">Total Paid</div></div>
    <div class="stat-card"><div class="stat-value amber">Rs.<?= number_format($pending) ?></div><div class="stat-label">Pending</div></div>
    <div class="stat-card"><div class="stat-value"><?= count($payments) ?></div><div class="stat-label">Transactions</div></div>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table class="etable">
        <thead><tr><th>Receipt No</th><th>Class</th><th>Amount</th><th>Date</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach($payments as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['receipt_no']) ?></td>
            <td><?= htmlspecialchars($p['class_name']) ?></td>
            <td>Rs. <?= number_format($p['amount']) ?></td>
            <td><?= fmtDate($p['payment_date']) ?></td>
            <td><?= htmlspecialchars($p['payment_method']) ?></td>
            <td><?= payBadge($p['status']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></div></body></html>