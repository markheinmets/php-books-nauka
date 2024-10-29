<?php

require_once('./connection.php');

$stmt = $pdo->query('SELECT id, title, release_date FROM books WHERE is_deleted=0');

// Check if a search term is provided
$searchTerm = $_GET['search'] ?? ''; // Default to an empty string if not set
$searchTerm = '%' . $searchTerm . '%'; // Prepare for SQL LIKE

// Prepare the SQL query with a search filter if search term is provided
$stmt = $pdo->prepare('SELECT id, title, release_date FROM books WHERE is_deleted = 0 AND title LIKE :searchTerm');
$stmt->bindParam(':searchTerm', $searchTerm);
$stmt->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<form action="./index.php" method="get">
    <input type="text" name="search" placeholder="Search for books" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <button type="submit">Search</button>
</form>

<ul>

    <?php
     while ($book = $stmt->fetch())
    {?>
    <li>
        <a href="./book.php?id=<?= $book['id']; ?>">
            <?= $book['title']; ?>
        </a>
    </li>
     <?php } ?>
   
</ul>
</body>
</html>