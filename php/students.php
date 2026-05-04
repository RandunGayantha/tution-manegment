<?php
require_once 'db.php';
$db = getDB();
$msg = '';

// Student Count Get By Grade to Chart 1
//Call View vw_StudentFinancialSummary 
$gradeSql = "SELECT grade, COUNT(*) as count FROM vw_StudentFinancialSummary GROUP BY grade";
$gradeRes = sqlsrv_query($db, $gradeSql);

$gradeLabels = [];
$gradeData = [];

while ($row = sqlsrv_fetch_array($gradeRes, SQLSRV_FETCH_ASSOC)) {
    $gradeLabels[] = "Grade " . $row['grade'];
    $gradeData[] = $row['count'];
}

// Active Inactive Students Count to Chart 3
$statusSql = "SELECT status, COUNT(*) as count FROM students GROUP BY status";
$statusRes = sqlsrv_query($db, $statusSql);

$statusLabels = [];
$statusData = [];

while ($sRow = sqlsrv_fetch_array($statusRes, SQLSRV_FETCH_ASSOC)) {
    $statusLabels[] = ucfirst($sRow['status']); // 'active' -> 'Active'
    $statusData[] = $sRow['count'];
}

// Payment Method Count to Chart 2
$methodSql = "SELECT method, COUNT(DISTINCT enroll_id) as count FROM payments GROUP BY method";
$methodRes = sqlsrv_query($db, $methodSql);

$methodLabels = [];
$methodData = [];

while ($mRow = sqlsrv_fetch_array($methodRes, SQLSRV_FETCH_ASSOC)) {
   
    $methodLabels[] = ucfirst($mRow['method']); 
    $methodData[] = $mRow['count'];
}
// ================= ADD STUDENT =================
if(isset($_POST['add_student'])) {
    $name    = trim($_POST['full_name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $grade   = trim($_POST['grade']);
    $address = trim($_POST['address']);
    $dob     = $_POST['dob'] ?? null;
    $gname   = $_POST['guardian_name'] ?? '';
    $gphone  = $_POST['guardian_phone'] ?? '';

    // Auto-generate Username 
    $username = strtolower(explode(' ', $name)[0]) . rand(100, 999);
    
    // Auto-generate Password 
    $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);

    if(empty($name)) {
        $msg = ['type'=>'error','text'=>'Name is required'];
   } else {
        // Call Procedure 1 
        $sql = "{call sp_AddStudent(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
        
        $params = array(
            $name, $email, $phone, $grade, $address, 
            $dob, $gname, $gphone, $username, $password
        );

       
        $stmt = sqlsrv_query($db, $sql, $params);

        if($stmt) {
            header("Location: students.php?success=1&u=$username&p=$password");
            exit();
        } else {
            
            $msg = ['type'=>'error', 'text'=> 'Database Error: ' . print_r(sqlsrv_errors(), true)];
        }
    }
}

// ================= DELETE =================
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Call Triger 1 trg_DeleteStudentCascade
    $sql = "DELETE FROM students WHERE student_id = ?";
    sqlsrv_query($db, $sql, array($id));

    header("Location: students.php");
    exit();
}


