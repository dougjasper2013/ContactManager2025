<?php
session_start();

$contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);

// Get form values
$first_name = filter_input(INPUT_POST, 'first_name');
$last_name = filter_input(INPUT_POST, 'last_name');
$email_address = filter_input(INPUT_POST, 'email_address');
$phone_number = filter_input(INPUT_POST, 'phone_number');
$status = filter_input(INPUT_POST, 'status');
$dob = filter_input(INPUT_POST, 'dob');
$type_id = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);

require_once('database.php');

// Check for duplicate email (excluding this contact)
$queryContacts = 'SELECT * FROM contacts';
$statement1 = $db->prepare($queryContacts);
$statement1->execute();
$contacts = $statement1->fetchAll();
$statement1->closeCursor();

foreach ($contacts as $contact) {
    if ($email_address == $contact["emailAddress"] && $contact_id != $contact["contactID"]) {
        $_SESSION["add_error"] = "Invalid data, Duplicate Email Address. Try again.";
        header("Location: error.php");
        die();
    }
}

if ($first_name == null || $last_name == null ||
    $email_address == null || $phone_number == null ||
    $dob == null || $type_id === false) {
    $_SESSION["add_error"] = "Invalid contact data, Check all fields and try again.";
    header("Location: error.php");
    die();
}

// Update the contact in the database
$query = 'UPDATE contacts
          SET firstName = :firstName,
              lastName = :lastName,
              emailAddress = :emailAddress,
              phone = :phone,
              status = :status,
              dob = :dob,
              typeID = :typeID
          WHERE contactID = :contactID';

$statement = $db->prepare($query);
$statement->bindValue(':contactID', $contact_id);
$statement->bindValue(':firstName', $first_name);
$statement->bindValue(':lastName', $last_name);
$statement->bindValue(':emailAddress', $email_address);
$statement->bindValue(':phone', $phone_number);
$statement->bindValue(':status', $status);
$statement->bindValue(':dob', $dob);
$statement->bindValue(':typeID', $type_id);
$statement->execute();
$statement->closeCursor();

$_SESSION["fullName"] = $first_name . " " . $last_name;

// Redirect to confirmation page
header("Location: update_confirmation.php");
die();
?>
