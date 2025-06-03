<?php

session_start();

if ($_SESSION['role'] !== 'admin') {
  header('Location: ../index');
}

require 'includes/header.php';
require 'includes/navconnected.php'; ?>

<div class="container-fluid product-page">
  <div class="container current-page">
    <nav>
      <div class="nav-wrapper">
        <div class="col s12">
          <a href="../index" class="breadcrumb">Home</a>
          <a href="index" class="breadcrumb">Dashboard</a>
        </div>
      </div>
    </nav>
  </div>
</div>

<div class="container admin-panel">
  <div class="container">
    <div class="admin-dashboard-header center-align animated fadeIn wow">
      <h4><i class="material-icons medium" style="vertical-align: middle;">dashboard</i> Admin Dashboard</h4>
    </div>
    
    <div class="row">
      <div class="col s12 m6 l4">
        <div class="admin-card animated fadeIn wow">
          <h5>Manage Users</h5>
          <p>View and manage user accounts and track user activities.</p>
          <a href="users" class="waves-effect waves-light btn">MANAGE</a>
        </div>
      </div>

      <div class="col s12 m6 l4">
        <div class="admin-card animated fadeIn wow">
          <h5>Statistics</h5>
          <p>Track sales metrics and monitor product popularity.</p>
          <a href="stats" class="waves-effect waves-light btn">VIEW STATS</a>
        </div>
      </div>

      <div class="col s12 m6 l4">
        <div class="admin-card animated fadeIn wow">
          <h5>Product Management</h5>
          <p>Update products, manage stock levels, and set pricing.</p>
          <a href="products" class="waves-effect waves-light btn">MANAGE</a>
        </div>
      </div>

      <div class="col s12 m6 l4">
        <div class="admin-card animated fadeIn wow">
          <h5>Add New Products</h5>
          <p>Add products with details, images, and stock information.</p>
          <a href="addproduct" class="waves-effect waves-light btn">ADD PRODUCT</a>
        </div>
      </div>

      <div class="col s12 m6 l4">
        <div class="admin-card animated fadeIn wow">
          <h5>Order Management</h5>
          <p>View orders, track shipping status, and handle cancellations.</p>
          <a href="orders" class="waves-effect waves-light btn">VIEW ORDERS</a>
        </div>
      </div>

      <div class="col s12 m6 l4">
        <div class="admin-card animated fadeIn wow">
          <h5>Profile Settings</h5>
          <p>Update your admin profile and account security settings.</p>
          <a href="editprofile" class="waves-effect waves-light btn">EDIT PROFILE</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
