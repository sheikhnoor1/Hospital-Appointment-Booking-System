<?php
require_once BASE_PATH . '/config/database.php';

class DoctorModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    public function getAll($specializationId = null) {
        $sql = "SELECT d.*, u.name, u.email, u.is_active, s.name AS specialization_name
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                JOIN specializations s ON d.specialization_id = s.id
                WHERE u.is_active = 1";
        $params = [];
        if ($specializationId) {
            $sql .= " AND d.specialization_id = ?";
            $params[] = $specializationId;
        }
        $sql .= " ORDER BY u.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAllAdmin() {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.name, u.email, u.is_active, s.name AS specialization_name,
                    COUNT(a.id) AS total_appointments
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             JOIN specializations s ON d.specialization_id = s.id
             LEFT JOIN appointments a ON a.doctor_id = d.id
             GROUP BY d.id ORDER BY u.name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.name, u.email, u.is_active, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             JOIN specializations s ON d.specialization_id = s.id
             WHERE d.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM doctors WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, photo_path, available_days)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['user_id'], $data['specialization_id'],
            $data['bio'], $data['consultation_fee'],
            $data['photo_path'] ?? null, $data['available_days']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE doctors SET specialization_id=?, bio=?, consultation_fee=?, available_days=?
             WHERE id=?"
        );
        return $stmt->execute([
            $data['specialization_id'], $data['bio'],
            $data['consultation_fee'], $data['available_days'], $id
        ]);
    }

    public function updatePhoto($id, $photoPath) {
        $stmt = $this->db->prepare("UPDATE doctors SET photo_path=? WHERE id=?");
        return $stmt->execute([$photoPath, $id]);
    }

    public function updateSchedule($id, $availableDays) {
        $stmt = $this->db->prepare("UPDATE doctors SET available_days=? WHERE id=?");
        return $stmt->execute([$availableDays, $id]);
    }

    public function getStats() {
        $stmt = $this->db->prepare(
            "SELECT d.id, u.name, COUNT(a.id) AS total
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             LEFT JOIN appointments a ON a.doctor_id = d.id
             GROUP BY d.id"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBookedSlots($doctorId, $date) {
        $stmt = $this->db->prepare(
            "SELECT appointment_time FROM appointments
             WHERE doctor_id=? AND appointment_date=? AND status NOT IN ('Cancelled','No-Show')"
        );
        $stmt->execute([$doctorId, $date]);
        return array_column($stmt->fetchAll(), 'appointment_time');
    }

    public function getAvailableSlots($doctorId, $date) {
        $doctor = $this->findById($doctorId);
        if (!$doctor) {
            return [];
        }

        $dayName   = date('l', strtotime($date));
        $availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
        if (!in_array($dayName, $availDays, true)) {
            return [];
        }

        $allSlots    = timeSlots();
        $bookedSlots = $this->getBookedSlots($doctorId, $date);

        // Exclude slots already booked and (if date is today) slots within the next 15 minutes
        $thresholdTs = null;
        if ($date === date('Y-m-d')) {
            $thresholdTs = time() + 15 * 60; // now + 15 minutes
        }

        return array_values(array_filter(
            $allSlots,
            function($slot) use ($bookedSlots, $thresholdTs, $date) {
                // bookedSlots may contain HH:MM:SS or HH:MM
                $isBooked = in_array($slot . ':00', $bookedSlots, true) || in_array($slot, $bookedSlots, true);
                if ($isBooked) return false;
                if ($thresholdTs !== null) {
                    $ts = strtotime($date . ' ' . $slot);
                    if ($ts < $thresholdTs) return false;
                }
                return true;
            }
        ));
    }
}
