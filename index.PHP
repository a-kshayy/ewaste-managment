<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ewaste-portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Ewaste-portal</h1>
    <?php
        require "config/db.php";
        
        $stmt = $pdo->query("SELECT * FROM categories");
        $categories = $stmt->fetchall();
        foreach($categories as $cat){
          echo "<p><strong>" . $cat['name'] . "</strong> - " .$cat['description'] . "</p>";
          }
         ?>
</body>
</html>