<?php
require_once BASE_PATH . '/config/database.php';

class AppointmentModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, fee_at_booking, reason)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['patient_id'], $data['doctor_id'],
            $data['appointment_date'], $data['appointment_time'],
            $data['fee_at_booking'],
            $data['reason']
        ]);
        return $this->db->lastInsertId();
    }

    public function isSlotTaken($doctorId, $date, $time) {
        $stmt = $this->db->prepare(
            "SELECT id FROM appointments
             WHERE doctor_id=? AND appointment_date=? AND appointment_time=?
             AND status NOT IN ('Cancelled','No-Show')"
        );
        $stmt->execute([$doctorId, $date, $time]);
        return $stmt->fetch() !== false;
    }

    public function findById($id) {
        $stmt = $this->db->prepare(
            "SELECT a.*, a.fee_at_booking AS consultation_fee, u.name AS patient_name, d_user.name AS doctor_name,
                    s.name AS specialization
             FROM appointments a
             JOIN users u ON a.patient_id = u.id
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users d_user ON d.user_id = d_user.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE a.id=?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByPatient($patientId) {
        $stmt = $this->db->prepare(
            "SELECT a.*, d_user.name AS doctor_name, s.name AS specialization
             FROM appointments a
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users d_user ON d.user_id = d_user.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE a.patient_id=?
             ORDER BY a.appointment_date DESC, a.appointment_time DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    public function getByDoctor($doctorId, $date = null) {
        $sql = "SELECT a.*, u.name AS patient_name
                FROM appointments a
                JOIN users u ON a.patient_id = u.id
                WHERE a.doctor_id=?";
        $params = [$doctorId];
        if ($date) {
            $sql .= " AND a.appointment_date=?";
            $params[] = $date;
        }
        $sql .= " ORDER BY a.appointment_date, a.appointment_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTodayByDoctor($doctorId) {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.name AS patient_name
             FROM appointments a
             JOIN users u ON a.patient_id = u.id
             WHERE a.doctor_id=? AND a.appointment_date = CURDATE()
             ORDER BY a.appointment_time"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    public function getUpcomingForDoctor($doctorId) {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.name AS patient_name
             FROM appointments a
             JOIN users u ON a.patient_id = u.id
             WHERE a.doctor_id=?
               AND (
                    a.appointment_date > CURDATE()
                    OR (a.appointment_date = CURDATE() AND a.appointment_time >= CURTIME())
               )
             ORDER BY a.appointment_date, a.appointment_time
             LIMIT 20"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    public function getNextAppointmentDayForDoctor($doctorId) {
        $stmt = $this->db->prepare(
            "SELECT a.appointment_date, COUNT(*) AS total
             FROM appointments a
             WHERE a.doctor_id=?
               AND a.appointment_date > CURDATE()
             GROUP BY a.appointment_date
             ORDER BY a.appointment_date ASC
             LIMIT 1"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetch();
    }

    public function getWeekByDoctor($doctorId) {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.name AS patient_name
             FROM appointments a
             JOIN users u ON a.patient_id = u.id
             WHERE a.doctor_id=?
             AND a.appointment_date >= CURDATE()
             AND a.appointment_date < DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY a.appointment_date, a.appointment_time"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    public function getAll($filters = []) {
        $sql = "SELECT a.*, u.name AS patient_name, d_user.name AS doctor_name, s.name AS specialization
                FROM appointments a
                JOIN users u ON a.patient_id = u.id
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users d_user ON d.user_id = d_user.id
                JOIN specializations s ON d.specialization_id = s.id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['doctor_id'])) {
            $sql .= " AND a.doctor_id=?"; $params[] = $filters['doctor_id'];
        }
        if (!empty($filters['date'])) {
            $sql .= " AND a.appointment_date=?"; $params[] = $filters['date'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND a.status=?"; $params[] = $filters['status'];
        }
        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status, $reason = null) {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET status=?, cancel_reason=? WHERE id=?"
        );
        return $stmt->execute([$status, $reason, $id]);
    }

    public function reschedule($id, $date, $time) {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET appointment_date=?, appointment_time=?, status='Pending' WHERE id=?"
        );
        return $stmt->execute([$date, $time, $id]);
    }

    public function cancelByPatient($id, $patientId) {
        $stmt = $this->db->prepare(
            "UPDATE appointments SET status='Cancelled'
             WHERE id=? AND patient_id=? AND status='Pending'"
        );
        $stmt->execute([$id, $patientId]);
        return $stmt->rowCount() > 0;
    }

    public function calculateTotalRevenue() {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(a.fee_at_booking), 0) AS revenue
             FROM appointments a
             WHERE a.status = 'Completed'"
        );
        $stmt->execute();
        $row = $stmt->fetch();
        return (float)($row['revenue'] ?? 0);
    }

    public function getPatientExpenseSummary($patientId) {
        $stmt = $this->db->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN a.status = 'Completed' THEN a.fee_at_booking ELSE 0 END), 0) AS total_spent,
                COALESCE(SUM(CASE WHEN a.status IN ('Pending', 'Confirmed') THEN a.fee_at_booking ELSE 0 END), 0) AS upcoming_expense,
                COALESCE(SUM(CASE WHEN a.status = 'Cancelled' THEN a.fee_at_booking ELSE 0 END), 0) AS cancelled_value,
                COALESCE(SUM(CASE WHEN a.status = 'Completed' AND a.appointment_date = CURDATE() THEN a.fee_at_booking ELSE 0 END), 0) AS today_expense
             FROM appointments a
             WHERE a.patient_id = ?"
        );
        $stmt->execute([$patientId]);
        $row = $stmt->fetch();

        return [
            'total_spent' => (float)($row['total_spent'] ?? 0),
            'upcoming_expense' => (float)($row['upcoming_expense'] ?? 0),
            'cancelled_value' => (float)($row['cancelled_value'] ?? 0),
            'today_expense' => (float)($row['today_expense'] ?? 0),
        ];
    }

    public function getDoctorIncomeSummary($doctorId) {
        $stmt = $this->db->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN status = 'Completed' THEN fee_at_booking ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN status = 'Completed' AND appointment_date = CURDATE() THEN fee_at_booking ELSE 0 END), 0) AS today_income
             FROM appointments
             WHERE doctor_id = ?"
        );
        $stmt->execute([$doctorId]);
        $row = $stmt->fetch();

        return [
            'total_income' => (float)($row['total_income'] ?? 0),
            'today_income' => (float)($row['today_income'] ?? 0),
        ];
    }

    public function getAdminRevenueSummary($fromDate = null, $toDate = null) {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN status = 'Completed' THEN fee_at_booking ELSE 0 END), 0) AS total_revenue,
                    COALESCE(SUM(CASE WHEN status = 'Completed' AND appointment_date = CURDATE() THEN fee_at_booking ELSE 0 END), 0) AS today_revenue,
                    COUNT(*) AS total_appointments
                FROM appointments
                WHERE 1=1";
        $params = [];

        if (!empty($fromDate)) {
            $sql .= " AND appointment_date >= ?";
            $params[] = $fromDate;
        }
        if (!empty($toDate)) {
            $sql .= " AND appointment_date <= ?";
            $params[] = $toDate;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return [
            'total_revenue' => (float)($row['total_revenue'] ?? 0),
            'today_revenue' => (float)($row['today_revenue'] ?? 0),
            'total_appointments' => (int)($row['total_appointments'] ?? 0),
        ];
    }

    public function getDoctorRevenueBreakdown($fromDate = null, $toDate = null) {
        $sql = "SELECT d.id, u.name,
                    COUNT(a.id) AS total_appointments,
                    COALESCE(SUM(CASE WHEN a.status = 'Completed' THEN a.fee_at_booking ELSE 0 END), 0) AS total_revenue,
                    COALESCE(SUM(CASE WHEN a.status = 'Completed' AND a.appointment_date = CURDATE() THEN a.fee_at_booking ELSE 0 END), 0) AS today_revenue
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             LEFT JOIN appointments a ON a.doctor_id = d.id";
        $params = [];

        if (!empty($fromDate)) {
            $sql .= " AND a.appointment_date >= ?";
            $params[] = $fromDate;
        }
        if (!empty($toDate)) {
            $sql .= " AND a.appointment_date <= ?";
            $params[] = $toDate;
        }

        $sql .= "
             GROUP BY d.id, u.name
             ORDER BY total_revenue DESC, u.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRevenueAppointmentRows($fromDate = null, $toDate = null) {
        $sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.fee_at_booking,
                       p.name AS patient_name, d_user.name AS doctor_name
                FROM appointments a
                JOIN users p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users d_user ON d.user_id = d_user.id
                WHERE 1=1";
        $params = [];

        if (!empty($fromDate)) {
            $sql .= " AND a.appointment_date >= ?";
            $params[] = $fromDate;
        }
        if (!empty($toDate)) {
            $sql .= " AND a.appointment_date <= ?";
            $params[] = $toDate;
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
