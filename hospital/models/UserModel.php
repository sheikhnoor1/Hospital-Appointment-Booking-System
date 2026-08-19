<?php
require_once BASE_PATH . '/config/database.php';

class UserModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password_hash, role, dob, blood_group, phone)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['name'], $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'patient',
            $data['dob'] ?? null,
            $data['blood_group'] ?? null,
            $data['phone'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE users SET name=?, phone=?, dob=? WHERE id=?"
        );
        return $stmt->execute([$data['name'], $data['phone'], $data['dob'], $id]);
    }

    public function updateByAdmin($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE users SET name=?, email=?, phone=? WHERE id=?"
        );
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $id,
        ]);
    }

    public function updatePassword($id, $newPassword) {
        $stmt = $this->db->prepare("UPDATE users SET password_hash=? WHERE id=?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    public function toggleActive($id) {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=?");
        $stmt->execute([$id]);
        $user = $this->findById($id);
        return $user['is_active'];
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countUpcomingAppointments($patientId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as cnt FROM appointments
             WHERE patient_id=? AND status IN ('Pending','Confirmed') AND appointment_date >= CURDATE()"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetch()['cnt'];
    }
}
