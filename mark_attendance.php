<?php
// mark_attendance.php
session_start();

// Include database connection
include 'db_connect.php';

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize inputs
    $registration_no = mysqli_real_escape_string($conn, trim($_POST['registration_no']));
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $time = mysqli_real_escape_string($conn, trim($_POST['time']));
    $date = date('Y-m-d');
    $action = $_POST['action']; // 'in' or 'out'

    // Validate inputs
    if (empty($registration_no) || empty($name) || empty($time) || empty($action)) {
        echo "<script>
              alert('All fields are required.');
              window.location.href = 'time.php';
            </script>";
        exit;
    }

    // Check if attendance record exists
    $stmt = $conn->prepare("SELECT * FROM siwes_staff_attendance WHERE registration_no = ? AND date = ?");
    $stmt->bind_param("ss", $registration_no, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = $result->fetch_assoc();

    if ($attendance) {
        if ($action === 'in' && empty($attendance['time_in'])) {
            // Mark Time In
            $stmt_update = $conn->prepare("UPDATE siwes_staff_attendance SET time_in = ? WHERE id = ?");
            $stmt_update->bind_param("si", $time, $attendance['id']);
            if ($stmt_update->execute()) {
                echo "<script>
                      alert('Time In marked successfully.');
                      window.location.href = 'index.php';
                    </script>";
            } else {
                echo "Error updating record: " . $stmt_update->error;
            }
            $stmt_update->close();
        } elseif ($action === 'out' && !empty($attendance['time_in']) && empty($attendance['time_out'])) {
            // Mark Time Out
            $stmt_update = $conn->prepare("UPDATE siwes_staff_attendance SET time_out = ? WHERE id = ?");
            $stmt_update->bind_param("si", $time, $attendance['id']);
            if ($stmt_update->execute()) {
                echo "<script>
                      alert('Time Out marked successfully.');
                      window.location.href = 'index.php';
                    </script>";
            } else {
                echo "Error updating record: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            // Time In already marked or no Time In to mark Time Out
            echo "<script>
                  alert('Either Time In has already been marked or no active record for Time Out.');
                  window.location.href = 'index.php';
                </script>";
        }
    } else {
        // Insert new attendance record with Time In (if it's not already set)
        if ($action === 'in') {
            $stmt_insert = $conn->prepare("INSERT INTO siwes_staff_attendance (registration_no, name, date, status, time_in) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $staff_id, $name, $date, $time);
            if ($stmt_insert->execute()) {
                echo "<script>
                      alert('Time In marked successfully.');
                      window.location.href = 'index.php';
                    </script>";
            } else {
                echo "Error inserting record: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            echo "<script>
                  alert('No active attendance record found for this Staff ID today.');
                  window.location.href = 'index.php';
                </script>";
        }
    }

    $stmt->close();
    $conn->close();
} else {
    // If accessed without POST, redirect to index.php
    header("Location: index.php");
    exit;
}