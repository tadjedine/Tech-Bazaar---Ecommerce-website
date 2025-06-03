<?php

session_start();

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index');
}

require 'includes/header.php';
require 'includes/navconnected.php'; //require $nav;?>

<div class="container-fluid product-page">
    <div class="container current-page">
        <nav>
            <div class="nav-wrapper">
                <div class="col s12">
                    <a href="index" class="breadcrumb">Dashboard</a>
                    <a href="infoproduct" class="breadcrumb">Products</a>
                    <a href="stats" class="breadcrumb">stats</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container stats-container">
    <div class="row">
        <div class="col s12">
            <h4 class="center-align">Product Statistics by Category</h4>
            <p class="center-align">Shows the percentage of sales for each product category</p>
        </div>
    </div>

    <div class="row stats-cards">
        <?php
        include '../db.php';

        $queryfirst = "SELECT
         product.id as 'id',
         product.id_category,
         SUM(command.quantity) as 'total',
         command.statut,
         command.id_product,
         category.name as 'name',
         category.id
         
         FROM product, command, category
         
         WHERE product.id = command.id_product
         AND category.id = product.id_category
         GROUP BY category.id";
         
        $resultfirst = $connection->query($queryfirst);
        if ($resultfirst->num_rows > 0) {
            // output data of each row
            while($rowfirst = $resultfirst->fetch_assoc()) {

                $idp = $rowfirst['id'];
                $name_best = $rowfirst['name'];
                $totalsold = $rowfirst['total'] ? $rowfirst['total'] : 0;
                $percent = ($totalsold > 0) ? ($totalsold / 50) * 100 : 0;

                ?>

                <div class="col s12 m4">
                    <div class="card stats-card">
                        <div class="card-content">
                            <span class="card-title"><?= $name_best; ?></span>
                            <div class="progress">
                                <div class="determinate" style="width: <?= number_format($percent, 2); ?>%"></div>
                            </div>
                            <p>Sales: <?= $totalsold; ?> units (<?= number_format($percent, 2); ?>%)</p>
                        </div>
                    </div>
                </div>

            <?php }
        } else { ?>
            <div class="col s12">
                <div class="card">
                    <div class="card-content center-align">
                        <p>No sales data available yet. Start selling to see statistics!</p>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<style>
    .stats-container {
        margin-top: 30px;
    }
    
    .stats-card {
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .stats-card .card-title {
        font-weight: 500;
        margin-bottom: 20px;
    }
    
    .progress {
        background-color: #e0e0e0;
        height: 20px;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress .determinate {
        background-color: #5E35B1;
        border-radius: 10px;
    }
</style>

<?php require 'includes/footer.php'; ?>
