<?php
require_once 'config/database.php';
$profile_id = 1; // dummy

try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM meal_assignments ma
        JOIN meals m ON m.id = ma.meal_id
        JOIN menus mn ON mn.id = m.menu_id
        WHERE ma.elderly_profile_id = ? AND mn.meal_date = CURDATE()
    ");
    $stmt->execute([$profile_id]);
    echo "Meals query OK\n";
} catch (Exception $e) {
    echo "Meals query failed: " . $e->getMessage() . "\n";
}

try {
    $stmt = $pdo->prepare("
        SELECT a.title, a.start_time
        FROM activity_registrations ar
        JOIN activities a ON a.id = ar.activity_id
        WHERE ar.elderly_profile_id = ?
          AND ar.status = 'registered'
          AND a.activity_date = CURDATE()
          AND a.start_time >= CURTIME()
        ORDER BY a.start_time ASC
        LIMIT 1
    ");
    $stmt->execute([$profile_id]);
    echo "Activity query OK\n";
} catch (Exception $e) {
    echo "Activity query failed: " . $e->getMessage() . "\n";
}
