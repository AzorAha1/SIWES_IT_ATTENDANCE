<?php
// submit_attendance.php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registration_no = mysqli_real_escape_string($conn, trim($_POST['registration_no']));
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));

    if (empty($registration_no) || empty($name)) {
        echo "<script>alert('All fields are required.');window.location.href='index.php';</script>";
        exit;
    }

    $current_time = date('H:i:s');
    $current_hour = (int)date('H');
    $current_date = date('Y-m-d');

    // Handle day shift logic
    // Look for existing record for today
    $stmt = $conn->prepare(
        "SELECT id, time_in, time_out 
        FROM siwes_staff_attendance 
        WHERE registration_no = ? 
        AND date = ?"
    );
    $stmt->bind_param("ss", $registration_no, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();

    if ($record) {
        if ($record['time_in'] && !$record['time_out']) {
            $update_stmt = $conn->prepare(
                "UPDATE siwes_staff_attendance 
                SET time_out = ? 
                WHERE id = ?"
            );
            $update_stmt->bind_param("si", $current_time, $record['id']);
            $update_stmt->execute();
            echo "<script>alert('Time Out marked successfully.');window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Attendance for today already completed.');window.location.href='index.php';</script>";
        }
    } else {
        // Only allow sign-in during day hours (8 AM to 8 PM)
        if ($current_hour >= 8 && $current_hour < 20) {
            $insert_stmt = $conn->prepare(
                "INSERT INTO siwes_staff_attendance (registration_no, name, date, time_in) 
                VALUES (?, ?, ?, ?)"
            );
            $insert_stmt->bind_param("ssss", $registration_no, $name, $current_date, $current_time);
            $insert_stmt->execute();
            echo "<script>alert('Time In marked successfully.');window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Sign-in is only allowed between 8 AM and 8 PM.');window.location.href='index.php';</script>";
        }
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>