# Part 4: Appointment Dashboard & Status Management
## Question-Answer Format: MVC Architecture

---

## **Q1: What is the purpose of Part 4 in the Hospital system?**

**Ans:**  
Part 4 implements the **Appointment Status Management System**. It provides:

1. **Doctor Dashboard** — Doctors see today's appointments in a table + weekly grid (Mon-Fri) and can mark appointments as "Completed" or "No-Show"
2. **Admin Appointment Panel** — Admins manage all appointments across the hospital with filters (doctor, date, status) and can confirm pending appointments or cancel with reason
3. **Shared REST Endpoint** — Single API endpoint `PUT /api/appointments/{id}` handles all status transitions with role-based validation
4. **Real-time UI Updates** — AJAX calls update status badges without page reload

**Business Value:**  
- Doctors manage their daily schedule efficiently
- Admins have centralized control of all appointments
- Transparent status tracking (Pending → Confirmed → Completed/Cancelled/No-Show)

---

## **Q2: Explain the MVC architecture used in Part 4**

**Ans:**  
Part 4 follows strict **Model-View-Controller** pattern:

### **MODEL Layer (Data Access)**
**File:** `models/AppointmentModel.php`

**Responsibilities:**
- Database queries using PDO prepared statements
- No business logic here, purely data operations

**Key Methods:**
```php
getTodayByDoctor($doctorId)        // SELECT appointments WHERE date=TODAY
getWeekByDoctor($doctorId)         // SELECT appointments WHERE date IN this week
getAll($filters)                    // SELECT with doctor/date/status filters
updateStatus($id, $status, $reason) // UPDATE appointments SET status=?
getDoctorIncomeSummary($doctorId)  // SELECT SUM(fee) WHERE status=Completed
findById($id)                       // SELECT single appointment with joins
```

**Security:** All queries use prepared statements → prevents SQL injection

---

### **CONTROLLER Layer (Business Logic)**
**Files:** 
- `controllers/DoctorController.php` — Dashboard
- `controllers/AppointmentController.php` — Admin list & POST status update
- `controllers/ApiController.php` — REST endpoint validation

**Responsibilities:**
- Request handling
- Input validation
- Role-based permission checks
- Calling model methods
- Returning responses (HTML or JSON)

**DoctorController::dashboard()**
```php
public function dashboard() {
    requireRole('doctor');                          // 1. Auth check
    $doctor = $this->doctorModel->findByUserId($_SESSION['user_id']); // 2. Get doctor
    $todayAppointments = $this->appointmentModel->getTodayByDoctor($doctor['id']); // 3. Query
    $weekAppointments = $this->appointmentModel->getWeekByDoctor($doctor['id']);   // 4. Query
    $incomeSummary = $this->appointmentModel->getDoctorIncomeSummary($doctor['id']); // 5. Query
    require BASE_PATH . '/views/doctor/dashboard.php'; // 6. Render view
}
```

**AppointmentController::adminList()**
```php
public function adminList() {
    requireRole('admin');                           // 1. Auth check
    $filters = $_GET;                               // 2. Get filters
    $appointments = $this->appointmentModel->getAll($filters); // 3. Query with filters
    $doctors = $this->doctorModel->getAllAdmin();   // 4. For dropdown
    require BASE_PATH . '/views/admin/appointments.php'; // 5. Render
}
```

