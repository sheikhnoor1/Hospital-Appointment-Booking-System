# Part 4: Appointment Dashboard & Status Management
## Complete MVC Architecture Document

---

## 📋 Overview

**Part 4** builds the doctor's daily schedule view, admin appointment management panel, and a shared REST endpoint that powers all status transitions. This document details the MVC architecture driving these features.

**Business Problem:**
- Doctors need to see today's appointments and manage them (mark completed, no-show)
- Admins need to manage all appointments across the hospital (confirm, cancel with reason)
- Single source of truth for appointment status (Pending → Confirmed → Completed/Cancelled/No-Show)

**Solution:**
- Shared REST endpoint: `PUT /api/appointments/{id}`
- Role-based permission checks (doctor = own appts only; admin = all appts)
- AJAX-driven UI with live badge updates (no page reload)
- Weekly grid visualization (Mon-Fri, time slots)

---

## 🏗️ MVC Architecture Breakdown

### **Layer 1: Frontend (Views & Presentation)**

#### **A. Doctor Dashboard** (`views/doctor/dashboard.php`)

**Responsibility:** Display doctor's daily schedule and appointment management interface

**Components:**

1. **Today's Appointments Table**
   - Columns: Time | Patient Name | Reason | Fee | Status Badge | Actions
   - Shows only future appointments (from current time onwards)
   - Fallback: If no today's appointments, show next upcoming day
   - Status badges with color-coded styles:
     - `badge-pending` (yellow)
     - `badge-confirmed` (green)
     - `badge-completed` (blue)
     - `badge-cancelled` (red)
     - `badge-no-show` (gray)

2. **Action Buttons**
   - "Mark Completed" → `updateAppointmentStatus(id, 'Completed')`
   - "Mark No-Show" → `updateAppointmentStatus(id, 'No-Show')`
   - Confirmation dialog before action
   - Disabled for already-completed appointments

3. **Weekly Schedule Grid** (Mon-Fri)
   - **Structure:** 5 columns (Monday–Friday) × time slots (rows)
   - **Time Slots:** 09:00, 09:30, 10:00, ... 17:00 (30-min intervals)
   - **Cells:** Show booked appointments as colored blocks
   - **Block Info:** Time + Patient Name + Status Badge
   - **Interactivity:** Click block to show details, quick action buttons

4. **Stats Cards**
   - Today's Appointments (count)
   - This Week (count)
   - Confirmed Today (count)
   - Total Income (sum of completed appointments)
   - Today's Income (sum of completed today)

**Data Passed from Controller:**
```php
$doctor              // Current logged-in doctor's record
$todayAppointments   // getTodayByDoctor($doctor['id'])
$displayAppointments // Filtered future appointments
$displayLabel        // "Today" or next date
$weekAppointments    // getWeekByDoctor($doctor['id'])
$incomeSummary       // getDoctorIncomeSummary($doctor['id'])
$statusColors        // Map: status → CSS class
```

---

#### **B. Admin Appointments List** (`views/admin/appointments.php`)

**Responsibility:** Display all appointments with filtering and management actions

**Components:**

1. **Filter Bar** (form with GET params)
   - **Doctor Dropdown:** Filter by doctor_id (reloads page with `?doctor_id=N`)
   - **Date Picker:** Filter by appointment_date (reloads with `?date=YYYY-MM-DD`)
   - **Status Dropdown:** Filter by status (reloads with `?status=Pending|Confirmed|...`)
   - **Submit / Reset Buttons**
   - Filters applied via `AppointmentModel::getAll($filters)`

2. **Appointments Table**
   - Columns: ID | Patient | Doctor | Specialization | Date & Time | Reason | Status | Actions
   - Sortable/filterable via GET params
   - Row count displayed: "X records"

3. **Action Buttons per Row**
   - **If Status = Pending:**
     - "Confirm" → `updateAppointmentStatus(id, 'Confirmed')`
   - **If Status ≠ Cancelled/Completed:**
     - "Cancel" → Opens cancel modal

