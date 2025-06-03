
<?php if (isset($_POST['login'])) {
    $email = $_POST['name']; // Assuming the login form uses 'name' for the email input
    $password = md5($_POST['pass']);
    include 'db.php';

    $email = mysqli_real_escape_string($connection, $email);
    $query = "SELECT * FROM users WHERE email='{$email}' AND password = '{$password}'";
    $select_user_query = mysqli_query($connection, $query);

    if (!$select_user_query) {
        die("QUERY FAILED: " . mysqli_error($connection));
    }

    $row = mysqli_fetch_array($select_user_query);

    if (!$row) {
        echo "<div class='center-align meh'>
            <a class='btn btn-large waves-effect gradient-minimalred lights-effect'>Wrong Info <i class='material-icons right'>close</i></a>
        </div>";
    } else {
        $db_id = $row['id'];
        $db_email = $row['email'];
        $db_password = $row['password'];
        $db_role = $row['role']; // Assuming 'role' is used instead of 'job'

        if ($db_role == 'admin') {
            $querystatut = "UPDATE users SET statut = 'online' WHERE id='$db_id'";
            $resultstatut = $connection->query($querystatut);

            $_SESSION['admin'] = $db_role;
            $_SESSION['id'] = $db_id;
            $_SESSION['user'] = $db_email;
            $_SESSION['logged_in'] = 'True';
            header("Location: admin/index.php");
        } else {
            $querystatut = "UPDATE users SET statut = 'online' WHERE id='$db_id'";
            $resultstatut = $connection->query($querystatut);

            $_SESSION['id'] = $db_id;
            $_SESSION['user'] = $db_email;
            $_SESSION['logged_in'] = 'True';
            header("Location: index");
        }
    }
} ?>