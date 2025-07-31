<?php
// Get total employees count
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employee_list WHERE status = 1")->fetchArray()['count'];

// Get today's attendance statistics
$today = date("Y-m-d");
$today_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance_list WHERE date(date_created) = '$today'")->fetchArray()['count'];

// Get attendance by type for today
$time_in_today = $conn->query("SELECT COUNT(*) as count FROM attendance_list a 
                               INNER JOIN att_type_list t ON a.att_type_id = t.att_type_id 
                               WHERE date(a.date_created) = '$today' AND t.name = 'Time In'")->fetchArray()['count'];

$time_out_today = $conn->query("SELECT COUNT(*) as count FROM attendance_list a 
                                INNER JOIN att_type_list t ON a.att_type_id = t.att_type_id 
                                WHERE date(a.date_created) = '$today' AND t.name = 'Time Out'")->fetchArray()['count'];

$ot_in_today = $conn->query("SELECT COUNT(*) as count FROM attendance_list a 
                             INNER JOIN att_type_list t ON a.att_type_id = t.att_type_id 
                             WHERE date(a.date_created) = '$today' AND t.name = 'OT In'")->fetchArray()['count'];

$ot_out_today = $conn->query("SELECT COUNT(*) as count FROM attendance_list a 
                              INNER JOIN att_type_list t ON a.att_type_id = t.att_type_id 
                              WHERE date(a.date_created) = '$today' AND t.name = 'OT Out'")->fetchArray()['count'];

// Get this month's attendance statistics
$current_month = date("Y-m");
$monthly_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance_list 
                                   WHERE strftime('%Y-%m', date_created) = '$current_month'")->fetchArray()['count'];

// Get department statistics
$dept_stats = $conn->query("SELECT d.name as dept_name, COUNT(e.employee_id) as emp_count 
                           FROM department_list d 
                           LEFT JOIN employee_list e ON d.department_id = e.department_id AND e.status = 1 
                           WHERE d.status = 1 
                           GROUP BY d.department_id, d.name 
                           ORDER BY emp_count DESC");

// Get recent attendance (last 5 records)
$recent_attendance = $conn->query("SELECT a.*, t.name as type_name, 
                                  (e.lastname || ', ' || e.firstname || ' ' || e.middlename) as fullname,
                                  e.employee_code 
                                  FROM attendance_list a 
                                  INNER JOIN employee_list e ON a.employee_id = e.employee_id 
                                  INNER JOIN att_type_list t ON a.att_type_id = t.att_type_id 
                                  ORDER BY a.date_created DESC LIMIT 5");
?>

<div class="container-fluid">
    <h3 class="text-center mb-4">TUPAD Daily Time Record System</h3>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $total_employees; ?></h4>
                            <p class="mb-0">Total Employees</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $today_attendance; ?></h4>
                            <p class="mb-0">Today's Attendance</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $monthly_attendance; ?></h4>
                            <p class="mb-0">This Month's Records</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $time_in_today; ?></h4>
                            <p class="mb-0">Time In Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-sign-in-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Statistics -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Today's Attendance Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-primary"><?php echo $time_in_today; ?></h3>
                                <p class="mb-0">Time In</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-danger"><?php echo $time_out_today; ?></h3>
                                <p class="mb-0">Time Out</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success"><?php echo $ot_in_today; ?></h3>
                                <p class="mb-0">OT In</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-warning"><?php echo $ot_out_today; ?></h3>
                                <p class="mb-0">OT Out</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Department Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th class="text-center">Employees</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($dept = $dept_stats->fetchArray()): ?>
                                <tr>
                                    <td><?php echo $dept['dept_name']; ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $dept['emp_count']; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Attendance -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Attendance Records</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($att = $recent_attendance->fetchArray()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $att['employee_code']; ?></strong><br>
                                        <small><?php echo $att['fullname']; ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $badge_class = "bg-primary";
                                        if($att['type_name'] == 'Time Out' || $att['type_name'] == 'OT Out') {
                                            $badge_class = "bg-danger";
                                        } elseif($att['type_name'] == 'OT In') {
                                            $badge_class = "bg-success";
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $att['type_name']; ?></span>
                                    </td>
                                    <td><?php echo date("M d, Y h:i A", strtotime($att['date_created'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}
</style>