4. **Cancel Modal Dialog**
   - Overlay with form
   - Input: "Cancellation Reason" (required textarea)
   - Buttons: "Cancel Appointment" | "Back"
   - Submit calls `updateAppointmentStatus(id, 'Cancelled', reason)`

**Data Passed from Controller:**
```php
$appointments  // getAll($filters) with all appts + joins
$doctors       // getAllAdmin() - for dropdown
$filters       // Current GET params: doctor_id, date, status
$success       // Flash message if action succeeded
$error         // Flash message if action failed
```

---

### **Layer 2: Client-Side JavaScript (AJAX & Interactivity)**

#### **C. `public/js/main.js` — `updateAppointmentStatus()` Function**

**Responsibility:** Handle all AJAX calls to the status endpoint, update UI in real-time

**Function Signature:**
```javascript
async function updateAppointmentStatus(id, status, reason = '') {
  // 1. Build URL: /index.php?page=api/appointments&appointment_id=id
  // 2. Fetch PUT with JSON: {status, reason}
  // 3. Parse response: {ok: true, new_status: "Completed"}
  // 4. Update badge elements: #doc-badge-{id}, #appt-badge-{id}
  // 5. Return {ok, error} object
}
```

**Flow:**
1. **Build Request:**
   - URL: `${BASE_URL}/index.php?page=api/appointments&appointment_id=${id}`
   - Method: `PUT`
   - Headers: `Content-Type: application/json`
   - Credentials: `same-origin` (include session cookies)
   - Body: `{status: "Completed", reason: "" or "reason text"}`

2. **Handle Response:**
   - Check `resp.ok` (HTTP status 200)
   - Parse JSON: `{ok, new_status}` or `{ok, error}`
   - If error: log & alert user

3. **Update DOM:**
   - Find badge: `document.querySelector('#doc-badge-' + id)`
   - Update text: `el.textContent = newStatus`
   - Update class: `el.className = 'badge badge-' + statusToClass(newStatus)`
   - **Result:** Badge color changes instantly, no page reload

**Error Handling:**
```javascript
if (!resp.ok) {
  console.error('updateAppointmentStatus error', err);
  return { ok: false, error: err.message };
}
```

---

#### **D. Global JavaScript Setup**

**In `views/layouts/footer.php`:**
```html
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/public/js/main.js"></script>
```

- Exposes `BASE_URL` globally for AJAX calls
- Loads `main.js` with `updateAppointmentStatus()` function

---

### **Layer 3: Router (Request Routing)**

#### **E. `index.php` — API Route Handler**

**Responsibility:** Route API requests to the correct controller method

**Route Definition:**
```php
// API routes (JSON endpoints)
if (strpos($page, 'api/') === 0) {
    require_once BASE_PATH . '/controllers/ApiController.php';
    $api = new ApiController();

    switch ($page) {
        case 'api/appointments':
            if ($method === 'PUT') $api->updateAppointmentStatus();
            elseif ($method === 'POST') $api->cancelAppointment();
            else jsonResponse(['error' => 'Method not allowed'], 405);
            break;
        // ... other API routes
    }
    exit;
}
```

**Request Flow:**
1. Browser sends: `PUT /index.php?page=api/appointments&appointment_id=5`
2. Router extracts: `$page = 'api/appointments'`, `$method = 'PUT'`
3. Routes to: `ApiController::updateAppointmentStatus()`
4. Returns: JSON response

---

### **Layer 4: Controller (Business Logic)**

#### **F. `controllers/ApiController.php` — `updateAppointmentStatus()` Method**

**Responsibility:** Validate permissions, sanitize input, call model, return JSON

**Method Implementation:**