**ApiController::updateAppointmentStatus()**
```php
public function updateAppointmentStatus() {
    if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);
    
    $id = (int)($_GET['appointment_id'] ?? 0);
    $data = json_decode(file_get_contents('php://input'), true);
    $status = trim($data['status'] ?? '');
    $reason = trim($data['reason'] ?? '');
    $role = $_SESSION['role'];
    
    // Input validation
    if ($id <= 0) jsonResponse(['error' => 'Invalid ID'], 400);
    
    $validStatuses = ['Pending','Confirmed','Completed','Cancelled','No-Show'];
    if (!in_array($status, $validStatuses))
        jsonResponse(['error' => 'Invalid status'], 400);
    
    // Fetch appointment
    $appointment = $this->appointmentModel->findById($id);
    if (!$appointment) jsonResponse(['error' => 'Not found'], 404);
    
    // ROLE-BASED PERMISSION CHECK
    if ($role === 'doctor') {
        $doctor = (new DoctorModel())->findByUserId($_SESSION['user_id']);
        if (!$doctor || $doctor['id'] != $appointment['doctor_id'])
            jsonResponse(['error' => 'Forbidden'], 403);
        
        if (!in_array($status, ['Completed','No-Show']))
            jsonResponse(['error' => 'Doctors can only set Completed or No-Show'], 403);
    } 
    elseif ($role === 'admin') {
        if ($status === 'Cancelled' && empty($reason))
            jsonResponse(['error' => 'Cancellation reason is required'], 400);
    } 
    else {
        jsonResponse(['error' => 'Forbidden'], 403);
    }
    
    // Update
    $this->appointmentModel->updateStatus($id, $status, $reason ?: null);
    jsonResponse(['ok' => true, 'new_status' => $status]);
}
```

---

### **VIEW Layer (Presentation)**
**Files:**
- `views/doctor/dashboard.php` — Today's table + weekly grid
- `views/admin/appointments.php` — Filter bar + appointments table + modal

**Responsibilities:**
- Display HTML only
- Loop through data variables
- Render status badges
- Include action buttons
- No database queries
- No business logic

**Doctor Dashboard View:**
```php
<!-- Today's Appointments Table -->
<table>
    <thead>
        <tr><th>Time</th><th>Patient</th><th>Reason</th><th>Fee</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($displayAppointments as $a): ?>
            <tr id="today-row-<?= $a['id'] ?>">
                <td><?= substr($a['appointment_time'], 0, 5) ?></td>
                <td><?= sanitize($a['patient_name']) ?></td>
                <td><?= sanitize($a['reason']) ?></td>
                <td>$<?= number_format($a['fee_at_booking'], 2) ?></td>
                <td>
                    <span class="badge badge-<?= $statusColors[$a['status']] ?>" 
                          id="doc-badge-<?= $a['id'] ?>">
                        <?= $a['status'] ?>
                    </span>
                </td>
                <td>
                    <?php if (!in_array($a['status'], ['Completed','Cancelled'])): ?>
                        <button class="btn btn-success btn-sm" 
                                onclick="(async ()=>{ if(!confirm('Mark Completed?')) return; 
                                await updateAppointmentStatus(<?= (int)$a['id'] ?>,'Completed'); })()">
                            Mark Completed
                        </button>
                        <button class="btn btn-warn btn-sm" 
                                onclick="(async ()=>{ if(!confirm('Mark No-Show?')) return; 
                                await updateAppointmentStatus(<?= (int)$a['id'] ?>,'No-Show'); })()">
                            Mark No-Show
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Weekly Grid -->
<div class="weekly-grid-wrap">
    <!-- Time labels on left -->
    <div class="weekly-time-labels">
        <?php foreach (timeSlots() as $s): ?>
            <div class="time-slot"><?= $s ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Grid with appointments -->
    <div class="weekly-grid">
        <div class="days-header">
            <?php foreach ($days as $day): ?>
                <div class="day-col"><?= $day['day_name'] ?></div>
            <?php endforeach; ?>
        </div>

        <div class="grid-body">
            <?php foreach ($slots as $s): ?>
                <?php foreach ($days as $day): ?>
                    <div class="grid-cell">
                        <?php if (!empty($grid[$day['date']][$s])): $ap = $grid[$day['date']][$s]; ?>
                            <div class="weekly-appt badge-<?= strtolower($ap['status']) ?>">
                                <div><?= $s ?> - <?= sanitize($ap['patient_name']) ?></div>
                                <button onclick="(async ()=>{ await updateAppointmentStatus(<?= $ap['id'] ?>,'Completed'); })()">✓</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
```

