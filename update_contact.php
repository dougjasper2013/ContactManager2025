<?php
session_start();

require_once('database.php');
require_once('image_util.php');

// Get contact ID
$contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);

// Get other form data
$first_name = filter_input(INPUT_POST, 'first_name');
$last_name = filter_input(INPUT_POST, 'last_name');
$email_address = filter_input(INPUT_POST, 'email_address');
$phone_number = filter_input(INPUT_POST, 'phone_number');
$status = filter_input(INPUT_POST, 'status');
$dob = filter_input(INPUT_POST, 'dob');
$type_id = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);

// Get uploaded image (if any)
$image = $_FILES['image'];

// Get current contact record to check current image name
$query = 'SELECT * FROM contacts WHERE contactID = :contactID';
$statement = $db->prepare($query);
$statement->bindValue(':contactID', $contact_id);
$statement->execute();
$contact = $statement->fetch();
$statement->closeCursor();

$old_image_name = $contact['imageName'];
$base_dir = 'images/';
$image_name = $old_image_name;

// Check for duplicate email in other contacts
$queryContacts = 'SELECT * FROM contacts';
$statement1 = $db->prepare($queryContacts);
$statement1->execute();
$contacts = $statement1->fetchAll();
$statement1->closeCursor();

foreach ($contacts as $c) {
    if ($email_address == $c["emailAddress"] && $contact_id != $c["contactID"]) {
        $_SESSION["add_error"] = "Invalid data, Duplicate Email Address. Try again.";
        header("Location: error.php");
        die();
    }
}

// Validate input
if ($first_name == null || $last_name == null || $email_address == null ||
    $phone_number == null || $dob == null || $type_id == null) {
    $_SESSION["add_error"] = "Invalid contact data. Check all fields and try again.";
    header("Location: error.php");
    die();
}

// If new image is uploaded
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $original_filename = basename($image['name']);
    $upload_path = $base_dir . $original_filename;

    move_uploaded_file($image['tmp_name'], $upload_path);

    // Process and create _100 and _400 versions
    process_image($base_dir, $original_filename);

    // Create new image name with _100
    $dot_pos = strrpos($original_filename, '.');
    $new_image_name = substr($original_filename, 0, $dot_pos) . '_100' . substr($original_filename, $dot_pos);
    $image_name = $new_image_name;

    // Delete old images if they are not the placeholder
    if ($old_image_name !== 'placeholder_100.jpg') {
        $old_base = substr($old_image_name, 0, strrpos($old_image_name, '_100'));
        $old_ext = substr($old_image_name, strrpos($old_image_name, '.'));
        $original = $old_base . $old_ext;
        $img100 = $old_base . '_100' . $old_ext;
        $img400 = $old_base . '_400' . $old_ext;

        foreach ([$original, $img100, $img400] as $file) {
            $path = $base_dir . $file;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}

// Update contact in database
$update_query = '
    UPDATE contacts
    SET firstName = :firstName,
        lastName = :lastName,
        emailAddress = :emailAddress,
        phone = :phone,
        status = :status,
        dob = :dob,
        typeID = :typeID,
        imageName = :imageName
    WHERE contactID = :contactID';

$statement = $db->prepare($update_query);
$statement->bindValue(':firstName', $first_name);
$statement->bindValue(':lastName', $last_name);
$statement->bindValue(':emailAddress', $email_address);
$statement->bindValue(':phone', $phone_number);
$statement->bindValue(':status', $status);
$statement->bindValue(':dob', $dob);
$statement->bindValue(':typeID', $type_id);
$statement->bindValue(':imageName', $image_name);
$statement->bindValue(':contactID', $contact_id);
$statement->execute();
$statement->closeCursor();

// Store full name for confirmation message
$_SESSION["fullName"] = $first_name . " " . $last_name;

// Redirect to confirmation
header("Location: update_confirmation.php");
die();
?>