```php
public function updateAppointmentStatus() {
    // Step 1: AUTHENTICATION CHECK
    if (!isLoggedIn()) 
        jsonResponse(['error' => 'Unauthorized'], 401);

    // Step 2: EXTRACT & VALIDATE INPUT
    $id     = (int)($_GET['appointment_id'] ?? 0);
    $data   = json_decode(file_get_contents('php://input'), true) ?? [];
    $status = trim($data['status'] ?? '');
    $reason = trim($data['reason'] ?? '');
    $role   = $_SESSION['role'];

    if ($id <= 0) 
        jsonResponse(['error' => 'Invalid appointment ID'], 400);

    // Step 3: VALIDATE STATUS
    $validStatuses = ['Pending','Confirmed','Completed','Cancelled','No-Show'];
    if (!in_array($status, $validStatuses))
        jsonResponse(['error' => 'Invalid status'], 400);

    // Step 4: FETCH APPOINTMENT
    $appointment = $this->appointmentModel->findById($id);
    if (!$appointment) 
        jsonResponse(['error' => 'Appointment not found'], 404);

    // Step 5: ROLE-BASED PERMISSION CHECK
    if ($role === 'doctor') {
        $doctor = (new DoctorModel())->findByUserId($_SESSION['user_id']);
        if (!$doctor || $doctor['id'] != $appointment['doctor_id'])
            jsonResponse(['error' => 'Forbidden'], 403);
        
        // Doctors can ONLY set Completed or No-Show
        if (!in_array($status, ['Completed','No-Show']))
            jsonResponse(['error' => 'Doctors can only set Completed or No-Show'], 403);
    } 
    elseif ($role === 'admin') {
        // Admins can set any status
        // But Cancelled requires reason
        if ($status === 'Cancelled' && empty($reason))
            jsonResponse(['error' => 'Cancellation reason is required'], 400);
    } 
    else {
        jsonResponse(['error' => 'Forbidden'], 403);
    }

    // Step 6: UPDATE APPOINTMENT IN DATABASE
    $this->appointmentModel->updateStatus($id, $status, $reason ?: null);

    // Step 7: RETURN JSON RESPONSE
    jsonResponse(['ok' => true, 'new_status' => $status]);
}
```

**Permission Matrix:**

| Role   | Can Update Own? | Allowed Statuses                  | Notes                              |
|--------|-----------------|-----------------------------------|--------------------------------------|
| Doctor | Own appts only  | Completed, No-Show                | Doctor must own the appointment     |
| Admin  | All appts       | Any (Pending, Confirmed, etc.)    | Reason required for Cancelled       |
| Patient| No              | —                                 | Patients can only cancel via different endpoint |

---

#### **G. `controllers/AppointmentController.php` — `updateStatus()` (Server-side Form Handler)**

**Responsibility:** Handle POST requests from form submissions (legacy fallback)

**Used for:** Doctor/Admin submitting forms (before we added AJAX)
**Now replaced by:** REST endpoint via AJAX

```php
public function updateStatus() {
    // Legacy form-based update
    // Kept for backward compatibility
    // Redirects with flash message
}
```

---

### **Layer 5: Model (Data Access)**

#### **H. `models/AppointmentModel.php` — Data Persistence**

**Responsibility:** Database queries for appointments

**Key Methods:**

1. **`findById($id)`** → Fetch single appointment with joins
   ```php
   SELECT a.*, a.fee_at_booking, u.name AS patient_name, 
          d_user.name AS doctor_name, s.name AS specialization
   FROM appointments a
   JOIN users u ON a.patient_id = u.id
   JOIN doctors d ON a.doctor_id = d.id
   JOIN users d_user ON d.user_id = d_user.id
   JOIN specializations s ON d.specialization_id = s.id
   WHERE a.id = ?
   ```

2. **`getTodayByDoctor($doctorId)`** → Today's appointments
   ```php
   SELECT a.*, u.name AS patient_name
   FROM appointments a
   JOIN users u ON a.patient_id = u.id
   WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
   ORDER BY a.appointment_time
   ```

