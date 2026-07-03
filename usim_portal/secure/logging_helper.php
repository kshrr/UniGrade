<?php
function log_security_event($pdo, $matric_no, $action, $description, $severity = 'INFO') {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    $sql = "INSERT INTO system_logs (matric_no, action, description, ip_address, severity) 
            VALUES (:matric_no, :action, :description, :ip_address, :severity)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'matric_no'   => $matric_no,
        'action'      => $action,
        'description' => $description,
        'ip_address'  => $ip_address,
        'severity'    => $severity
    ]);
}
?>