**Admin Appointments View:**
```php
<!-- Filter Bar -->
<form method="GET" class="filter-bar">
    <select name="doctor_id" class="form-control">
        <option value="">All Doctors</option>
        <?php foreach ($doctors as $d): ?>
            <option value="<?= $d['id'] ?>" <?= ($_GET['doctor_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                <?= sanitize($d['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <input type="date" name="date" class="form-control" value="<?= sanitize($_GET['date'] ?? '') ?>">
    
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        <?php foreach (['Pending','Confirmed','Completed','Cancelled','No-Show'] as $st): ?>
            <option value="<?= $st ?>" <?= ($_GET['status'] ?? '') === $st ? 'selected' : '' ?>>
                <?= $st ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit" class="btn btn-primary">Filter</button>
</form>

<!-- Appointments Table -->
<table>
    <thead>
        <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date & Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr id="appt-row-<?= $a['id'] ?>">
                <td>#<?= $a['id'] ?></td>
                <td><?= sanitize($a['patient_name']) ?></td>
                <td><?= sanitize($a['doctor_name']) ?></td>
                <td><?= date('M j, Y', strtotime($a['appointment_date'])) ?> <?= substr($a['appointment_time'],0,5) ?></td>
                <td><?= sanitize($a['reason']) ?></td>
                <td>
                    <span class="badge badge-<?= strtolower($a['status']) ?>" id="appt-badge-<?= $a['id'] ?>">
                        <?= $a['status'] ?>
                    </span>
                </td>
                <td>
                    <?php if ($a['status'] === 'Pending'): ?>
                        <button class="btn btn-success btn-sm" 
                                onclick="(async ()=>{ if(!confirm('Confirm?')) return; 
                                await updateAppointmentStatus(<?= $a['id'] ?>,'Confirmed'); })()">
                            Confirm
                        </button>
                    <?php endif; ?>
                    
                    <?php if (!in_array($a['status'], ['Cancelled','Completed'])): ?>
                        <button class="btn btn-danger btn-sm" onclick="openCancelModal(<?= $a['id'] ?>)">
                            Cancel
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Cancel Modal -->
<div class="modal-backdrop" id="cancel-modal">
    <div class="modal">
        <h2>Cancel Appointment</h2>
        <form id="cancel-form" onsubmit="return false;">
            <input type="hidden" id="cancel-appt-id" name="appointment_id">
            <textarea name="reason" id="cancel-reason" class="form-control" placeholder="Reason..." required></textarea>
            <button id="cancel-submit" class="btn btn-danger">Cancel Appointment</button>
        </form>
    </div>
</div>
```

---

### **ROUTER Layer (Request Dispatcher)**
**File:** `index.php` (lines 86-109)

**Responsibilities:**
- Parse URL parameters
- Route to correct controller
- Call appropriate method
- Check HTTP method

```php
// Doctor routes
if (strpos($page, 'doctor/') === 0) {
    require_once BASE_PATH . '/controllers/DoctorController.php';
    $doctorController = new DoctorController();
    require_once BASE_PATH . '/controllers/AppointmentController.php';
    $appt = new AppointmentController();

    switch ($page) {
        case 'doctor/dashboard':
            $method === 'POST' ? $appt->updateStatus() : $doctorController->dashboard();
            break;
    }
    exit;
}

// Admin routes
if (strpos($page, 'admin/') === 0) {
    require_once BASE_PATH . '/controllers/AdminController.php';
    require_once BASE_PATH . '/controllers/AppointmentController.php';
    $admin = new AdminController();
    $appt  = new AppointmentController();

    switch ($page) {
        case 'admin/appointments':
            $method === 'POST' ? $appt->updateStatus() : $appt->adminList(); break;
    }
    exit;
}

// API routes
if (strpos($page, 'api/') === 0) {
    require_once BASE_PATH . '/controllers/ApiController.php';
    $api = new ApiController();

    switch ($page) {
        case 'api/appointments':
            if ($method === 'PUT') $api->updateAppointmentStatus();
            elseif ($method === 'POST') $api->cancelAppointment();
            else jsonResponse(['error' => 'Method not allowed'], 405);
            break;
    }
    exit;
}
```

