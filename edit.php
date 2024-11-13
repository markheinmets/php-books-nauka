<?php

    require_once('./connection.php');

    $id = $_GET['id'];

    // Fetch book details
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $book = $stmt->fetch();

    // Fetch authors associated with the book
    $stmtAuthors = $pdo->prepare('SELECT a.id, a.first_name, a.last_name FROM authors a 
                                JOIN book_authors ba ON a.id = ba.author_id 
                                WHERE ba.book_id = :book_id');
    $stmtAuthors->execute(['book_id' => $id]);
    $authors = $stmtAuthors->fetchAll();

    if ( isset($_POST['submit_book']) && $_POST['submit_book'] == 'Save' ) {
        $stmt = $pdo->prepare('UPDATE books SET title = :title, release_date = :release_date, price = :price, language = :language WHERE id = :id');
        $stmt->execute([
            'id' => $id, 
            'title' => $_POST['title'],
            'release_date' => $_POST['release_date'],
            'price' => $_POST['price'],
            'language' => $_POST['language'],
        ]);

        $author_ids = $_POST['author_id'];
        $first_names = $_POST['author_first_name'];
        $last_names = $_POST['author_last_name'];

        foreach ($author_ids as $index => $author_id) {
            $stmtAuthor = $pdo->prepare('UPDATE authors SET first_name = :first_name, last_name = :last_name WHERE id = :id');
            $stmtAuthor->execute([
                'id' => $author_id,
                'first_name' => $first_names[$index],
                'last_name' => $last_names[$index],
            ]);
        };

        // Redirect to the book details page
        header('Location: ./book.php?id=' . $book['id']);
        exit();
    }

    // Add new authors if provided
    if (!empty($_POST['new_author_first_name'])) {
        foreach ($_POST['new_author_first_name'] as $index => $first_name) {
            if (!empty($first_name)) {
                $last_name = $_POST['new_author_last_name'][$index];
                $stmtNewAuthor = $pdo->prepare('INSERT INTO authors (first_name, last_name) VALUES (:first_name, :last_name)');
                $stmtNewAuthor->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]);

                // Get the ID of the newly added author and link them to the book
                $newAuthorId = $pdo->lastInsertId();
                $stmtLink = $pdo->prepare('INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)');
                $stmtLink->execute([
                    'book_id' => $id,
                    'author_id' => $newAuthorId
                ]);
            }
        }
    }
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $book = $stmt->fetch();

    // Check for any authors to add
    $newAuthors = !empty($_POST['new_author_first_name']) ? array_filter($_POST['new_author_first_name']) : [];
    // Handle deletion of authors if a delete button was clicked
    if (isset($_POST['delete_author_id'])) {
        foreach ($_POST['delete_author_id'] as $author_id) {
            // First, delete the associations in the book_authors table
            $stmtDeleteAssociation = $pdo->prepare('DELETE FROM book_authors WHERE author_id = :author_id AND book_id = :book_id');
            $stmtDeleteAssociation->execute(['author_id' => $author_id, 'book_id' => $id]);

            // Then delete the author from the authors table
            $stmtDelete = $pdo->prepare('DELETE FROM authors WHERE id = :id');
            $stmtDelete->execute(['id' => $author_id]);
        }
    
        // Redirect to the same page to see the changes
        header('Location: ./edit.php?id=' . $id);
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <form action="./edit.php?id=<?= $book['id']; ?>" method="post">
        <input type="hidden" name="id" value="<?= $book['title']; ?>">
        <label for="title">Title</label><br> <input type="text" name="title" value="<?= $book['title']; ?>" style="width: 240px;"><br><br>
        <label for="release_date">Release Date</label><br> <input type="text" name="release_date" value= "<?= $book['release_date']; ?>"><br><br>
        <label for="price">Price</label><br> <input type="number" step="0.01" name="price" value="<?= $book['price']; ?>"><br><br>
        <label for="language">Language</label><br> <input type="text" name="language" value="<?= $book['language']; ?>"><br><br>
        <input type="submit" name="submit_book" value="Save">
         <h3>Authors:</h3>
        <?php foreach ($authors as $author): ?>
            
            <input type="hidden" name="author_id[]" value="<?= $author['id']; ?>"> <!-- Store author IDs -->
            <label for="author_first_name[]">First name <br></label><input type="text" name="author_first_name[]" value="<?= htmlspecialchars($author['first_name']); ?>" placeholder="First Name" required> <br>
            <label for="author_last_name[]">Last name <br></label><input type="text" name="author_last_name[]" value="<?= htmlspecialchars($author['last_name']); ?>" placeholder="Last Name" required><br><br>
            <button type="submit" name="delete_author_id[]" value="<?= $author['id']; ?>"> Delete </button>
            
        <?php endforeach; ?>
        <h3>Add New Authors:</h3>
        <div id="newAuthorsContainer">
        <input type="text" name="new_author_first_name[]" placeholder="First Name"><br><br>
        <input type="text" name="new_author_last_name[]" placeholder="Last Name">
        </div>
    <br>
    <button type="submit" name="add_new_author">Add Another Author</button>

    </form>
    <br><br>
    <a href="./book.php?id=<?= $book['id']; ?>">
        back
    </a>
</body>
</html>