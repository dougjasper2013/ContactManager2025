<?php
require_once('database.php');

$contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);

if ($contact_id == null) {
    header("Location: index.php");
    exit();
}

// Get contact details
$query = '
    SELECT c.*, t.contactType
    FROM contacts c
    LEFT JOIN types t ON c.typeID = t.typeID
    WHERE c.contactID = :contact_id';
$statement = $db->prepare($query);
$statement->bindValue(':contact_id', $contact_id);
$statement->execute();
$contact = $statement->fetch();
$statement->closeCursor();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Manager - Contact Details</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
</head>
<body>
    <?php include("header.php"); ?>

    <main>
        <h2>Contact Details</h2>

        <?php
            $imageName = $contact['imageName'];

            // Remove _100 before extension and add _400 instead
            $dotPosition = strrpos($imageName, '.');
            $baseName = substr($imageName, 0, $dotPosition);
            $extension = substr($imageName, $dotPosition);

            // If filename ends in _100, replace with _400
            if (str_ends_with($baseName, '_100')) {
                $baseName = substr($baseName, 0, -4); // Remove '_100'
            }
            $imageName_400 = $baseName . '_400' . $extension;
        ?>
        <img src="<?php echo htmlspecialchars('./images/' . $imageName_400); ?>"
            alt="<?php echo htmlspecialchars($contact['firstName'] . ' ' . $contact['lastName']); ?>" />

        <ul>
            <li><strong>First Name:</strong> <?php echo htmlspecialchars($contact['firstName']); ?></li>
            <li><strong>Last Name:</strong> <?php echo htmlspecialchars($contact['lastName']); ?></li>
            <li><strong>Email:</strong> <?php echo htmlspecialchars($contact['emailAddress']); ?></li>
            <li><strong>Phone:</strong> <?php echo htmlspecialchars($contact['phone']); ?></li>
            <li><strong>Status:</strong> <?php echo htmlspecialchars($contact['status']); ?></li>
            <li><strong>Birth Date:</strong> <?php echo htmlspecialchars($contact['dob']); ?></li>
            <li><strong>Contact Type:</strong> <?php echo htmlspecialchars($contact['contactType']); ?></li>
        </ul>

        <p><a href="index.php">Back to Contact List</a></p>
    </main>

    <?php include("footer.php"); ?>
</body>
</html>