---

### **CLIENT-SIDE Layer (AJAX)**
**File:** `public/js/main.js`

**Responsibility:** Handle all AJAX calls for status updates

```javascript
async function updateAppointmentStatus(id, status, reason = '') {
    try {
        const url = `${BASE_URL}/index.php?page=api/appointments&appointment_id=${encodeURIComponent(id)}`;
        const resp = await fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ status: status, reason: reason })
        });
        const json = await resp.json();
        if (!resp.ok || !json.ok) throw new Error(json.error || 'Update failed');

        // Update badge in-place
        const newStatus = json.new_status;
        const badgeSelectors = [`#doc-badge-${id}`, `#appt-badge-${id}`];
        badgeSelectors.forEach(sel => {
            const el = document.querySelector(sel);
            if (!el) return;
            el.textContent = newStatus;
            el.className = 'badge badge-' + (newStatus.toLowerCase().replace(/\s+/g,'-'));
        });

        return { ok: true, new_status: newStatus };
    } catch (err) {
        console.error('updateAppointmentStatus error', err);
        return { ok: false, error: err.message };
    }
}
```

---

## **Q3: What is the data flow when a doctor marks an appointment as "Completed"?**

**Ans:**

1. **User Action (Browser)** → Doctor clicks "Mark Completed" button

2. **JavaScript Handler (main.js)** →
   - Confirmation dialog shows
   - `updateAppointmentStatus(5, 'Completed')` called
   - Builds: `PUT /index.php?page=api/appointments&appointment_id=5`
   - Body: `{"status":"Completed","reason":null}`

3. **Router (index.php)** →
   - Parses: `page='api/appointments'`, `method='PUT'`
   - Routes to: `ApiController::updateAppointmentStatus()`

4. **Controller (ApiController)** →
   - ✓ Check: `isLoggedIn()`
   - ✓ Extract: `$id=5`, `$status='Completed'`
   - ✓ Validate: status in enum list
   - Model→`findById(5)` gets appointment data
   - ✓ Check: Doctor owns appointment (doctor_id match)
   - ✓ Check: Doctor allowed status (only Completed/No-Show)
   - Calls: `Model→updateStatus(5, 'Completed', null)`

5. **Model (AppointmentModel)** →
   - `UPDATE appointments SET status='Completed' WHERE id=5`
   - Returns: `true` (1 row affected)

6. **Response (JSON)** →
   ```json
   {"ok":true,"new_status":"Completed"}
   ```

7. **JavaScript Update (main.js)** →
   - Finds: `document.querySelector('#doc-badge-5')`
   - Updates: `textContent = 'Completed'`
   - Updates: `className = 'badge badge-completed'`
   - **Result:** Badge color changes to blue (no page reload)

8. **UI Feedback** → ✅ Status shows as "Completed" instantly

---

## **Q4: Explain the role-based permission structure in Part 4**

**Ans:**

### **Doctor Role**
**Can Do:**
- ✅ Update OWN appointments only
- ✅ Set status to: `Completed`, `No-Show`

**Cannot Do:**
- ❌ Update other doctors' appointments
- ❌ Set status to: Pending, Confirmed, Cancelled
- ❌ Require/use cancellation reason

**Check Code:**
```php
if ($role === 'doctor') {
    $doctor = (new DoctorModel())->findByUserId($_SESSION['user_id']);
    if (!$doctor || $doctor['id'] != $appointment['doctor_id'])
        jsonResponse(['error' => 'Forbidden'], 403);
    
    if (!in_array($status, ['Completed','No-Show']))
        jsonResponse(['error' => 'Doctors can only set Completed or No-Show'], 403);
}
```

---

### **Admin Role**
**Can Do:**
- ✅ Update ANY appointment
- ✅ Set status to: `Pending`, `Confirmed`, `Completed`, `Cancelled`, `No-Show`
- ✅ Provide cancellation reason (required for Cancelled)

**Cannot Do:**
- ❌ Cancel without reason (validation fails)

**Check Code:**
```php
elseif ($role === 'admin') {
    if ($status === 'Cancelled' && empty($reason))
        jsonResponse(['error' => 'Cancellation reason is required'], 400);
    // All other checks pass for admin
}
```

---

### **Patient Role**
**Can Do:**
- ❌ Cannot use this endpoint
- ✓ Use different endpoint: `POST /api/appointments/cancel`

**Cannot Do:**
- ❌ Call `PUT /api/appointments`

---

## **Q5: What database tables and fields are used in Part 4?**

**Ans:**

### **Main Table: appointments**
```sql
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,              -- Foreign key to users.id
    doctor_id INT NOT NULL,               -- Foreign key to doctors.id
    appointment_date DATE NOT NULL,       -- YYYY-MM-DD
    appointment_time TIME NOT NULL,       -- HH:MM:SS
    reason VARCHAR(255),                  -- Chief complaint
    fee_at_booking DECIMAL(10, 2),        -- Fee captured at booking time
    status ENUM('Pending','Confirmed','Completed','Cancelled','No-Show') DEFAULT 'Pending',
    cancel_reason TEXT,                   -- Admin's reason if cancelled
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX (doctor_id),
    INDEX (patient_id),
    INDEX (appointment_date),
    INDEX (status)
);
```

### **Supporting Tables Queried:**
**users** (for patient_name, doctor_name)
```
id | name | email | password | role | is_active | created_at
```

**doctors** (for doctor info)
```
id | user_id | specialization_id | available_days | consultation_fee | bio | photo_path
```

**specializations** (for appointment details)
```
id | name | description
```

---

## **Q6: What are the status transitions allowed in Part 4?**

**Ans:**

### **Status Enum Values**
- `Pending` — Initial state after booking
- `Confirmed` — Admin confirms the appointment
- `Completed` — Doctor marks as completed
- `Cancelled` — Admin cancels with reason
- `No-Show` — Doctor marks patient didn't show

### **Valid Transitions by Role**

**Doctor Actions:**
```
Pending → Completed (immediate or after confirmed)
Pending → No-Show
Confirmed → Completed
Confirmed → No-Show
```

**Admin Actions:**
```
Pending → Confirmed
Pending → Cancelled (with reason)
Confirmed → Completed
Confirmed → Cancelled (with reason)
Any → No-Show
```

**Final States (No further changes):**
```
✓ Completed (appointment done)
✓ Cancelled (appointment cancelled)
✓ No-Show (patient didn't show)
```

---

## **Q7: How does the weekly grid work in the Doctor Dashboard?**

**Ans:**

### **Structure**
- **Columns:** 5 (Monday through Friday)
- **Rows:** Time slots (09:00, 09:30, 10:00, ... 17:00 at 30-minute intervals)
- **Cells:** Display appointments as colored blocks

### **Data Generation**
```php
// Get Monday of current week
$monday = date('Y-m-d', strtotime('monday this week'));
$days = [];
for ($d = 0; $d < 5; $d++) {
    $date = date('Y-m-d', strtotime($monday . " +{$d} day"));
    $days[] = ['date' => $date, 'label' => date('D M j', strtotime($date))];
}

// Map appointments by date and time
$grid = [];
foreach ($weekAppointments as $a) {
    $d = $a['appointment_date'];
    $t = substr($a['appointment_time'], 0, 5); // HH:MM
    $grid[$d][$t] = $a;
}
```

### **Rendering**
```php
<div class="weekly-grid-wrap">
    <!-- Time labels on left -->
    <div class="weekly-time-labels">
        <?php foreach (timeSlots() as $s): ?>
            <div class="time-slot"><?= $s ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Grid cells -->
    <div class="grid-body">
        <?php foreach ($slots as $s): ?>
            <?php foreach ($days as $day): ?>
                <div class="grid-cell">
                    <?php if (!empty($grid[$day['date']][$s])): 
                        $ap = $grid[$day['date']][$s]; ?>
                        <div class="weekly-appt badge-<?= strtolower($ap['status']) ?>">
                            <div><?= $s ?> - <?= sanitize($ap['patient_name']) ?></div>
                            <button onclick="(async ()=>{ 
                                await updateAppointmentStatus(<?= $ap['id'] ?>,'Completed'); 
                            })()">✓</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