3. **`getWeekByDoctor($doctorId)`** → This week's appointments
   ```php
   SELECT a.*, u.name AS patient_name
   FROM appointments a
   JOIN users u ON a.patient_id = u.id
   WHERE a.doctor_id = ?
     AND a.appointment_date >= CURDATE()
     AND a.appointment_date < DATE_ADD(CURDATE(), INTERVAL 7 DAY)
   ORDER BY a.appointment_date, a.appointment_time
   ```

4. **`getAll($filters)`** → All appointments (admin list)
   ```php
   SELECT a.*, u.name AS patient_name, d_user.name AS doctor_name, 
          s.name AS specialization
   FROM appointments a
   JOIN users u ON a.patient_id = u.id
   JOIN doctors d ON a.doctor_id = d.id
   JOIN users d_user ON d.user_id = d_user.id
   JOIN specializations s ON d.specialization_id = s.id
   WHERE 1=1
     [AND a.doctor_id = ? if filter set]
     [AND a.appointment_date = ? if filter set]
     [AND a.status = ? if filter set]
   ORDER BY a.appointment_date DESC, a.appointment_time DESC
   ```

5. **`updateStatus($id, $status, $reason = null)`** → Core update
   ```php
   UPDATE appointments 
   SET status = ?, cancel_reason = ? 
   WHERE id = ?
   
   // status transitions:
   // Pending → Confirmed (admin)
   // Pending → Cancelled (admin with reason)
   // Confirmed/Pending → Completed (doctor)
   // Any → No-Show (doctor)
   ```

6. **`getDoctorIncomeSummary($doctorId)`** → For dashboard stats
   ```php
   SELECT 
     SUM(CASE WHEN status = 'Completed' THEN fee_at_booking ELSE 0 END) AS total_income,
     SUM(CASE WHEN status = 'Completed' AND appointment_date = CURDATE() 
         THEN fee_at_booking ELSE 0 END) AS today_income
   FROM appointments
   WHERE doctor_id = ?
   ```

---

### **Layer 6: Database (Data Storage)**

#### **I. MySQL Schema — `appointments` Table**

```sql
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    fee_at_booking DECIMAL(10, 2),
    status ENUM('Pending','Confirmed','Completed','Cancelled','No-Show') DEFAULT 'Pending',
    cancel_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX (doctor_id),
    INDEX (patient_id),
    INDEX (appointment_date),
    INDEX (status)
);
```

**Key Fields:**
- `status`: 5 states controlling workflow
- `cancel_reason`: Admin's reason for cancellation
- `fee_at_booking`: Captured at time of booking (immutable)
- Foreign keys ensure referential integrity

---

## 🔄 Complete Request-Response Flow

### **Scenario: Doctor marks appointment as "Completed"**

```
1. USER ACTION (Browser)
   └─ Doctor clicks "Mark Completed" button on dashboard

2. JAVASCRIPT HANDLER (main.js)
   ├─ confirm('Mark this appointment as Completed?')
   ├─ await updateAppointmentStatus(5, 'Completed')
   └─ Build: PUT /api/appointments?appointment_id=5
             {"status":"Completed","reason":""}

3. ROUTER (index.php)
   ├─ Parse: page='api/appointments', method='PUT'
   ├─ Route to: ApiController::updateAppointmentStatus()
   └─ Continue processing

4. CONTROLLER - Authentication (ApiController)
   ├─ Check: isLoggedIn() ✓
   ├─ Check: $_SESSION['role'] = 'doctor' ✓
   └─ Continue

5. CONTROLLER - Input Validation (ApiController)
   ├─ Extract: appointment_id = 5
   ├─ Extract: status = 'Completed'
   ├─ Validate: in_array('Completed', validStatuses) ✓
   └─ Continue

6. CONTROLLER - Fetch Data (ApiController)
   ├─ Call: $appointmentModel->findById(5)
   └─ Result: {id:5, doctor_id:3, patient_id:2, ...}

7. CONTROLLER - Permission Check (ApiController)
   ├─ Doctor must own: doctor_id in appointment = 3
   ├─ Current doctor: findByUserId($_SESSION['user_id']) = 3 ✓
   ├─ Status allowed: in_array('Completed', ['Completed','No-Show']) ✓
   └─ Continue

8. MODEL - Database Update (AppointmentModel)
   ├─ Execute: UPDATE appointments 
   │           SET status='Completed', cancel_reason=NULL
   │           WHERE id=5
   ├─ Result: 1 row affected
   └─ Return: true

9. CONTROLLER - Response (ApiController)
   └─ jsonResponse(['ok' => true, 'new_status' => 'Completed'])

10. JAVASCRIPT - Parse Response (main.js)
    ├─ Check: resp.ok = 200 ✓
    ├─ Parse: json = {ok: true, new_status: 'Completed'}
    └─ Continue

11. JAVASCRIPT - Update DOM (main.js)
    ├─ Find: document.querySelector('#doc-badge-5')
    ├─ Update text: el.textContent = 'Completed'
    ├─ Update class: el.className = 'badge badge-completed'
    └─ Result: Badge color changes from yellow to blue

12. USER FEEDBACK (Browser)
    └─ ✅ Appointment status changed to "Completed" 
       (badge now shows blue, no page reload)
```

