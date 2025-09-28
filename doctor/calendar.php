<?php
// doctor/calendar.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();

// Fetch approved appointments for doctor with prescription and treatment status
$stmt = $pdo->prepare("
  SELECT a.id, a.date_time, a.reason, u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone,
         (SELECT COUNT(*) FROM prescriptions WHERE appointment_id = a.id) as prescription_count,
         (SELECT COUNT(*) FROM treatments WHERE appointment_id = a.id) as treatment_count
  FROM appointments a
  JOIN users u ON a.patient_id = u.id
  WHERE a.doctor_id = :did AND a.status = 'approved'
  ORDER BY a.date_time ASC
");
$stmt->execute([':did' => $did]);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert appointments to FullCalendar event objects
$events = [];
foreach ($apps as $a) {
    // Determine event color based on status
    $backgroundColor = '#3b82f6'; // Default blue for approved
    if ($a['prescription_count'] > 0 && $a['treatment_count'] > 0) {
        $backgroundColor = '#9333ea'; // Purple for both
    } elseif ($a['prescription_count'] > 0) {
        $backgroundColor = '#22c55e'; // Green for prescription only
    } elseif ($a['treatment_count'] > 0) {
        $backgroundColor = '#f97316'; // Orange for treatment only
    }
    
    $events[] = [
        'id'    => $a['id'],
        'title' => $a['patient_name'],
        'start' => date('c', strtotime($a['date_time'])),
        'backgroundColor' => $backgroundColor,
        'extendedProps' => [
            'reason' => $a['reason'],
            'patient_email' => $a['patient_email'],
            'patient_phone' => $a['patient_phone'],
            'prescription_count' => $a['prescription_count'],
            'treatment_count' => $a['treatment_count']
        ]
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Appointments Calendar - Doctor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.js"></script>
  <style>
    .fc-event {
      border: none !important;
      border-radius: 6px !important;
    }
    .fc-event-title {
      font-weight: 500;
    }
    .fc-daygrid-event {
      margin: 1px 0;
    }
    .loading {
      opacity: 0.6;
      pointer-events: none;
    }
  </style>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
            <i data-lucide="calendar-days" class="w-8 h-8 text-indigo-600"></i>
            Appointments Calendar
          </h2>
          <p class="text-gray-600 mt-2">Manage your patient appointments and medical records</p>
        </div>
        <div class="flex gap-3">
          <button onclick="refreshCalendar()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Refresh
          </button>
          <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Dashboard
          </a>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
              <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Appointments</p>
              <p class="text-2xl font-semibold text-gray-900"><?= count($apps) ?></p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
              <i data-lucide="pill" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">With Prescriptions</p>
              <p class="text-2xl font-semibold text-gray-900"><?= count(array_filter($apps, fn($a) => $a['prescription_count'] > 0)) ?></p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-100 text-orange-600">
              <i data-lucide="stethoscope" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">With Treatments</p>
              <p class="text-2xl font-semibold text-gray-900"><?= count(array_filter($apps, fn($a) => $a['treatment_count'] > 0)) ?></p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
              <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Complete</p>
              <p class="text-2xl font-semibold text-gray-900"><?= count(array_filter($apps, fn($a) => $a['prescription_count'] > 0 && $a['treatment_count'] > 0)) ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Legend</h3>
        <div class="flex flex-wrap gap-6 text-sm">
          <div class="flex items-center gap-2">
            <span class="w-4 h-4 bg-blue-500 rounded"></span>
            <span class="text-gray-700">Approved Appointments</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-4 h-4 bg-green-500 rounded"></span>
            <span class="text-gray-700">With Prescription</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-4 h-4 bg-orange-500 rounded"></span>
            <span class="text-gray-700">With Treatment</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-4 h-4 bg-purple-500 rounded"></span>
            <span class="text-gray-700">Complete (Both)</span>
          </div>
        </div>
      </div>

      <!-- Calendar -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div id="calendar" class="p-6"></div>
      </div>
    </div>
  </main>

  <!-- Enhanced Modal -->
  <div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Appointment Details</h3>
        <button onclick="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div class="p-6">
        <div id="modal-content">
          <div class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <span class="ml-3 text-gray-600">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script>
    let calendar;
    let currentAppointmentId = null;

    document.addEventListener('DOMContentLoaded', function() {
      initializeCalendar();
      lucide.createIcons();
    });

    function initializeCalendar() {
      const calendarEl = document.getElementById('calendar');
      calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?= json_encode($events) ?>,
        eventClick: function(info) {
          currentAppointmentId = info.event.id;
          openModal();
          loadAppointment(info.event.id);
        },
        eventDidMount: function(info) {
          // Add tooltip with appointment details
          const event = info.event;
          const props = event.extendedProps;
          const tooltip = `
            <div class="text-sm">
              <div class="font-semibold">${event.title}</div>
              <div class="text-gray-600">${props.reason}</div>
              ${props.prescription_count > 0 ? '<div class="text-green-600">✓ Prescription</div>' : ''}
              ${props.treatment_count > 0 ? '<div class="text-orange-600">✓ Treatment</div>' : ''}
            </div>
          `;
          info.el.setAttribute('title', tooltip);
        }
      });
      calendar.render();
    }

    function openModal() {
      const modal = document.getElementById('modal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      const modal = document.getElementById('modal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = 'auto';
      currentAppointmentId = null;
    }

    function refreshCalendar() {
      if (calendar) {
        calendar.refetchEvents();
        // Refresh the page to update stats
        setTimeout(() => location.reload(), 500);
      }
    }

    async function loadAppointment(appId) {
      try {
        showLoading();
        const response = await fetch(`api/get_appointment.php?id=${appId}`);
        const data = await response.json();
        
        if (data.ok) {
          displayAppointmentDetails(data);
        } else {
          showError('Failed to load appointment details: ' + (data.msg || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error loading appointment:', error);
        showError('Network error. Please try again.');
      }
    }

    function displayAppointmentDetails(data) {
      const props = data.extendedProps || {};
      const hasPrescription = props.prescription_count > 0;
      const hasTreatment = props.treatment_count > 0;
      
      document.getElementById('modal-content').innerHTML = `
        <div class="space-y-6">
          <!-- Patient Info -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
              Patient Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-500">Name:</span>
                <span class="font-medium text-gray-900">${data.patient_name}</span>
              </div>
              <div>
                <span class="text-gray-500">Email:</span>
                <span class="font-medium text-gray-900">${props.patient_email || 'N/A'}</span>
              </div>
              <div>
                <span class="text-gray-500">Phone:</span>
                <span class="font-medium text-gray-900">${props.patient_phone || 'N/A'}</span>
              </div>
              <div>
                <span class="text-gray-500">Appointment:</span>
                <span class="font-medium text-gray-900">${formatDateTime(data.date_time)}</span>
              </div>
            </div>
            <div class="mt-3">
              <span class="text-gray-500">Reason:</span>
              <span class="font-medium text-gray-900">${data.reason}</span>
            </div>
          </div>

          <!-- Status Indicators -->
          <div class="flex gap-4">
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg ${hasPrescription ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">
              <i data-lucide="pill" class="w-4 h-4"></i>
              <span class="text-sm font-medium">Prescription ${hasPrescription ? '✓' : 'Pending'}</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg ${hasTreatment ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600'}">
              <i data-lucide="stethoscope" class="w-4 h-4"></i>
              <span class="text-sm font-medium">Treatment ${hasTreatment ? '✓' : 'Pending'}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-wrap gap-3">
            <button onclick="showPrescriptionForm(${data.id})" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
              <i data-lucide="pill" class="w-4 h-4"></i>
              ${hasPrescription ? 'Edit Prescription' : 'Add Prescription'}
            </button>
            <button onclick="showTreatmentForm(${data.id})" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
              <i data-lucide="stethoscope" class="w-4 h-4"></i>
              ${hasTreatment ? 'Edit Treatment' : 'Add Treatment'}
            </button>
            <a href="patient_history.php?patient_id=${data.patient_id}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
              <i data-lucide="history" class="w-4 h-4"></i>
              View History
            </a>
          </div>

          <!-- Form Area -->
          <div id="form-area" class="hidden"></div>
        </div>
      `;
      lucide.createIcons();
    }

    function showPrescriptionForm(appId) {
      document.getElementById('form-area').innerHTML = `
        <div class="bg-indigo-50 rounded-lg p-4">
          <h4 class="font-semibold text-indigo-900 mb-4 flex items-center gap-2">
            <i data-lucide="pill" class="w-5 h-5"></i>
            Prescription Form
          </h4>
          <form onsubmit="submitPrescription(event, ${appId})" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Name *</label>
                <input name="medicine" placeholder="e.g., Paracetamol 500mg" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dosage</label>
                <input name="dosage" placeholder="e.g., 1 tablet twice daily" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
              <input name="duration" placeholder="e.g., 7 days" 
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
              <textarea name="instructions" placeholder="Special instructions for the patient" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3"></textarea>
            </div>
            <div class="flex gap-3">
              <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                Save Prescription
              </button>
              <button type="button" onclick="hideForm()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Cancel
              </button>
            </div>
          </form>
        </div>
      `;
      document.getElementById('form-area').classList.remove('hidden');
      lucide.createIcons();
    }

    function showTreatmentForm(appId) {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('form-area').innerHTML = `
        <div class="bg-green-50 rounded-lg p-4">
          <h4 class="font-semibold text-green-900 mb-4 flex items-center gap-2">
            <i data-lucide="stethoscope" class="w-5 h-5"></i>
            Treatment Form
          </h4>
          <form onsubmit="submitTreatment(event, ${appId})" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Treatment Name *</label>
              <input name="treatment_name" placeholder="e.g., Blood Test, X-Ray" 
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="date" value="${today}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cost (₹) *</label>
                <input type="number" step="0.01" name="cost" placeholder="0.00" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea name="notes" placeholder="Additional notes about the treatment" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" rows="3"></textarea>
            </div>
            <div class="flex gap-3">
              <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                Save Treatment
              </button>
              <button type="button" onclick="hideForm()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Cancel
              </button>
            </div>
          </form>
        </div>
      `;
      document.getElementById('form-area').classList.remove('hidden');
      lucide.createIcons();
    }

    function hideForm() {
      document.getElementById('form-area').classList.add('hidden');
    }

    async function submitPrescription(e, appId) {
      e.preventDefault();
      const formData = new FormData(e.target);
      formData.append('appointment_id', appId);
      formData.append('csrf', '<?= csrf() ?>');
      
      try {
        showFormLoading(e.target);
        const response = await fetch('api/add_prescription_ajax.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();
        handleFormResponse(data, appId, 'prescription');
      } catch (error) {
        console.error('Error submitting prescription:', error);
        showError('Network error. Please try again.');
        hideFormLoading(e.target);
      }
    }

    async function submitTreatment(e, appId) {
      e.preventDefault();
      const formData = new FormData(e.target);
      formData.append('appointment_id', appId);
      formData.append('csrf', '<?= csrf() ?>');
      
      try {
        showFormLoading(e.target);
        const response = await fetch('api/add_treatment_ajax.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();
        handleFormResponse(data, appId, 'treatment');
      } catch (error) {
        console.error('Error submitting treatment:', error);
        showError('Network error. Please try again.');
        hideFormLoading(e.target);
      }
    }

    function handleFormResponse(data, appId, type) {
      hideFormLoading();
      if (data.ok) {
        showSuccess(data.msg || 'Saved successfully!');
        recolorEvent(appId, type);
        setTimeout(() => {
          closeModal();
          refreshCalendar();
        }, 1500);
      } else {
        showError(data.msg || 'Failed to save. Please try again.');
      }
    }

    function recolorEvent(appId, type) {
      const event = calendar.getEventById(String(appId));
      if (!event) return;

      const colorMap = {
        'prescription': '#22c55e', // green-500
        'treatment': '#f97316',    // orange-500
        'both': '#9333ea'          // purple-600
      };

      const currentColor = event.backgroundColor;
      if ((type === 'prescription' && currentColor === '#f97316') ||
          (type === 'treatment' && currentColor === '#22c55e')) {
        event.setProp('backgroundColor', colorMap['both']);
      } else {
        event.setProp('backgroundColor', colorMap[type]);
      }
    }

    function showLoading() {
      document.getElementById('modal-content').innerHTML = `
        <div class="flex items-center justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <span class="ml-3 text-gray-600">Loading...</span>
        </div>
      `;
    }

    function showFormLoading(form) {
      const submitBtn = form.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white inline-block mr-2"></div>Saving...';
    }

    function hideFormLoading() {
      const submitBtns = document.querySelectorAll('button[type="submit"]');
      submitBtns.forEach(btn => {
        btn.disabled = false;
        btn.innerHTML = btn.innerHTML.replace(/<div.*?<\/div>/, '').replace('Saving...', 'Save');
      });
    }

    function showSuccess(message) {
      showNotification(message, 'success');
    }

    function showError(message) {
      showNotification(message, 'error');
    }

    function showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      }`;
      notification.innerHTML = `
        <div class="flex items-center gap-2">
          <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="w-5 h-5"></i>
          <span>${message}</span>
        </div>
      `;
      document.body.appendChild(notification);
      lucide.createIcons();
      
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }

    function formatDateTime(dateTime) {
      const date = new Date(dateTime);
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    // Close modal on outside click
    document.getElementById('modal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });
  </script>
</body>
</html>
