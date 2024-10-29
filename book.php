<?php

require_once('./connection.php');

// Get the book ID from the URL
$id = $_GET['id'] ?? null; // Use null coalescing to avoid notices if 'id' is not set

if (!$id) {
    die('Book ID not provided.'); // Exit if ID is not provided
}

// Step 1: Retrieve book details
$stmtBook = $pdo->prepare('SELECT * FROM books WHERE id = :id');
$stmtBook->execute(['id' => $id]);
$book = $stmtBook->fetch(); // Fetch the book details

// Step 2: Retrieve all authors linked to this book using the junction table
$stmtAuthors = $pdo->prepare('
    SELECT authors.first_name, authors.last_name FROM authors
    INNER JOIN book_authors ON authors.id = book_authors.author_id
    WHERE book_authors.book_id = :id
');
$stmtAuthors->execute(['id' => $id]);
$authors = $stmtAuthors->fetchAll(); // Fetch all authors linked to the book

// Now you have $book and $authors available for use in your HTML

if (!$book) {
    die('Book not found.'); // Exit if book is not found
}

// Generate the action URL for deletion
$action_url = "./delete.php?id=" . urlencode($book['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['title']); ?></title>
</head>
<body>
    <h1>Book Details</h1>

    <img src="<?= htmlspecialchars($book['cover_path']); ?>" alt="">
    
    <h2><?= htmlspecialchars($book['title']) ?></h2>
    <p>Published: <?= htmlspecialchars($book['release_date']) ?></p>
    
    <h3>Author(s):</h3>
    <ul>
        <?php foreach ($authors as $author): ?>
           <li><?= htmlspecialchars($author['first_name']) . ' ' . htmlspecialchars($author['last_name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Price: <?= htmlspecialchars($book['price']); ?></h3>

    <h3>Language: <?= htmlspecialchars($book['language']); ?></h3>

    <a href="./edit.php?id=<?= $book['id']; ?>">Edit</a>

    <a href="./index.php?search=<?= urlencode($_GET['search'] ?? '') ?>">Homepage</a>
    
    <br><br>
    
    <form action="<?= htmlspecialchars($action_url) ?>" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
        <input type="hidden" name="is_deleted" value="1">
        <input type="submit" name="submit" value="Delete">
    </form>
</body>
</html>