---

## 📁 File Structure for Part 4

```
hospital/
├── index.php                              # Router (api/appointments route)
├── config/
│   └── app.php                            # Helper functions (jsonResponse, etc.)
├── controllers/
│   ├── ApiController.php                  # REST endpoint handler
│   ├── AppointmentController.php          # Legacy form handler
│   └── DoctorController.php               # Dashboard controller
├── models/
│   └── AppointmentModel.php               # Data access layer
├── views/
│   ├── doctor/
│   │   └── dashboard.php                  # Doctor UI (today's list + weekly grid)
│   ├── admin/
│   │   └── appointments.php               # Admin UI (list + filters + modal)
│   └── layouts/
│       └── footer.php                     # BASE_URL global + main.js
├── public/
│   ├── css/
│   │   └── style.css                      # Grid styles, badge colors
│   └── js/
│       └── main.js                        # updateAppointmentStatus() function
└── PART_4_MVC_ARCHITECTURE.md             # This document
```

---

## 🔐 Security & Validation

### **Authentication & Authorization**

| Layer | Check | Implementation |
|-------|-------|-----------------|
| **Router** | Is logged in? | `isLoggedIn()` in ApiController |
| **Controller** | Is correct role? | Doctor/Admin only |
| **Controller** | Own appointment? | Doctor must own (doctor_id match) |
| **Controller** | Valid status? | In allowed list for role |
| **Database** | Data integrity | Foreign keys, NOT NULL constraints |

### **Input Sanitization**

```php
$status = trim($data['status'] ?? '');              // Trim whitespace
$reason = trim($data['reason'] ?? '');              // Trim whitespace
// Validated against enum: ['Pending','Confirmed',...]
// Prepared statements prevent SQL injection
```

---

## ✨ Key Features Implemented

### **1. Today's Appointments Table**
- ✅ Filters out past appointments
- ✅ Shows Time, Patient, Reason, Fee, Status Badge
- ✅ Quick action buttons: Mark Completed, Mark No-Show
- ✅ Disables actions for completed/cancelled appointments

### **2. Weekly Grid (Mon-Fri)**
- ✅ 5 columns (Monday → Friday)
- ✅ Time slots (09:00 → 17:00, 30-min intervals)
- ✅ Colored appointment blocks by status
- ✅ Click to expand, action buttons on block

### **3. Admin Appointment List**
- ✅ Dropdown filter by doctor
- ✅ Date picker filter
- ✅ Status dropdown filter
- ✅ Filters applied via GET params (page reload)
- ✅ Actions: Confirm (if Pending), Cancel (with modal reason)

### **4. Shared REST Endpoint**
- ✅ Single `PUT /api/appointments/{id}` for all status updates
- ✅ Role-based permission validation
- ✅ JSON request/response
- ✅ Returns `{ok: true, new_status: "Completed"}`

