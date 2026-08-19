<?php
require_once BASE_PATH . '/config/database.php';

class SpecializationModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM specializations ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM specializations WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name) {
        $stmt = $this->db->prepare("INSERT INTO specializations (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name) {
        $stmt = $this->db->prepare("UPDATE specializations SET name=? WHERE id=?");
        return $stmt->execute([$name, $id]);
    }

    public function hasDoctors($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM doctors WHERE specialization_id=?");
        $stmt->execute([$id]);
        return $stmt->fetch()['cnt'] > 0;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM specializations WHERE id=?");
        return $stmt->execute([$id]);
    }
}
