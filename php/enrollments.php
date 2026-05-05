<?php
require_once 'db.php';
$db = getPDO();
$msg = '';

// ENROLL STUDENT using Stored Procedure
if(isset($_POST['enroll'])) {
    $sid = (int)$_POST['student_id'];
    $cid = (int)$_POST['class_id'];

    // Call stored procedure EnrollStudent for SQL Server
    # Using SELECT-based return instead of OUTPUT due to PDO limitations
    try {
    $stmt = $db->prepare("EXEC EnrollStudent ?, ?");
    $stmt->execute([$sid, $cid]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $proc_msg = $result['message'] ?? 'No response from procedure';

    if(str_starts_with($proc_msg, 'SUCCESS')) {
        $msg = ['type'=>'success','text'=> $proc_msg];
    } else {
        $msg = ['type'=>'error','text'=> $proc_msg];
    }

} catch (PDOException $e) {
    $msg = ['type'=>'error','text'=> 'Error: ' . $e->getMessage()];
}
}

// DELETE ENROLLMENT
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM enrollments WHERE enroll_id=?");
        if($stmt->execute([$id])) {
            $msg = ['type'=>'success','text'=>'Enrollment removed.'];
        }
    } catch (PDOException $e) {
        $msg = ['type'=>'error','text'=>$e->getMessage()];
    }
}

$studentsList = $db->query("SELECT * FROM students WHERE status='active' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$classesList  = $db->query("SELECT c.*, dbo.IsClassFull(c.class_id) AS is_full FROM classes c WHERE c.status='active' ORDER BY class_name")->fetchAll(PDO::FETCH_ASSOC);

// Filter by student
$filter_student = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

$sql = "
    SELECT e.*, s.full_name AS student_name, c.class_name, c.fee, c.subject
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    JOIN classes  c ON e.class_id   = c.class_id
";
if ($filter_student) {
    $sql .= " WHERE e.student_id = ?";
}
$sql .= " ORDER BY e.enroll_id DESC";

$stmt = $db->prepare($sql);
if ($filter_student) {
    $stmt->execute([$filter_student]);
} else {
    $stmt->execute();
}
$enrollmentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="main">
    <div class="topbar">
        <h1>📋 Enrollments</h1>
        <span class="topbar-date">Total: <?= count($enrollmentsList) ?></span>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">

        <!-- Enroll Form using Stored Procedure -->
        <div class="card">
            <div class="card-header"><span class="card-title">➕ Enroll Student</span></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Select Student *</label>
                        <select name="student_id" required>
                            <option value="">-- Choose Student --</option>
                            <?php foreach($studentsList as $s): ?>
                            <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['full_name']) ?> (<?= $s['grade'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-full">
                        <label>Select Class *</label>
                        <select name="class_id" required>
                            <option value="">-- Choose Class --</option>
                            <?php
                            foreach($classesList as $c):
                                $full_label = $c['is_full']=='YES' ? ' [FULL]' : '';
                            ?>
                            <option value="<?= $c['class_id'] ?>" <?= $c['is_full']=='YES'?'style="color:red;"':'' ?>>
                                <?= htmlspecialchars($c['class_name']) ?> - Rs.<?= $c['fee'] ?><?= $full_label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <br>
                <button type="submit" name="enroll" class="btn btn-success">Enroll Now</button>
            </form>

            <div style="margin-top:16px;padding:12px;background:#e8eaf6;border-radius:6px;font-size:12px;">
                <strong>⚡ Stored Procedure Used:</strong><br>
                <code>EXEC EnrollStudent @p_student_id, @p_class_id</code><br><br>
                The procedure:<br>
                ✅ Checks if class is full<br>
                ✅ Checks duplicate enrollment<br>
                ✅ Returns success/error message
            </div>
        </div>

        <!-- Enrollments List -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">All Enrollments</span>
                <form method="GET" style="display:flex;gap:8px;">
                    <select name="student_id" style="padding:5px 8px;border:1px solid #ddd;border-radius:5px;font-size:13px;">
                        <option value="">All Students</option>
                        <?php
                        foreach($studentsList as $s):
                        ?>
                        <option value="<?= $s['student_id'] ?>" <?= $filter_student==$s['student_id']?'selected':'' ?>>
                            <?= htmlspecialchars($s['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <?php if($filter_student): ?><a href="enrollments.php" class="btn btn-sm" style="background:#eee;">Clear</a><?php endif; ?>
                </form>
            </div>

            <div style="overflow-y:auto;max-height:420px;">
            <table>
                <thead>
                    <tr><th>#</th><th>Student</th><th>Class</th><th>Enrolled On</th><th>Fee</th><th>Payment</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php
                foreach($enrollmentsList as $e): ?>
                <tr>
                    <td><?= $e['enroll_id'] ?></td>
                    <td><strong><?= htmlspecialchars($e['student_name']) ?></strong></td>
                    <td><?= htmlspecialchars($e['class_name']) ?><br>
                        <small style="color:#888;"><?= $e['subject'] ?></small></td>
                    <td><?= $e['enroll_date'] ?></td>
                    <td>Rs. <?= number_format($e['fee'],2) ?></td>
                    <td><span class="badge badge-<?= $e['payment_status'] ?>"><?= strtoupper($e['payment_status']) ?></span></td>
                    <td>
                        <a href="payments.php?enroll_id=<?= $e['enroll_id'] ?>" class="btn btn-success btn-sm">Pay</a>
                        <a href="?delete=<?= $e['enroll_id'] ?>"
                           onclick="return confirm('Remove this enrollment?')"
                           class="btn btn-danger btn-sm">Del</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</body></html>