### **5. AJAX UI Updates**
- ✅ No page reload after status change
- ✅ Badge updates in real-time
- ✅ Color changes reflect new status
- ✅ Works on doctor dashboard, admin list, weekly grid

---

## 🧪 Testing Checklist

### **Doctor Workflow**
- [ ] Login as doctor
- [ ] View today's appointments on dashboard
- [ ] Click "Mark Completed" on an appointment
- [ ] Confirm modal appears
- [ ] Badge updates to blue (no reload)
- [ ] Status shows as "Completed" in database

### **Weekly Grid**
- [ ] Grid displays Mon-Fri for current week
- [ ] Appointments show as colored blocks
- [ ] Click block shows details
- [ ] Action buttons on block work (AJAX)

### **Admin Workflow**
- [ ] Login as admin
- [ ] Navigate to Appointments page
- [ ] Filter by doctor (dropdown reload)
- [ ] Filter by date (date picker reload)
- [ ] Filter by status (dropdown reload)
- [ ] Click "Confirm" on Pending appointment
- [ ] Confirm button changes, no reload
- [ ] Click "Cancel" on any appointment
- [ ] Modal appears for cancellation reason
- [ ] Submit cancels and updates UI

### **Permission & Security**
- [ ] Doctor cannot mark Non-Owned appointment
- [ ] Doctor cannot set status other than Completed/No-Show
- [ ] Admin can set any status
- [ ] Admin must provide reason to cancel
- [ ] Patient role cannot access API

---

## 🎯 API Contract

### **Request**
```http
PUT /index.php?page=api/appointments&appointment_id=5 HTTP/1.1
Content-Type: application/json

{
  "status": "Completed",
  "reason": ""
}
```

### **Success Response (200)**
```json
{
  "ok": true,
  "new_status": "Completed"
}
```

### **Error Responses**
```json
// 401 Unauthorized
{"error": "Unauthorized"}

// 400 Bad Request
{"error": "Cancellation reason is required"}

// 403 Forbidden
{"error": "Doctors can only set Completed or No-Show"}

// 404 Not Found
{"error": "Appointment not found"}
```

---

## 📊 Status Workflow Diagram

```
┌──────────┐
│ Pending  │ (Initial state after booking)
└────┬─────┘
     │
     ├──→ [Admin: Confirm] ──→ ┌───────────┐
     │                          │ Confirmed │
     │                          └─────┬─────┘
     │                                │
     │    [Doctor: Mark Completed] ◄──┘
     │    [Doctor: Mark No-Show]
     │
     ├──→ [Doctor: Mark Completed] ──→ ┌───────────┐
     │                                  │ Completed │ (Final)
     │                                  └───────────┘
     │
     ├──→ [Doctor: Mark No-Show] ──→ ┌─────────┐
     │                                │ No-Show │ (Final)
     │                                └─────────┘
     │
     └──→ [Admin: Cancel + Reason] ──→ ┌──────────┐
                                        │Cancelled │ (Final)
                                        └──────────┘
```

---

## 🚀 Performance Notes

- **Database Indices:** doctor_id, appointment_date, status for fast filtering
- **Prepared Statements:** Prevent SQL injection, enable query caching
- **JSON Response:** Minimal payload (only status + ok flag)
- **AJAX:** No page reload = instant perceived performance
- **Weekly Grid:** JavaScript-based rendering, no server loops

---

## 📝 Conclusion

Part 4 implements a clean MVC separation with:
- **Model:** Data access (AppointmentModel)
- **View:** HTML/CSS templates (doctor dashboard, admin list)
- **Controller:** Business logic + validation (ApiController)
- **Router:** Request dispatching (index.php)
- **Client:** AJAX handler (updateAppointmentStatus in main.js)

The REST endpoint serves as the single source of truth for all appointment status transitions, ensuring data consistency and enabling both server-side validation and client-side interactivity.

