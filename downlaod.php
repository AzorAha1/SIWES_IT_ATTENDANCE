<?php
// export_csv.php
include 'db_connect.php';

// Fetch attendance data
$sql = "SELECT registration_no, name, date, time_in, time_out FROM siwes_staff_attendance ORDER BY date DESC, time_in DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set headers to download file rather than display
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=siwes_attendance_records.csv');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, array('Registration No', 'Name', 'Date', 'Time In', 'Time Out'));

    // Output the data
    while($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
} else {
    echo "No attendance records found.";
}

$conn->close();
?>