```

### **Features**
- ✅ Visual time-based layout
- ✅ Color-coded by status (pending=yellow, confirmed=green, etc.)
- ✅ Quick action buttons on each block
- ✅ Click to expand appointment details

---

## **Q8: What files are involved in Part 4 and their roles?**

**Ans:**

| File | Layer | Role |
|------|-------|------|
| `index.php` | Router | Route dispatch (`?page=doctor/dashboard`, `?page=api/appointments`) |
| `controllers/DoctorController.php` | Controller | `dashboard()` method — get doctor & appointment data |
| `controllers/AppointmentController.php` | Controller | `adminList()` & `updateStatus()` methods |
| `controllers/ApiController.php` | Controller | `updateAppointmentStatus()` method — REST endpoint |
| `models/AppointmentModel.php` | Model | All database queries (get, update, filter) |
| `views/doctor/dashboard.php` | View | Doctor UI — today's table + weekly grid |
| `views/admin/appointments.php` | View | Admin UI — filters + table + modal |
| `public/js/main.js` | Client-Side | `updateAppointmentStatus()` function for AJAX |
| `views/layouts/footer.php` | Client-Side | Export `BASE_URL` constant + include main.js |
| `config/app.php` | Config | Helper functions (`jsonResponse()`, `isLoggedIn()`, `requireRole()`, `sanitize()`) |
| `config/database.php` | Config | PDO database connection |
| `config/schema.sql` | Database | SQL schema for appointments table |

---

## **Q9: How does the Admin filter appointments?**

**Ans:**

### **Filter Form (GET method)**
```php
<form method="GET" class="filter-bar">
    <select name="doctor_id">...</select>  <!-- Filter by doctor -->
    <input type="date" name="date"/>      <!-- Filter by date -->
    <select name="status">...</select>    <!-- Filter by status -->
    <button type="submit">Filter</button>
