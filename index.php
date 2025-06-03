<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
    $nav = 'includes/nav.php';
    // Initialize item count for non-logged in users
    if (!isset($_SESSION['item'])) {
        $_SESSION['item'] = 0;
    }
} else {
    $nav = 'includes/navconnected.php';
    $idsess = $_SESSION['id'];
}

require 'includes/header.php';
require $nav; ?>

<style>
    .autocomplete {
        /*the container must be positioned relative:*/
        position: relative;
        display: block;
    }

    .autocomplete-items {
        color: #FF3D00;
        font: 16px Poppins, sans-serif;
        position: absolute;
        border: 1px solid #d4d4d4;
        border-bottom: none;
        border-top: none;
        z-index: 99;
        /*position the autocomplete items to be the same width as the container:*/
        top: 100%;
        left: 0;
        right: 0;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }

    .autocomplete-items div {
        padding-bottom: 20px;
        padding-top: 20px;
        padding-left: 30px;
        cursor: pointer;
        background-color: #fff;
    }

    .autocomplete-items div:hover {
        /*when hovering an item:*/
        color: #FF3D00;
        background-color: #f1f1f1;
    }

    .autocomplete-active {
        /*when navigating through the items using the arrow keys:*/
        background-color: #311B92 !important;
        color: #ffffff;
    }
    
    .hero-caption {
        color: white;
        text-align: center;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .hero-caption h2 {
        font-weight: 700;
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    
    .hero-caption p {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }
</style>

<div class="container-fluid home" id="top">
    <div class="hero-caption animated fadeIn wow">
        <h2>Welcome to TechBazaar</h2>
        <p>Find the latest tech gadgets and electronics at amazing prices</p>
    </div>
    <div class="container search">
        <div class="row">
            <div class="col s12">
                <form method="GET" action="search.php">
                    <div class="input-field">
                        <input id="search" class="searching" type="search" name="searched" required autocomplete="off" placeholder="Search products...">
                        <i class="material-icons">close</i>
                    </div>
                    <div class="center-align">
                        <button type="submit" class="waves-light miaw waves-effect btn hide">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container most">
    <h4 class="center-align animated fadeIn wow">Top Trending Products</h4>
    <div class="row">
        <?php

        include 'db.php';

        // selecting product available in largest quantity
        $queryfirst = "SELECT
    product.id as 'id',
    product.name as 'name',
    product.price as 'price',
    product.thumbnail as 'thumbnail',
    
    SUM(command.quantity) as 'total',
    command.statut,
    command.id_product
    
    FROM product, command
    WHERE product.id = command.id_product AND command.statut = 'paid'
    GROUP BY product.id
    ORDER BY SUM(command.quantity) DESC LIMIT 6";
        $resultfirst = $connection->query($queryfirst);
        if ($resultfirst->num_rows > 0) {
            // output data of each row
            while ($rowfirst = $resultfirst->fetch_assoc()) {

                $id_best = $rowfirst['id'];
                $name_best = $rowfirst['name'];
                $price_best = $rowfirst['price'];
                $thumbnail_best = $rowfirst['thumbnail'];
                $totalsold = $rowfirst['total'];

        ?>

                <div class="col s12 m4">
                    <div class="card hoverable animated fadeIn wow">
                        <div class="card-image">
                            <a href="product.php?id=<?= $id_best;  ?>"><img src="products/<?= $thumbnail_best; ?>"></a>
                            <span class="card-title"><?= $name_best; ?></span>
                            <a href="product.php?id=<?= $id_best; ?>" class="btn-floating halfway-fab waves-effect waves-light"><i class="material-icons">add</i></a>
                        </div>
                        <div class="card-content">
                            <div class="container">
                                <div class="row">
                                    <div class="col s6">
                                        <p><i class="material-icons left">attach_money</i> <?= $price_best; ?></p>
                                    </div>
                                    <div class="col s6">
                                        <p><i class="material-icons left">shopping_basket</i> <?= $totalsold; ?> sold</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
        <?php }
        } ?>


    </div>
</div>

<div class="container-fluid center-align categories">
    <a href="#category" class="button-rounded btn-large waves-effect waves-light">Browse Categories</a>
    <div class="container" id="category">
        <div class="row">
            <?php

            //get categories
            $querycategory = "SELECT id, name, icon  FROM category";
            $total = $connection->query($querycategory);
            if ($total->num_rows > 0) {
                // output data of each row
                while ($rowcategory = $total->fetch_assoc()) {
                    $id_category = $rowcategory['id'];
                    $name_category = $rowcategory['name'];
                    $icon_category = $rowcategory['icon'];

            ?>

                    <div class="col s12 m4">
                        <div class="card hoverable animated fadeIn wow">
                            <div class="card-image">
                                <a href="category.php?id=<?= $id_category; ?>"><img src="src/img/<?= $icon_category; ?>.png" alt=""></a>
                                <span class="card-title"><?= $name_category; ?></span>
                            </div>
                        </div>
                    </div>

            <?php }
            } ?>
        </div>
    </div>
</div>


<div class="container-fluid about" id="about">
    <div class="container">
        <div class="row">
            <div class="col s12 m6">
                <div class="card animated fadeIn wow">
                    <div class="card-image">
                        <img src="src/img/Tech Bazaar.png" alt="Tech Bazaar Logo" class="responsive-img" style="padding: 20px;">
                    </div>
                </div>
            </div>

            <div class="col s12 m6">
                <h3 class="animated fadeIn wow" style="color: #2196F3;">About Us</h3>
                <div class="divider animated fadeIn wow"></div>
                <p class="animated fadeIn wow">At TechBazaar, we're passionate about bringing you the latest and greatest in technology. Founded with a vision to make high-quality electronics accessible to everyone, we've grown into a trusted destination for tech enthusiasts.
                <br><br>
                Our curated selection features products from top brands, ensuring you receive only the best. Whether you're looking for a new smartphone, laptop, camera, or smart home device, we've got you covered with competitive prices and exceptional service.
                <br><br>
                With our secure shopping platform and dedicated customer support team, you can shop with confidence knowing that we're here to assist you every step of the way.</p>
            </div>

        </div>
    </div>
</div>

<div class="container contact" id="contact">
    <div class="row">
        <form action="https://postmail.invotes.com/send" method="post" id="email_form" class="col s12 animated fadeIn wow">
            <h3 class="animated fadeIn wow">Contact Us</h3>
            <div class="row">
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">account_circle</i>
                    <input id="icon_prefix" name="subject" type="text" class="validate">
                    <label for="icon_prefix">Subject</label>
                </div>
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">email</i>
                    <input id="email" name="reply_to" type="email" class="validate">
                    <label for="email" data-error="wrong" data-success="right">Email</label>
                </div>
                <div class="input-field col s12 ">
                    <i class="material-icons prefix">message</i>
                    <textarea id="icon_prefix2" class="materialize-textarea" type="text" name="text" rows="4"" style=" resize: vertical;min-height: 8rem;" required></textarea>
                    <label for="icon_prefix2">Your message</label>
                </div>
                <!-- Go to https://postmail.invotes.com/ to get an access token -->
                <input type="hidden" name="access_token" value="" />
                <input type="hidden" name="success_url" value="." />
                <input type="hidden" name="error_url" value=".?err=1" />
                <div class="center-align">
                    <button id="submit_form" type="submit" name="contact" value="Send" class="button-rounded btn-large waves-effect waves-light">Submit</button>
                </div>
                <p>Powered by <a href="https://postmail.invotes.com" target="_blank">PostMail</a></p>
            </div>
        </form>
    </div>
</div>



<?php
require 'includes/secondfooter.php';
require 'includes/footer.php'; ?>
<script>
    var submitButton = document.getElementById("submit_form");
    var form = document.getElementById("email_form");
    form.addEventListener("submit", function(e) {
        setTimeout(function() {
            submitButton.value = "Sending...";
            submitButton.disabled = true;
        }, 1);
    });
</script>