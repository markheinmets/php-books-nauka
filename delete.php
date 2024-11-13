<?php

    require_once('./connection.php');

    // Get the book ID from the POST request
    $id = $_POST['id'] ?? null; // Use null coalescing to avoid notices if 'id' is not set

    if ($id) {
        // Prepare the statement to update the is_deleted column
        $stmt = $pdo->prepare('UPDATE books SET is_deleted = 1 WHERE id = :id');
        
        // Execute the update
        $stmt->execute(['id' => $id]);

        // Redirect to the index page after the soft delete
        header('Location: ./index.php');
        exit(); // Exit after redirection to avoid further script execution
    } else {
        echo "Error: Book ID not provided."; // Handle the case where ID is not set
    }
?>


