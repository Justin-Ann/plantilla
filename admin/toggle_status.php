<?php
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Fetch current status
    $stmt = $conn->prepare("SELECT active FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    // Toggle status
    $new_status = $current_status ? 0 : 1;

    $stmt = $conn->prepare("UPDATE users SET active = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "new_status" => $new_status
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update status."]);
    }

    $stmt->close();
    $conn->close();
}
?>
