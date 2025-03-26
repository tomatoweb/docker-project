<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <?php
    if(isset($_GET['message'])) {
      echo 'Welcome ' . $_GET['message'];
    } else {
      echo 'No message to display';
    }
  ?>
</body>
</html>