// ================= TOGGLE STATUS =================
if(isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    sqlsrv_query($db, "UPDATE students 
        SET status = CASE WHEN status='active' THEN 'inactive' ELSE 'active' END 
        WHERE student_id = ?", array($id));
    header("Location: students.php");
    exit();
}


// ================= FILTERS & MAIN QUERY =================
$where = [];
$queryParams = [];

if(!empty($_GET['search'])) {
    $where[] = "(s.full_name LIKE ? OR s.email LIKE ?)";
    $term = "%".$_GET['search']."%";
    $queryParams[] = $term;
    $queryParams[] = $term;
}

if(!empty($_GET['grade'])) {
    $where[] = "s.grade = ?";
    $queryParams[] = $_GET['grade'];
}

if(!empty($_GET['status'])) {
    $where[] = "s.status = ?";
    $queryParams[] = $_GET['status'];
}

if(!empty($_GET['payment_method'])) {
   
    $where[] = "EXISTS (SELECT 1 FROM payments p2 WHERE p2.enroll_id = e.enroll_id AND p2.method = ?)";
    $queryParams[] = $_GET['payment_method'];
}


// Call Function 1
$sql = "
SELECT 
    s.student_id, 
    s.full_name, 
    s.email, 
    s.phone, 
    s.grade, 
    s.status, 
    s.username,
    s.password,
    dbo.fn_GetStudentBalance(s.student_id) AS calculated_balance,
    ISNULL((SELECT SUM(p.amount) FROM payments p 
            JOIN enrollments e2 ON p.enroll_id = e2.enroll_id 
            WHERE e2.student_id = s.student_id), 0) AS total_paid
FROM students s
LEFT JOIN enrollments e ON s.student_id = e.student_id";


if(!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// 3. GROUP BY and ORDER BY

$sql .= "
GROUP BY 
    s.student_id, s.full_name, s.email, s.phone, s.grade, s.status, s.username, s.password
ORDER BY s.student_id ASC";

$studentsResult = sqlsrv_query($db, $sql, $queryParams);

if ($studentsResult === false) {
    die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
}

//Join Query
$overdueSql = "SELECT COUNT(*) as overdue_total FROM
         (SELECT s.student_id FROM students s 
         JOIN enrollments e ON s.student_id = e.student_id JOIN classes c ON e.class_id = c.class_id 
         LEFT JOIN payments p ON e.enroll_id = p.enroll_id 
         GROUP BY s.student_id, c.fee HAVING (c.fee - ISNULL(SUM(p.amount), 0)) > 0) t";

$overdueRes = sqlsrv_query($db, $overdueSql);
$overdueRow = sqlsrv_fetch_array($overdueRes, SQLSRV_FETCH_ASSOC);
$overdueCount = $overdueRow['overdue_total'] ?? 0;





// ================= DASHBOARD STATS =================
$totalStudents = sqlsrv_fetch_array(sqlsrv_query($db,"SELECT COUNT(*) as t FROM students"),SQLSRV_FETCH_ASSOC)['t'] ?? 0;
$activeStudents = sqlsrv_fetch_array(sqlsrv_query($db,"SELECT COUNT(*) as t FROM students WHERE status='active'"),SQLSRV_FETCH_ASSOC)['t'] ?? 0;
$totalCollection = sqlsrv_fetch_array(sqlsrv_query($db,"SELECT ISNULL(SUM(amount),0) as t FROM payments"),SQLSRV_FETCH_ASSOC)['t'] ?? 0;


echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';
include 'header.php';
?>

<div class="main">
    <div class="topbar">
        <h1>👨‍🎓 Students Management</h1>
    </div>

    <!-- STAT CARDS -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:20px;">
        <div class="card" style="display:flex;align-items:center;gap:15px;padding:20px;">
            <div style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#eef2ff;">
                <i class="fa-solid fa-users" style="color:#4f46e5;font-size:22px;"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;"><?= $totalStudents ?></div>
                <div style="color:#777;font-size:14px;">Total Students</div>
            </div>
        </div>

        <div class="card" style="display:flex;align-items:center;gap:15px;padding:20px;">
            <div style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#e6f7ee;">
                <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:22px;"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;"><?= $activeStudents ?></div>
                <div style="color:#777;font-size:14px;">Active Students</div>
            </div>
        </div>

        <div class="card" style="display:flex;align-items:center;gap:15px;padding:20px;">
            <div style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#fff4e5;">
                <i class="fa-solid fa-clock" style="color:#f59e0b;font-size:22px;"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;"><?= $overdueCount ?></div>
                <div style="color:#777;font-size:14px;">Overdue Payments</div>
            </div>
        </div>

        <div class="card" style="display:flex;align-items:center;gap:15px;padding:20px;">
            <div style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#f3e8ff;">
                <i class="fa-solid fa-wallet" style="color:#7c3aed;font-size:22px;"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;">Rs. <?= number_format($totalCollection,2) ?></div>
                <div style="color:#777;font-size:14px;">Total Collected</div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success" style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px;">
            ✅ Student added successfully! <br>
            <strong>Username:</strong> <?= htmlspecialchars($_GET['u']) ?> | 
            <strong>Password:</strong> <?= htmlspecialchars($_GET['p']) ?>
        </div>
    <?php endif; ?>

    
<div style="display:grid; grid-template-columns: 400px minmax(0, 1fr); gap:20px; width:100%; overflow:hidden;">
        <!-- ADD FORM -->
        <div class="card" style="border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-radius: 12px;">
            <div style="display: flex; align-items: center; padding: 20px; border-bottom: 1px solid #f1f5f9; min-height: 73px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-plus" style="color: #4f46e5; font-size: 18px;"></i>
                    <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Add New Student</h2>
                </div>
            </div>
            <form method="POST" action="students.php">
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:15px; padding:15px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Address</label>
                        <input type="text" name="address" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group"><label>Email</label><input type="email" name="email"  required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                    <div class="form-group">
                        <label>Grade</label>
                        <select name="grade" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                            <?php for($g=6;$g<=13;$g++): ?>
                                <option value="Grade <?= $g ?>">Grade <?= $g ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                    <div class="form-group"><label>Guardian Name</label><input type="text" name="guardian_name" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                    <div class="form-group"><label>Guardian Phone</label><input type="text" name="guardian_phone" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                    
                    <div style="grid-column: span 2; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 8px 12px; margin-top: 5px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-circle-info" style="color: #1d4ed8; font-size: 14px;"></i>
                        <label style="color: #1e40af; font-size: 12px; margin: 0; font-family: sans-serif; font-weight: 500;">
                            Username and password will be generated automatically.
                        </label>
                    </div>
                </div>
                <div style="padding:15px;">
                    <button type="submit" name="add_student" class="btn btn-primary" style="background:#4f46e5; color:#fff; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">
                        <i class="fa-solid fa-user-plus"></i> Add Student
                    </button>
                    <button type="reset" class="btn btn-reset" style="background:#ef4444; color:#fff; border:none; padding:10px 20px; border-radius:5px; margin-left:10px; cursor:pointer;">
                        <i class="fa-solid fa-rotate-right"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- STUDENTS LIST -->
         <div style="display:grid; grid-template-columns: 810px 1fr; gap:20px;">
        <div class="card" style="border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #f1f5f9;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-users-gear" style="color: #4f46e5; font-size: 20px;"></i>
                    <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">All Students</h2>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn" style="background: #fff; border: 1px solid #e2e8f0; padding: 8px 15px; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-filter"></i> Filters
                    </button>
                    <button class="btn" style="background: #fff; border: 1px solid #e2e8f0; padding: 8px 15px; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-download"></i> Export
                    </button>
                </div>
            </div>

            <br>
            <form method="GET" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 15px; padding: 0 20px 20px 20px;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 12px; color: #94a3b8;"></i>
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
                </div>
                <select name="grade" onchange="this.form.submit()" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
                    <option value="">All Grades</option>
                    <?php for($g=6;$g<=13;$g++): ?>
                        <option value="Grade <?= $g ?>" <?= ($_GET['grade'] ?? '') == "Grade $g" ? 'selected' : '' ?>>Grade <?= $g ?></option>
                    <?php endfor; ?>
                </select>
                <select name="status" onchange="this.form.submit()" style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
                    <option value="">All Status</option>
                    <option value="active" <?= ($_GET['status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($_GET['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <select style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;" name="payment_method" onchange="this.form.submit()">
    <option value="">Payment Method</option>
    <option value="Cash" <?= (isset($_GET['payment_method']) && $_GET['payment_method'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
    <option value="bank" <?= (isset($_GET['payment_method']) && $_GET['payment_method'] == 'bank') ? 'selected' : '' ?>>Bank Transfer</option>
    <option value="online" <?= (isset($_GET['payment_method']) && $_GET['payment_method'] == 'online') ? 'selected' : '' ?>>Online Payment</option>
</select>
                </select>
            </form>

            <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            
                    <thead>
                        <tr style="border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; text-align: left;">
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">#</th>
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">Student</th>
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">Grade</th>
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">Phone</th>
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">Total Paid</th>
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">Balance</th>
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">Status</th>
                            <th style="padding: 15px 20px; font-weight: 600; color: #64748b; font-size: 13px;">Actions</th>
                        </tr>
                    </thead>
               <tbody>
<?php

$perPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$start = ($page - 1) * $perPage;

// store all rows first
$students = [];
while($row = sqlsrv_fetch_array($studentsResult, SQLSRV_FETCH_ASSOC)){
    $students[] = $row;
}

// total count
$totalRows = count($students);

// slice only 5
$students = array_slice($students, $start, $perPage);

// fix numbering
$count = $start + 1;

// LOOP
foreach($students as $s):
$balance = $s['calculated_balance'];
?>
    <tr style="border-bottom: 1px solid #f8fafc;">
        <td style="padding: 15px 20px; color: #64748b; font-size: 14px;"><?= $count++ ?></td>

        <td style="padding: 15px 20px;">
            <div style="font-weight: 600; color: #1e293b; font-size: 14px;">
                <?= htmlspecialchars($s['full_name']) ?>
            </div>
            <div style="font-size: 12px; color: #94a3b8;">
                <?= htmlspecialchars($s['email'] ?? '') ?>
            </div>
        </td>

        <td style="padding: 15px 20px; font-size: 14px;">
            <?= str_replace('Grade ', '', $s['grade']) ?>
        </td>

        <td style="padding: 15px 20px; font-size: 14px;">
            <?= htmlspecialchars($s['phone']) ?>
        </td>

        <td style="padding: 15px 20px; color: #16a34a; font-weight: 500; font-size: 14px;">
            Rs. <?= number_format($s['total_paid'], 2) ?>
        </td>

        <td style="padding: 15px 20px; color: <?= $balance > 0 ? '#ef4444' : '#16a34a' ?>; font-weight: 500; font-size: 14px;">
            Rs. <?= number_format($balance, 2) ?>
        </td>

        <td style="padding: 15px 20px;">
            <span style="padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; background: <?= $s['status'] == 'active' ? '#dcfce7' : '#f1f5f9' ?>; color: <?= $s['status'] == 'active' ? '#16a34a' : '#64748b' ?>;">
                <?= strtoupper($s['status']) ?>
            </span>
        </td>

        <td style="padding: 15px 20px;">
            <div style="display: flex; gap: 8px;">
                <a href="?toggle=<?= $s['student_id'] ?>" style="color: #4f46e5; border: 1px solid #e2e8f0; padding: 6px; border-radius: 6px;">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
                <a href="?delete=<?= $s['student_id'] ?>" onclick="return confirm('Are you sure?')" style="color: #ef4444; border: 1px solid #fee2e2; padding: 6px; border-radius: 6px;">
                    <i class="fa-regular fa-trash-can"></i>
                </a>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
                </table>
            </div>
            
            <!-- Pagination Section -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-top: 1px solid #f1f5f9;">
                <div style="font-size: 13px; color: #64748b;">Showing <?= $count-1 ?> of <?= $totalStudents ?> students</div>
                <div style="display: flex; gap: 5px;">
                   <div style="display: flex; gap: 5px;">
    
</div><div style="display: flex; gap: 5px;">
<?php
$totalPages = ceil($totalRows / $perPage);
?>

<?php if($page > 1): ?>
    <a href="?page=<?= $page-1 ?>" style="padding:5px 10px; background:#e5e7eb; border-radius:6px; text-decoration:none;">Prev</a>
<?php endif; ?>

<?php for($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>" 
       style="padding:5px 12px;
              background:<?= $i == $page ? '#4f46e5' : '#e5e7eb' ?>;
              color:<?= $i == $page ? '#fff' : '#000' ?>;
              border-radius:6px;
              text-decoration:none;">
        <?= $i ?>
    </a>
<?php endfor; ?>

<?php if($page < $totalPages): ?>
    <a href="?page=<?= $page+1 ?>" style="padding:5px 10px; background:#e5e7eb; border-radius:6px; text-decoration:none;">Next</a>
<?php endif; ?>
</div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<div style="display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap;">


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="card p-4 shadow-sm" style="max-width: 450px; width: 100%;">
    <h5 class="mb-4">Students by Grade</h5>
    <br>

    <div style="display:flex; align-items:center; gap:30px;">
        
        <!-- Chart -->
        <div style="width: 160px;">
            <canvas id="gradeChart"></canvas>
        </div>

        <!-- Legend (RIGHT SIDE, 2 columns) -->
        <div id="customLegend" style="
            display: grid;
            
            gap: 12px 20px;
            font-size: 14px;
        "></div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('gradeChart').getContext('2d');

// CREATE CHART
const gradeChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($gradeLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($gradeData); ?>,
            backgroundColor: [
                '#4285F4',
                '#34A853',
                '#FBBC05',
                '#A767EE',
                '#EA4335'
            ],
            borderWidth: 0
        }]
    },
    options: {
        cutout: '60%',
        plugins: {
            legend: {
                display: false 
            }
        }
    }
});


const legendContainer = document.getElementById('customLegend');
legendContainer.innerHTML = '';

const dataValues = gradeChart.data.datasets[0].data;
const total = dataValues.reduce((a, b) => a + b, 0);

gradeChart.data.labels.forEach((label, i) => {
    const color = gradeChart.data.datasets[0].backgroundColor[i];
    const value = dataValues[i];
    const percent = total > 0 ? Math.round((value / total) * 100) : 0;

    const item = document.createElement('div');

    item.style.display = 'flex';
    item.style.alignItems = 'center';
    item.style.justifyContent = 'space-between';
    item.style.gap = '10px';
    item.style.cursor = 'pointer';

    item.innerHTML = `
        <div style="display:flex; align-items:center; gap:8px; min-width:110px;">
            <span style="
                width:12px;
                height:12px;
                background:${color};
                border-radius:50%;
                display:inline-block;
            "></span>

            <span style="white-space:nowrap;">
                ${label} <!-- ✅ KEEP FULL LABEL -->
            </span>
        </div>

        <span style="color:#6b7280; font-size:13px; white-space:nowrap;">
            ${value} (${percent}%)
        </span>
    `;

    item.onclick = () => {
        gradeChart.toggleDataVisibility(i);
        gradeChart.update();
    };

    legendContainer.appendChild(item);
});

</script>
<div class="card p-4 shadow-sm" style="max-width: 350px; width: 80%;">
    <h5 class="fw-bold mb-4">Payment Method Distribution</h5>
<br>
    <div style="display:flex; align-items:center; gap:30px;">

        <!-- Chart -->
        <div style="width: 160px; height:160px;">
            <canvas id="methodChart"></canvas>
        </div>

        <!-- Legend (RIGHT SIDE) -->
        <div style="flex:1;">
            <ul style="list-style:none; padding:0; margin:0;">
                <?php 
                $mColors = ['#4e73df', '#1cc88a', '#f6c23e', '#36b9cc'];
                $totalM = array_sum($methodData);

                foreach($methodLabels as $index => $label): 
                    $val = $methodData[$index];
                    $per = ($totalM > 0) ? round(($val / $totalM) * 100) : 0;
                ?>
                <li style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    
                    <!-- Left -->
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="
                            width:10px;
                            height:10px;
                            border-radius:50%;
                            background: <?= $mColors[$index] ?>;
                            display:inline-block;
                        "></span>
                        <span style="font-size:14px;"><?= $label ?></span>
                        <!-- Right -->
                    <span style="font-size:13px; color:#6b7280;">
                        <?= $val ?> (<?= $per ?>%)
                    </span>
                    </div>

                    

                </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const mCtx = document.getElementById('methodChart').getContext('2d');

    new Chart(mCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($methodLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($methodData); ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#36b9cc'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false 
                }
            }
        }
    });
});
</script>

