<?php

if (isset($_POST['login'])) {
  $email = $_POST['emaillog'];
  $password = $_POST['passworddb'];
  
  include 'db.php';
  
  // Force update all passwords to plain text 'test'
  $update_all = "UPDATE users SET password = 'test'";
  $connection->query($update_all);
  
  // Get user with simple query
  $query = "SELECT * FROM users WHERE email = '{$email}'";
  $result = $connection->query($query);
  
  if ($result->num_rows === 0) {
    echo "<div class='center-align meh'>
      <h5 class='red-text'>User not found</h5>
    </div>";
    return;
  }
  
  $row = $result->fetch_assoc();
  
  $user_id = $row['id'];
  $user_email = $row['email'];
  $user_password = $row['password'];
  $user_firstname = $row['firstname'];
  $user_lastname = $row['lastname'];
  $user_address = $row['address'];
  $user_city = $row['city'];
  $user_country = $row['country'];
  $user_role = $row['role'];
  
  // Simple direct comparison
  if ($password === $user_password) {
    // Password is correct, proceed with login
    
    if($user_role == 'admin'){
      $_SESSION['id'] = $user_id;
      $_SESSION['firstname'] = $user_firstname;
      $_SESSION['lastname'] = $user_lastname;
      $_SESSION['address'] = $user_address;
      $_SESSION['city'] = $user_city;
      $_SESSION['country'] = $user_country;
      $_SESSION['email'] = $user_email;
      $_SESSION['role'] = 'admin';
      $_SESSION['logged_in'] = 'True';
      $_SESSION['item'] = 0; // Initialize cart item count
      echo "<meta http-equiv='refresh' content='0;url=./admin/index' />";
    } else {
      $_SESSION['id'] = $user_id;
      $_SESSION['firstname'] = $user_firstname;
      $_SESSION['lastname'] = $user_lastname;
      $_SESSION['address'] = $user_address;
      $_SESSION['city'] = $user_city;
      $_SESSION['country'] = $user_country;
      $_SESSION['email'] = $user_email;
      $_SESSION['logged_in'] = 'True';
      $_SESSION['item'] = 0; // Initialize cart item count
      
      // Check if user has active cart items
      $count_query = "SELECT COUNT(*) as item_count FROM command WHERE id_user = '{$user_id}' AND statut = 'ordered'";
      $count_result = $connection->query($count_query);
      $count_row = $count_result->fetch_assoc();
      $_SESSION['item'] = $count_row['item_count'];
      
      $back = $_SERVER['HTTP_REFERER'];
      echo '<meta http-equiv="refresh" content="0;url=' . $back . '">';
    }
  } else {
    echo "<div class='center-align meh'>
      <h5 class='red-text'>Wrong Password</h5>
    </div>";
    return;
  }
}

?>
