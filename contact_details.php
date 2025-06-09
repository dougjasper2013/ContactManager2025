<?php
session_start();
require_once("database.php");

// Get contact ID from GET
$contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);
if (!$contact_id) {
    header("Location: index.php");
    exit;
}

// Fetch contact info
$query = 'SELECT c.*, t.contactType FROM contacts c LEFT JOIN types t ON c.typeID = t.typeID WHERE contactID = :contact_id';
$statement = $db->prepare($query);
$statement->bindValue(':contact_id', $contact_id);
$statement->execute();
$contact = $statement->fetch();
$statement->closeCursor();

if (!$contact) {
    echo "Contact not found.";
    exit;
}

// Convert _100 image to _400 version
$imageName = $contact['imageName'];
$dotPosition = strrpos($imageName, '.');
$baseName = substr($imageName, 0, $dotPosition);
$extension = substr($imageName, $dotPosition);

if (str_ends_with($baseName, '_100')) {
    $baseName = substr($baseName, 0, -4);
}
$imageName_400 = $baseName . '_400' . $extension;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Details</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    
</head>
<body>
    <?php include("header.php"); ?>

    <div class="container">
        <h2>Contact Details</h2>

        <img class="contact-image" src="<?php echo htmlspecialchars('./images/' . $imageName_400); ?>" 
             alt="<?php echo htmlspecialchars($contact['firstName'] . ' ' . $contact['lastName']); ?>" />

        <div class="contact-info">
            <p><strong>First Name:</strong> <?php echo htmlspecialchars($contact['firstName']); ?></p>
            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($contact['lastName']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($contact['emailAddress']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($contact['phone']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($contact['status']); ?></p>
            <p><strong>Birth Date:</strong> <?php echo htmlspecialchars($contact['dob']); ?></p>
            <p><strong>Contact Type:</strong> <?php echo htmlspecialchars($contact['contactType']); ?></p>
        </div>

        <a class="back-link" href="index.php">← Back to Contact List</a>
    </div>

    <?php include("footer.php"); ?>
</body>
</html>
