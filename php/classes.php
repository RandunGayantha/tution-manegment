<?php
require_once 'db.php';
$db = getDB();
$msg = '';

# Calling SQL Server stored procedure for class creation
if(isset($_POST['add_class'])) {
    $name    = trim($_POST['class_name']);
    $subject = trim($_POST['subject']);
    $tid     = (int)$_POST['teacher_id'];
    $sched   = trim($_POST['schedule']);
    $max     = (int)$_POST['max_students'];
    $fee     = (float)$_POST['fee'];

    // Handle No Teacher case
    $tid_param = ($tid == 0) ? null : $tid;

    // ✅ Validation BEFORE try
    if(!$name){
        $msg = ['type'=>'error','text'=>'Class name is required'];
    } else {

        try {
            $stmt = $db->prepare("EXEC AddClass ?, ?, ?, ?, ?, ?");
            $stmt->execute([$name, $subject, $tid_param, $sched, $max, $fee]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $proc_msg = $result['message'] ?? 'No response';

            if(str_starts_with($proc_msg, 'SUCCESS')) {
                $msg = ['type'=>'success','text'=>"Class '$name' created successfully!"];
            } else {
                $msg = ['type'=>'error','text'=> $proc_msg];
            }

        } catch (PDOException $e) {
            $msg = ['type'=>'error','text'=>$e->getMessage()];
        }

    }
}

if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("UPDATE classes SET status='inactive' WHERE class_id=?");
        if($stmt->execute([$id])) {
            $msg = ['type'=>'success','text'=>"Class ID $id deactivated successfully"];
        }
    } catch (PDOException $e) {
        $msg = ['type'=>'error','text'=>$e->getMessage()];
    }
}

$teachersList = $db->query("SELECT * FROM teachers WHERE status='active' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$classesList = $db->query("
    SELECT * FROM vw_ClassDetails
    ORDER BY class_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="main">
    <div class="topbar"><h1>📖 Classes</h1></div>

    <?php if($msg): ?>
    <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">

        <div class="card">
            <div class="card-header"><span class="card-title">➕ Add Class</span></div>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Class Name *</label>
                        <input type="text" name="class_name" required placeholder="e.g. Maths Grade 10">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject">
                            <option>Mathematics</option><option>Science</option>
                            <option>English</option><option>ICT</option>
                            <option>Physics</option><option>Chemistry</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Teacher</label>
                        <select name="teacher_id">
                            <option value="0">-- No Teacher --</option>
                            <?php foreach($teachersList as $t): ?>
                            <option value="<?= $t['teacher_id'] ?>"><?= htmlspecialchars($t['full_name']) ?> (<?= $t['subject'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-full">
                        <label>Schedule</label>
                        <input type="text" name="schedule" placeholder="e.g. Mon/Wed 4pm-6pm">
                    </div>
                    <div class="form-group">
                        <label>Max Students</label>
                        <input type="number" name="max_students" value="30" min="1">
                    </div>
                    <div class="form-group">
                        <label>Monthly Fee (Rs.)</label>
                        <input type="number" name="fee" placeholder="2500" step="0.01">
                    </div>
                </div>
                <br>
               <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>

                <small style="color:#888;display:block;margin-top:10px;">
                    💡 Stored Procedure Used:
                    <code>EXEC AddClass @p_class_name, @p_subject, @p_teacher_id, @p_schedule, @p_max, @p_fee</code>
                </small>
            </form>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">All Classes</span></div>
            <table>
                <thead>
                    <tr><th>#</th><th>Class</th><th>Teacher</th><th>Schedule</th><th>Fee</th><th>Enrolled/Max</th><th>Full?</th><th>Status</th><th>Act</th></tr>
                </thead>
                <tbody>
                <?php foreach($classesList as $cl): ?>
                <tr>
                    <td><?= $cl['class_id'] ?></td>
                    <td><strong><?= htmlspecialchars($cl['class_name']) ?></strong><br>
                        <small style="color:#888;"><?= $cl['subject'] ?></small></td>
                    <td><?= htmlspecialchars($cl['teacher_name'] ?? '-') ?></td>
                    <td style="font-size:12px;"><?= $cl['schedule'] ?></td>
                    <td>Rs. <?= number_format($cl['fee'],2) ?></td>
                    <td><?= $cl['enrolled'] ?> / <?= $cl['max_students'] ?></td>
                    <td>
                        <?php if($cl['is_full']=='YES'): ?>
                            <span class="badge badge-overdue">FULL</span>
                        <?php else: ?>
                            <span class="badge badge-paid">OPEN</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-<?= $cl['status'] ?>"><?= strtoupper($cl['status']) ?></span></td>
                    <td>
                        <a href="?delete=<?= $cl['class_id'] ?>"
                           onclick="return confirm('Deactivate this class?')"
                           class="btn btn-danger btn-sm">Del</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <small style="color:#888;margin-top:10px;display:block;">
                💡 <strong>IsClassFull()</strong> and <strong>GetClassStudentCount()</strong> SQL functions used here
            </small>
        </div>
    </div>
</div>
</body></html>
