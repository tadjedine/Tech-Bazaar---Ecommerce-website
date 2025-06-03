<?php
session_start();

if (!isset($_GET['id'])) {
    header('Location: index');
}

if (!isset($_SESSION['logged_in'])) {
  $nav = 'includes/nav.php';
}
else {
  $nav ='includes/navconnected.php';
  $idsess = $_SESSION['id'];
}

$id_product =$_GET['id'];
 require 'includes/header.php';
 require $nav;?>

 <div class="container-fluid product-page" id="top">
   <div class="container current-page">
      <nav>
        <div class="nav-wrapper">
          <div class="col s12">
            <a href="index" class="breadcrumb">Home</a>
            <a href="product.php?id=<? $id_product; ?>" class="breadcrumb">Product</a>
          </div>
        </div>
      </nav>
    </div>
   </div>


<div class="container-fluid product">
  <div class="container">
   <div class="row">
     <div class="col s12 m6">
        <div class="card">
          <div class="card-image">
            <?php

            include 'db.php';

            //get products
            $queryproduct = "SELECT id, name, price, description, id_picture, thumbnail, stock
              FROM product WHERE id = '{$id_product}'";
            $result1 = $connection->query($queryproduct);
            if ($result1->num_rows > 0) {
            // output data of each row
            while($rowproduct = $result1->fetch_assoc()) {
              $id_productdb = $rowproduct['id'];
              $name_product = $rowproduct['name'];
              $price_product = $rowproduct['price'];
              $id_pic = $rowproduct['id_picture'];
              $description = $rowproduct['description'];
              $thumbnail_product = $rowproduct['thumbnail']; 
              $stock_product = $rowproduct['stock'];
            }}?>
            <img class="materialboxed" width="650" src="products/<?= $thumbnail_product; ?>" alt="">
          </div>
        </div>
       <div class="row">
         <?php

         //get categories
           $querypic = "SELECT picture, id_product FROM pictures WHERE id_product = '$id_pic'";
           $total = $connection->query($querypic);
           if ($total->num_rows > 0) {
           // output data of each row
           while($rowpic = $total->fetch_assoc()) {
             $pics = $rowpic['picture'];
          ?>
           <div class="col s12 m4">
             <div class="card hoverable">
               <div class="card-image">
                 <img class="materialboxed" width="650" src="productsimg/<?= $pics; ?>" alt="">
               </div>
             </div>
           </div>
         <?php }} ?>
       </div>
     </div>

     <div class="col s12 m6">
       <form method="post">
       <h2><?= $name_product; ?></h2>
       <div class="divider"></div>
       <div class="stuff">
        <h3 class="woow">Price</h3>
        <h5>$ <?= $price_product; ?></h5>
        <p><?= $description; ?></p>
        <p class="<?= ($stock_product > 0) ? 'green-text' : 'red-text'; ?>">
          <?= ($stock_product > 0) ? "In Stock: $stock_product items available" : "Out of Stock"; ?>
        </p>
        <div class="input-field col s12">
          <i class="material-icons prefix">shopping_basket</i>
          <input id="icon_prefix" type="number" name="quantity" min="1" max="<?= $stock_product; ?>" value="1"
                 oninvalid="this.setCustomValidity('Please select a quantity between 1 and <?= $stock_product; ?>')" 
                 oninput="this.setCustomValidity('')"
                 class="validate" required <?= ($stock_product <= 0) ? 'disabled' : ''; ?>>
          <label for="icon_prefix">Quantity</label>
        </div>

           <?php

            if (isset($_POST['buy'])) {
               if (!isset($_SESSION['logged_in'])) {
                 // Instead of redirecting to sign in, create a guest cart in session
                 if (!isset($_SESSION['guest_cart'])) {
                   $_SESSION['guest_cart'] = array();
                 }
                 
                 $quantity = $_POST['quantity'];
                 
                 // Check if we have enough stock
                 if ($quantity > $stock_product) {
                   echo '<div class="center-align red-text">
                     <h5>Error: Not enough stock available</h5>
                   </div>';
                 } else {
                   // Add to guest cart
                   $item = array(
                     'id' => $id_productdb,
                     'name' => $name_product,
                     'price' => $price_product,
                     'quantity' => $quantity,
                     'thumbnail' => $thumbnail_product
                   );
                   
                   // Add/update item in cart
                   $found = false;
                   foreach ($_SESSION['guest_cart'] as $key => $cart_item) {
                     if ($cart_item['id'] == $id_productdb) {
                       $_SESSION['guest_cart'][$key]['quantity'] += $quantity;
                       $found = true;
                       break;
                     }
                   }
                   
                   if (!$found) {
                     $_SESSION['guest_cart'][] = $item;
                   }
                   
                   // Count the total number of items in cart instead of incrementing
                   $totalItems = 0;
                   foreach ($_SESSION['guest_cart'] as $cartItem) {
                     $totalItems += $cartItem['quantity'];
                   }
                   $_SESSION['item'] = $totalItems;
                   
                   echo '<div class="center-align green-text">
                     <h5>Item added to cart successfully!</h5>
                   </div>';
                   
                   echo "<meta http-equiv='refresh' content='2;url=product.php?id=" . $id_product . "' />";
                 }
               }
               else {
                  $quantity = $_POST['quantity'];
                  
                  include 'db.php';
                  
                  // Start transaction
                  $connection->begin_transaction();
                  
                  try {
                      // Check stock availability
                      $checkStock = "SELECT stock FROM product WHERE id = ? AND stock >= ?";
                      $checkStmt = $connection->prepare($checkStock);
                      $checkStmt->bind_param("ii", $id_productdb, $quantity);
                      $checkStmt->execute();
                      $result = $checkStmt->get_result();
                      
                      if ($result->num_rows === 0) {
                          throw new Exception("Not enough stock available");
                      }
                      
                      // Insert into command
                      $querybuy = "INSERT INTO command(id_product, quantity, statut, id_user)
                                   VALUES (?, ?, 'ordered', ?)";
                      
                      $stmt = $connection->prepare($querybuy);
                      $stmt->bind_param("iis", $id_productdb, $quantity, $idsess);
                      
                      if (!$stmt->execute()) {
                          throw new Exception($stmt->error);
                      }
                      
                      // Update product stock
                      $updateStock = "UPDATE product SET stock = stock - ? WHERE id = ?";
                      $updateStmt = $connection->prepare($updateStock);
                      $updateStmt->bind_param("ii", $quantity, $id_productdb);
                      
                      if (!$updateStmt->execute()) {
                          throw new Exception($updateStmt->error);
                      }
                      
                      // Initialize if not set
                      if (!isset($_SESSION['item'])) {
                        $_SESSION['item'] = 0;
                      }
                      
                      $_SESSION['item'] += 1;
                      
                      $connection->commit();
                      
                      echo '<div class="center-align green-text">
                        <h5>Item added to cart successfully!</h5>
                      </div>';
                      
                      // Refresh the page to show the updated stock
                      echo "<meta http-equiv='refresh' content='2;url=product.php?id=" . $id_product . "' />";
                      
                  } catch (Exception $e) {
                      // Rollback the transaction
                      $connection->rollback();
                      
                      echo '<div class="center-align red-text">
                        <h5>Error: ' . $e->getMessage() . '</h5>
                      </div>';
                  }
                  
                  if (isset($stmt)) $stmt->close();
                  if (isset($checkStmt)) $checkStmt->close();
                  if (isset($updateStmt)) $updateStmt->close();
                }
            }

            ?>

       <div class="center-align">
           <button type="submit" name="buy" class="btn-large meh button-rounded waves-effect waves-light" <?= ($stock_product <= 0) ? 'disabled' : ''; ?>>Add to Cart</button>
       </div>
       </div>
        </form>
     </div>
   </div>
  </div>
</div>
<?php
 require 'includes/secondfooter.php';
 require 'includes/footer.php'; ?>
