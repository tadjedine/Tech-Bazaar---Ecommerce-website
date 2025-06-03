<!DOCTYPE html>
<html>
  <head>
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>TechBazaar - Admin Dashboard</title>
      <link rel="icon" href="../src/img/icon.png">
      <!--Let browser know website is optimized for mobile-->
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <!-- font-awesome -->
      <link rel="stylesheet" href="../src/css/font-awesome-4.6.3/css/font-awesome.min.css">
      <!--Import materialize.css-->
      <link type="text/css" rel="stylesheet" href="../src/css/materialize.min.css"  media="screen,projection"/>
      <!-- animate css -->
      <link rel="stylesheet" href="../src/css/animate.css-master/animate.min.css">
      <!-- Google fonts -->
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <!-- My own style -->
      <link rel="stylesheet" href="../src/css/radical-theme.css">
      <link rel="stylesheet" href="../src/css/style.css">
      <link rel="stylesheet" href="src/css/admin-style.css">
      <!-- Progress bar -->
      <link rel='stylesheet' href='../src/css/nprogress.css'/>
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      
      <style>
        .admin-dashboard-header {
          background: linear-gradient(45deg, #311B92, #5E35B1);
          color: white;
          padding: 20px;
          margin-bottom: 30px;
          border-radius: 8px;
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .admin-dashboard-header h4 {
          margin: 0;
          font-weight: 600;
        }
        
        .admin-card {
          border-radius: 8px;
          overflow: hidden;
          transition: all 0.3s ease;
        }
        
        .admin-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .admin-card .card-content {
          background: white;
          min-height: 150px; /* Ensure sufficient height for content */
          display: flex;
          flex-direction: column;
        }
        
        .admin-card .card-content p {
          flex-grow: 1;
          overflow: visible; /* Ensure text is not cut off */
          margin-bottom: 10px;
        }
        
        .admin-panel .btn {
          background: #FF3D00;
          box-shadow: 0 4px 10px rgba(255, 61, 0, 0.3);
        }
        
        .admin-panel .btn:hover {
          background: #ff6333;
          box-shadow: 0 6px 15px rgba(255, 61, 0, 0.4);
        }
        
        .product-page {
          background: linear-gradient(45deg, #311B92, #5E35B1);
        }
        
        .product-page nav {
          background: transparent;
        }
        
        .product-page .breadcrumb {
          color: rgba(255, 255, 255, 0.8);
        }
        
        .product-page .breadcrumb:before {
          color: rgba(255, 255, 255, 0.5);
        }
      </style>
    </head>
  <body>
