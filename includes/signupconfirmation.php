<?php
if (isset($_POST['signup'])) {

  $email = $_POST['email'];
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  $password = $_POST['password'];
  $address = $_POST['address'];
  $city = $_POST['city'];
  $country = $_POST['country'];

  // Use PHP's password_hash instead of md5 for better security
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  include 'db.php';

  // Sanitize input to prevent SQL injection
  $email = mysqli_real_escape_string($connection, $email);
  $firstname = mysqli_real_escape_string($connection, $firstname);
  $lastname = mysqli_real_escape_string($connection, $lastname);
  $address = mysqli_real_escape_string($connection, $address);
  $city = mysqli_real_escape_string($connection, $city);
  $country = mysqli_real_escape_string($connection, $country);

  // Check if email already exists
  $check_email = "SELECT * FROM users WHERE email = ?";
  $stmt = $connection->prepare($check_email);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    echo "<div class='center-align'>
         <h5 class='red-text'>Email already exists! Please use a different email.</h5>
         </div>";
    $stmt->close();
  } else {
    $stmt->close();
    
    // Use prepared statement for secure insertion
    $query = "INSERT INTO users(email, firstname, lastname, password, address, city, country, role) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'client')";
              
    $stmt = $connection->prepare($query);
    $stmt->bind_param("sssssss", $email, $firstname, $lastname, $hashed_password, $address, $city, $country);
    
    if ($stmt->execute()) {
      echo "<div class='center-align'>
         <h5 class='black-text'>Welcome <span class='green-text'>$firstname</span> Please Log In</h5><br><br>
         <a class='button-rounded btn waves-effects waves-light'>Log In</a>
         </div>";
    } else {
      echo "<h5 class='red-text'>Error: " . $stmt->error . "</h5>";
    }
    
    $stmt->close();
  }
  
  $connection->close();
}
?>
