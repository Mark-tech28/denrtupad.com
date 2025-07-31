<?php
session_start();
require_once('DBConnection.php');

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("location:./");
    exit;
}

// Get employee ID from request
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

if(empty($employee_id)){
    die("Employee ID is required");
}

// Get employee details
$employee = $conn->query("SELECT e.*, d.name as dept_name, dd.name as desg_name 
                         FROM employee_list e 
                         LEFT JOIN department_list d ON e.department_id = d.department_id 
                         LEFT JOIN designation_list dd ON e.designation_id = dd.designation_id 
                         WHERE e.employee_id = '$employee_id'")->fetchArray();

if(!$employee){
    die("Employee not found");
}

// Get attendance records for the employee
$attendance_records = $conn->query("SELECT a.*, t.name as type_name, 
                                   date(a.date_created) as attendance_date,
                                   time(a.date_created) as attendance_time
                                   FROM attendance_list a 
                                   INNER JOIN att_type_list t ON a.att_type_id = t.att_type_id 
                                   WHERE a.employee_id = '$employee_id' 
                                   AND date(a.date_created) BETWEEN '$date_from' AND '$date_to'
                                   ORDER BY a.date_created DESC");

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Attendance_Record_' . $employee['employee_code'] . '_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Start Excel content
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .header { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .employee-info { margin-bottom: 20px; }
        .date-range { margin-bottom: 20px; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">TUPAD Daily Time Record System</div>
    <div class="header">Employee Attendance Report</div>
    
    <div class="employee-info">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 150px;"><strong>Employee Code:</strong></td>
                <td style="border: none;"><?php echo $employee['employee_code']; ?></td>
                <td style="border: none; width: 150px;"><strong>Department:</strong></td>
                <td style="border: none;"><?php echo $employee['dept_name']; ?></td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Employee Name:</strong></td>
                <td style="border: none;"><?php echo $employee['lastname'] . ', ' . $employee['firstname'] . ' ' . $employee['middlename']; ?></td>
                <td style="border: none;"><strong>Designation:</strong></td>
                <td style="border: none;"><?php echo $employee['desg_name']; ?></td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Gender:</strong></td>
                <td style="border: none;"><?php echo $employee['gender']; ?></td>
                <td style="border: none;"><strong>Contact:</strong></td>
                <td style="border: none;"><?php echo $employee['contact']; ?></td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Email:</strong></td>
                <td style="border: none;"><?php echo $employee['email']; ?></td>
                <td style="border: none;"><strong>Status:</strong></td>
                <td style="border: none;"><?php echo $employee['status'] == 1 ? 'Active' : 'Inactive'; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="date-range">
        <strong>Report Period:</strong> <?php echo date('F d, Y', strtotime($date_from)); ?> to <?php echo date('F d, Y', strtotime($date_to)); ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Date</th>
                <th>Time</th>
                <th>Attendance Type</th>
                <th>Day of Week</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            $total_records = 0;
            while($record = $attendance_records->fetchArray()): 
                $total_records++;
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                <td><?php echo date('h:i A', strtotime($record['attendance_time'])); ?></td>
                <td><?php echo $record['type_name']; ?></td>
                <td><?php echo date('l', strtotime($record['attendance_date'])); ?></td>
            </tr>
            <?php endwhile; ?>
            
            <?php if($total_records == 0): ?>
            <tr>
                <td colspan="5" style="text-align: center; font-style: italic;">No attendance records found for the selected period.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 50%;">
                    <strong>Total Records:</strong> <?php echo $total_records; ?>
                </td>
                <td style="border: none; text-align: right;">
                    <strong>Generated on:</strong> <?php echo date('F d, Y h:i A'); ?>
                </td>
            </tr>
        </table>
    </div>
</body>
</html> 