<div class="card p-4 shadow-sm" style="max-width: 410px; width: 80%;">
    <h5 class="fw-bold mb-4">Student Status Overview</h5>
    <br>

    <div style="display:flex; align-items:center; gap:30px;">

        <!-- Chart -->
        <div style="width: 180px; height:160px;">
            <canvas id="statusBarChart"></canvas>
        </div>

        <!-- Legend (RIGHT SIDE) -->
        <div style="flex:1;">
            <ul style="list-style:none; padding:0; margin:0;">
                <?php 
                $sColors = ['#1cc88a', '#e74a3b'];
                $totalS = array_sum($statusData);

                foreach($statusLabels as $index => $label): 
                    $val = $statusData[$index];
                    $per = ($totalS > 0) ? round(($val / $totalS) * 100) : 0;
                ?>
                <li style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">

                    <!-- Left side label -->
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="
                            width:10px;
                            height:10px;
                            border-radius:50%;
                            background: <?= $sColors[$index] ?>;
                            display:inline-block;
                        "></span>

                        <span style="font-size:14px;"><?= $label ?></span>
                    </div>

                    <!-- Right side value -->
                    <span style="font-size:13px; color:#6b7280;">
                        <?= $val ?> (<?= $per ?>%)
                    </span>

                </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sCtx = document.getElementById('statusBarChart').getContext('2d');

    new Chart(sCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($statusLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($statusData); ?>,
                backgroundColor: ['#1cc88a', '#e74a3b'],
                borderRadius: 6,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
</div>