</form>
```

### **URL After Filter**
```
?page=admin/appointments&doctor_id=3&date=2026-05-17&status=Pending
```

### **Controller Extract Filters**
```php
public function adminList() {
    requireRole('admin');
    $filters = [
        'doctor_id' => $_GET['doctor_id'] ?? '',
        'date'      => $_GET['date'] ?? '',
        'status'    => $_GET['status'] ?? '',
    ];
    $appointments = $this->appointmentModel->getAll($filters);
}
```

### **Model Build Dynamic Query**
```php
public function getAll($filters = []) {
    $sql = "SELECT ... FROM appointments WHERE 1=1";
    $params = [];
    
    if (!empty($filters['doctor_id'])) {
        $sql .= " AND a.doctor_id=?";
        $params[] = $filters['doctor_id'];
    }
    if (!empty($filters['date'])) {
        $sql .= " AND a.appointment_date=?";
        $params[] = $filters['date'];
    }
    if (!empty($filters['status'])) {
        $sql .= " AND a.status=?";
        $params[] = $filters['status'];
    }
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
```

### **Result**
- Query executes with filters
- Page reloads with filtered results
- Filters remain in form values

---

## **Q10: How are error responses handled in the REST endpoint?**

**Ans:**

### **Error Response 401 — Unauthorized**
```json
{
    "error": "Unauthorized"
}
```
**When:** User not logged in → `isLoggedIn()` returns false

---

### **Error Response 403 — Forbidden**
```json
{
    "error": "Forbidden"
}
```
**When:** 
- Doctor tries to update other doctor's appointment
- Doctor tries to set status not allowed (e.g., Pending)
- Patient tries to use endpoint

---

### **Error Response 400 — Bad Request**
```json
{
    "error": "Cancellation reason is required"
}
```
**When:**
- Invalid appointment_id (`$id <= 0`)
- Invalid status (not in enum)
- Admin tries to cancel without reason

---

### **Error Response 404 — Not Found**
```json
{
    "error": "Appointment not found"
}
```
**When:** Appointment with given ID doesn't exist

---

### **Success Response 200 — OK**
```json
{
    "ok": true,
    "new_status": "Completed"
}
```
**When:** All validations pass and database updated successfully

---

## **Q11: Why use AJAX instead of form submission?**

**Ans:**

### **Benefits of AJAX (updateAppointmentStatus)**

1. **No Page Reload**
   - User stays on same page
   - Context preserved (scroll position, other data)
   - Better user experience

2. **Instant Feedback**
   - Badge updates immediately
   - Status color changes live
   - No waiting for page load

3. **JSON Response**
   - Minimal data transfer
   - Easy to parse
   - Only status returned

4. **Error Handling**
   - Display error in modal/alert
   - No full page error pages
   - Better for mobile/slow connections

### **Form Submission (Legacy POST)**
```php
$method === 'POST' ? $appt->updateStatus() : $doctorController->dashboard();
```
- Still supported for backward compatibility
- Causes page reload
- Full page redirect with flash message

---

## **Q12: How is input validation performed in Part 4?**

**Ans:**

### **Layer 1: Client-Side (Browser)**
```javascript
if (!confirm('Mark Completed?')) return; // User confirmation
```

### **Layer 2: Input Sanitization (ApiController)**
```php
$id     = (int)($_GET['appointment_id'] ?? 0);    // Type cast to int
$data   = json_decode(file_get_contents('php://input'), true); // Parse JSON
$status = trim($data['status'] ?? '');            // Trim whitespace
$reason = trim($data['reason'] ?? '');            // Trim whitespace
$role   = $_SESSION['role'];                      // From session (trusted)
```

### **Layer 3: Type Validation**
```php
if ($id <= 0) 
    jsonResponse(['error' => 'Invalid appointment ID'], 400);
```

### **Layer 4: Enum Validation**
```php
$validStatuses = ['Pending','Confirmed','Completed','Cancelled','No-Show'];
if (!in_array($status, $validStatuses))
    jsonResponse(['error' => 'Invalid status'], 400);
```

### **Layer 5: Business Logic Validation**
```php
// Check appointment exists
$appointment = $this->appointmentModel->findById($id);
if (!$appointment) 
    jsonResponse(['error' => 'Appointment not found'], 404);

// Check role permissions
if ($role === 'doctor') {
    // Doctor can only set specific statuses
    // Doctor can only update own appointments
}

// Check required fields
if ($status === 'Cancelled' && empty($reason))
    jsonResponse(['error' => 'Cancellation reason is required'], 400);
```

### **Layer 6: SQL Injection Prevention (Model)**
```php
// ✅ Prepared statement (safe)
$stmt = $this->db->prepare("UPDATE appointments SET status=?, cancel_reason=? WHERE id=?");
$stmt->execute([$status, $reason, $id]);

// ❌ String concatenation (vulnerable)
// $sql = "UPDATE appointments SET status='$status' WHERE id=$id";
```

---

## **Summary Table: Part 4 MVC Components**

| Component | File | Responsibility | Key Methods |
|-----------|------|-----------------|-------------|
| **Router** | index.php | Route requests | strpos(), switch() |
| **Doctor Controller** | DoctorController.php | Load dashboard | dashboard() |
| **Admin Controller** | AppointmentController.php | Load admin panel | adminList(), updateStatus() |
| **API Controller** | ApiController.php | REST endpoint | updateAppointmentStatus() |
| **Appointment Model** | AppointmentModel.php | Database queries | getTodayByDoctor(), updateStatus(), getAll() |
| **Doctor View** | views/doctor/dashboard.php | Doctor UI | Table, weekly grid, badges |
| **Admin View** | views/admin/appointments.php | Admin UI | Filter, table, modal |
| **JavaScript** | public/js/main.js | AJAX calls | updateAppointmentStatus() |
| **Config** | config/app.php | Helpers | jsonResponse(), isLoggedIn(), requireRole() |

---

**This Q&A format provides complete MVC architecture understanding for Part 4!** ✅

