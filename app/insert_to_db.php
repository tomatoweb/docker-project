<?php

$servername = "mysql"; // le nom du service dans docker-compose.yml (le container php et le container mysql partage le même réseau et peuvent donc communiquer via leur nom de service/container)
$username = "root";
$password = "pw";
$dbname = "dev";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//$sql = "INSERT INTO example_table (first_name, last_name) VALUES ('John', 'Doe'), ('Jane', 'Doe')";
$sql = "INSERT INTO example_table (first_name, last_name) VALUES ('Jack', 'Russel')";

if ($conn->query($sql) === TRUE) {
  echo "New records created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();