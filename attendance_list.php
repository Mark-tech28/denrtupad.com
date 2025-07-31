
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Attendance Records</h3>
        <div class="card-tools align-middle">
            <button class="btn btn-danger btn-sm py-1 rounded-0 me-2" type="button" id="delete_all_attendance">
                <i class="fa fa-trash"></i> Delete All Attendance
            </button>
            <button class="btn btn-info btn-sm py-1 rounded-0 me-2" type="button" id="export_excel"><i class="fa fa-file-excel"></i> Export Excel</button>
            <button class="btn btn-success btn-sm py-1 rounded-0" type="button" id="print"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="employee_filter" class="form-label">Select Employee:</label>
                <select class="form-select form-select-sm" id="employee_filter">
                    <option value="">All Employees</option>
                    <?php 
                    $employees = $conn->query("SELECT e.employee_id, e.employee_code, (e.lastname || ', ' || e.firstname || ' ' || e.middlename) as fullname 
                                             FROM employee_list e 
                                             WHERE e.status = 1 
                                             ORDER BY e.lastname, e.firstname");
                    while($emp = $employees->fetchArray()):
                    ?>
                    <option value="<?php echo $emp['employee_id']; ?>"><?php echo $emp['employee_code'] . ' - ' . $emp['fullname']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Date From:</label>
                <input type="date" class="form-control form-control-sm" id="date_from" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Date To:</label>
                <input type="date" class="form-control form-control-sm" id="date_to" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button class="btn btn-primary btn-sm" type="button" id="filter_records">Filter</button>
                    <button class="btn btn-secondary btn-sm" type="button" id="reset_filter">Reset</button>
                </div>
            </div>
        </div>
        
        <table class="table table-bordered table-hover" id="att-list">
            <colgroup>
                <col width="5%">
                <col width="45%">
                <col width="25%">
                <col width="25%">
            </colgroup>
            <thead>
                <tr>
                    <th class="p-0 text-center">#</th>
                    <th class="p-0 text-center">Employee</th>
                    <th class="p-0 text-center">Attendance Type</th>
                    <th class="p-0 text-center">Attendance DateTime</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Build query with filters
                $where_conditions = [];
                $params = [];
                
                // Employee filter
                if(isset($_GET['employee_id']) && !empty($_GET['employee_id'])){
                    $where_conditions[] = "a.employee_id = '" . $_GET['employee_id'] . "'";
                }
                
                // Date range filter
                $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d');
                $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
                $where_conditions[] = "date(a.date_created) BETWEEN '$date_from' AND '$date_to'";
                
                $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
                
                $sql = "SELECT a.*,t.name as tname,(e.lastname || ', ' || e.firstname || ' ' || e.middlename) as `fullname`,e.employee_code 
                        FROM `attendance_list` a 
                        inner join employee_list e on a.employee_id = e.employee_id 
                        inner join att_type_list t on a.att_type_id = t.att_type_id 
                        $where_clause 
                        ORDER BY a.date_created DESC";
                
                $att_qry = $conn->query($sql);
                $i = 1;
                while($row = $att_qry->fetchArray()):
                    $bg = "primary";
                    if(in_array($row['att_type_id'],array(2,4)))
                    $bg = "danger";
                ?>
                <tr>
                    <td class="align-middle py-0 px-1 text-center"><?php echo $i++; ?></td>
                    <td class="align-middle py-0 px-1">
                        <p class="m-0">
                            <small><b>Employee Code:</b> <?php echo $row['employee_code'] ?></small><br>
                            <small><b>Name:</b> <?php echo $row['fullname'] ?></small>
                        </p>
                    </td>
                    <td class="align-middle py-0 px-1 text-center">
                        <span class="badge bg-<?php echo $bg ?>"><?php echo $row['tname'] ?></span>
                    </td>
                    <td class="align-middle py-0 px-1 text-end"><?php echo date("M d, Y h:i A",strtotime($row['date_created']))  ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    $(function(){
        // Delete all attendance records
        $('#delete_all_attendance').click(function(){
            _conf("⚠️ WARNING: This will delete ALL attendance records from the database! This action cannot be undone. Are you absolutely sure you want to proceed?",'delete_all_attendance',[])
        })
        
        // Filter records
        $('#filter_records').click(function(){
            var employee_id = $('#employee_filter').val();
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();
            
            if(!date_from || !date_to){
                alert('Please select both date range');
                return;
            }
            
            if(date_from > date_to){
                alert('Date From cannot be greater than Date To');
                return;
            }
            
            // Reload page with filters
            var url = window.location.href.split('?')[0];
            var params = [];
            if(employee_id) params.push('employee_id=' + employee_id);
            if(date_from) params.push('date_from=' + date_from);
            if(date_to) params.push('date_to=' + date_to);
            
            if(params.length > 0){
                url += '?' + params.join('&');
            }
            
            window.location.href = url;
        });
        
        // Reset filter
        $('#reset_filter').click(function(){
            window.location.href = window.location.href.split('?')[0];
        });
        
        // Export Excel
        $('#export_excel').click(function(){
            var employee_id = $('#employee_filter').val();
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();
            
            if(!employee_id){
                alert('Please select an employee to export their attendance records');
                return;
            }
            
            if(!date_from || !date_to){
                alert('Please select both date range');
                return;
            }
            
            if(date_from > date_to){
                alert('Date From cannot be greater than Date To');
                return;
            }
            
            // Open Excel export in new window
            var url = 'export_attendance_excel.php?employee_id=' + employee_id + '&date_from=' + date_from + '&date_to=' + date_to;
            window.open(url, '_blank');
        });
        
        // Print function
        $('#print').click(function(){
            var _h = $("head").clone()
            var _table = $('#att-list').clone()
            var _el = $("<div>")
            _el.append(_h)
            _el.append("<h2 class='text-center'>Attendance List</h2>")
            _el.append("<hr/>")
            _el.append(_table)

            var nw = window.open("","_blank","width=1200,height=900")
                     nw.document.write(_el.html())
                     nw.document.close()
                     setTimeout(() => {
                         nw.print()
                         setTimeout(() => {
                         nw.close()
                         }, 200);
                     }, 200);
        })
        
        // Set filter values from URL parameters
        var urlParams = new URLSearchParams(window.location.search);
        if(urlParams.get('employee_id')){
            $('#employee_filter').val(urlParams.get('employee_id'));
        }
        if(urlParams.get('date_from')){
            $('#date_from').val(urlParams.get('date_from'));
        }
        if(urlParams.get('date_to')){
            $('#date_to').val(urlParams.get('date_to'));
        }
    })
    
    function delete_all_attendance(){
        $('#confirm_modal button').attr('disabled',true)
        $.ajax({
            url: 'Actions.php?a=delete_all_attendance',
            method:'POST',
            dataType:'JSON',
            error:err=>{
                console.log(err)
                alert("An error occurred.")
                $('#confirm_modal button').attr('disabled',false)
            },
            success:function(resp){
                if(resp.status == 'success'){
                    alert("All attendance records have been successfully deleted from the database.")
                    location.reload()
                }else{
                    alert("An error occurred: " + resp.msg)
                    $('#confirm_modal button').attr('disabled',false)
                }
            }
        })
    }
</script>