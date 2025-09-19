<?php
include("modern-header.php");
include("dbconnection.php");
if(isset($_POST['submit']))
{
	if(isset($_GET['editid']))
	{
			$sql ="UPDATE department SET departmentname='$_POST[departmentname]',description='$_POST[textarea]',status='$_POST[select]' WHERE departmentid='$_GET[editid]'";
		if($qsql = mysqli_query($con,$sql))
		{
			echo "<script>alert('department record updated successfully...');</script>";
		}
		else
		{
			echo mysqli_error($con);
		}	
	}
	else
	{
	$sql ="INSERT INTO department(departmentname,description,status) values('$_POST[departmentname]','$_POST[textarea]','$_POST[select]')";
	if($qsql = mysqli_query($con,$sql))
	{
		echo "<script>alert('Department record inserted successfully...');</script>";
	}
	else
	{
		echo mysqli_error($con);
	}
}
}
if(isset($_GET['editid']))
{
	$sql="SELECT * FROM department WHERE departmentid='$_GET[editid]' ";
	$qsql = mysqli_query($con,$sql);
	$rsedit = mysqli_fetch_array($qsql);
	
}
?>


<!-- Main Content -->
<main class="pt-24 min-h-screen bg-slate-50">
    <div class="container-modern py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-4">
                <i data-lucide="building" class="w-5 h-5 text-healsync-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Department Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">
                <?php echo isset($_GET['editid']) ? 'Edit Department' : 'Add New Department'; ?>
            </h1>
            <p class="text-slate-600">
                <?php echo isset($_GET['editid']) ? 'Update department information and settings' : 'Create a new department in the HealSync system'; ?>
            </p>
        </div>

        <!-- Department Form -->
        <div class="max-w-2xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmdept" onSubmit="return validateform()" class="form-modern">
                        
                        <!-- Department Information Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="info" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                Department Information
                            </h3>
                            
                            <!-- Department Name -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Department Name *</label>
                                <input class="form-input-modern" type="text" name="departmentname" id="departmentname" 
                                    value="<?php echo $rsedit['departmentname']; ?>" 
                                    placeholder="Enter department name" required />
                                <p class="text-xs text-slate-500 mt-1">Enter a unique name for the department</p>
                            </div>

                            <!-- Description -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Description</label>
                                <textarea class="form-input-modern" name="textarea" id="textarea" rows="4" 
                                    placeholder="Enter department description"><?php echo $rsedit['description']; ?></textarea>
                                <p class="text-xs text-slate-500 mt-1">Provide a brief description of the department's services</p>
                            </div>

                            <!-- Status -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Status *</label>
                                <select name="select" id="select" class="form-input-modern" required>
                                    <option value="">Select Status</option>
                                    <?php
                                    $arr = array("Active","Inactive");
                                    foreach($arr as $val)
                                    {
                                        if($val == $rsedit['status'])
                                        {
                                            echo "<option value='$val' selected>$val</option>";
                                        }
                                        else
                                        {
                                            echo "<option value='$val'>$val</option>";			  
                                        }
                                    }
                                    ?>
                                </select>
                                <p class="text-xs text-slate-500 mt-1">Set the department status (Active departments are available for appointments)</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                <?php echo isset($_GET['editid']) ? 'Update Department' : 'Create Department'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Information Card -->
            <div class="card-modern mt-8">
                <div class="card-body">
                    <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                        <i data-lucide="help-circle" class="w-5 h-5 mr-2 text-healsync-500"></i>
                        Department Guidelines
                    </h3>
                    <div class="space-y-3 text-sm text-slate-600">
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Department names should be unique and descriptive</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Active departments will be available for patient appointments</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Inactive departments will be hidden from public booking</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Descriptions help patients understand department services</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'modern-footer.php';?>

<script>
// Initialize Lucide icons
lucide.createIcons();

// Modern form validation
function validateform() {
    const form = document.frmdept;
    const alphaspaceExp = /^[a-zA-Z\s]+$/;
    
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Department name validation
    if (form.departmentname.value.trim() === "") {
        showError("Department Name Required", "Please enter the department name.");
        form.departmentname.classList.add('border-red-500');
        form.departmentname.focus();
        return false;
    }
    
    if (!form.departmentname.value.match(alphaspaceExp)) {
        showError("Invalid Department Name", "Department name should contain only letters and spaces.");
        form.departmentname.classList.add('border-red-500');
        form.departmentname.focus();
        return false;
    }
    
    // Status validation
    if (form.select.value === "") {
        showError("Status Required", "Please select the department status.");
        form.select.classList.add('border-red-500');
        form.select.focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Processing...';
    submitBtn.disabled = true;
    
    return true;
}

// Modern error display
function showError(title, message) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#0ea5e9',
        background: '#ffffff',
        color: '#1e293b'
    });
}

// Enhanced input validation
document.querySelectorAll('.form-input-modern').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        if (this.value.trim()) {
            this.classList.add('border-healsync-500/50');
        } else {
            this.classList.remove('border-healsync-500/50');
        }
    });
});

// Auto-capitalize department name
document.getElementById('departmentname').addEventListener('input', function() {
    // Capitalize first letter of each word
    this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
});

// Status change handler
document.getElementById('select').addEventListener('change', function() {
    const status = this.value;
    const helpText = this.parentElement.querySelector('.text-xs');
    
    if (status === 'Active') {
        helpText.textContent = 'Active departments are visible to patients and available for appointments';
        helpText.className = 'text-xs text-green-600 mt-1';
    } else if (status === 'Inactive') {
        helpText.textContent = 'Inactive departments are hidden from patients and not available for appointments';
        helpText.className = 'text-xs text-red-600 mt-1';
    } else {
        helpText.textContent = 'Set the department status (Active departments are available for appointments)';
        helpText.className = 'text-xs text-slate-500 mt-1';
    }
});

// Form submission success handling
<?php if(isset($_POST['submit']) && !mysqli_error($con)): ?>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?php echo isset($_GET["editid"]) ? "Department updated successfully!" : "Department created successfully!"; ?>',
    confirmButtonColor: '#0ea5e9'
}).then((result) => {
    if (result.isConfirmed) {
        <?php if(!isset($_GET['editid'])): ?>
        // Clear form for new entry
        document.frmdept.reset();
        <?php endif; ?>
    }
});
<?php endif; ?>
</script>
</script>