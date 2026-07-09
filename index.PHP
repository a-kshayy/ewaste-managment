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
    $name = "AKSHAY";
    echo "<p>WELCOME, $name! Today is " . date("F j , y") . "</p>";
    ?>
    <button onclick="sayHi()">CLICK ME</button>
    <script>
    function sayHi()
    {
        alert("Hello frome javascript!");
    }
        </script>
</body>
</html>