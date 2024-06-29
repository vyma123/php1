<?php
include 'db.php';
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// check if property_id exists and is valid
function isValidPropertyId($property_id, $type, $conn)
{
    $property_id = (int) $property_id;
    // security
    $type = $conn->real_escape_string($type);
    $check_sql = "SELECT COUNT(*) as count FROM property WHERE id = $property_id AND type_ = '$type'";
    $result = $conn->query($check_sql);
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}


?>