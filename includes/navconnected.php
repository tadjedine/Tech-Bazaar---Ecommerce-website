<ul id="dropdown2" class="dropdown-content">
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <li><a class="blue-text" href="admin/index">Dashboard</a></li>
    <?php endif; ?>
    <li><a class="blue-text" href="editprofile">Edit Profile</a></li>
    <li><a class="blue-text" href="orders">My Orders</a></li>
    <li><a class="blue-text" href="cancelled_orders">Cancelled Orders</a></li>
    <li><a class="blue-text" href="includes/logout">Log out</a></li>
</ul>
<div class="navbar-fixed">
    <nav class="navblack">
        <div class="nav-wrapper nav-wrapper-2 container">
            <ul class="left hide-on-med-and-down">
                <li><a href="index" class="dark-text">
                    <img src="src/img/Tech Bazaar.png" alt="Tech Bazaar Logo" height="32" style="vertical-align: middle; margin-right: 8px; margin-top: -5px;">
                    TechBazaar
                </a></li>
            </ul>

            <ul class="center hide-on-large-only">
                <li><a href="index" class="dark-text">
                    <img src="src/img/Tech Bazaar.png" alt="Tech Bazaar Logo" height="28" style="vertical-align: middle; margin-right: 8px; margin-top: -5px;">
                    TechBazaar
                </a></li>
            </ul>

            <ul class="right hide-on-med-and-down">
                <li><a href="index" class="dark-text">Home</a></li>
                <li><a href="#about" class="dark-text">About</a></li>
                <li><a href="#contact" class="dark-text">Contact</a></li>
                <li><a href="cart" class="dark-text baskett">
                    <i class="material-icons">shopping_cart</i>
                </a></li>
                <li><a href="editprofile" class="nohover dropdown-button" class="dropdown-button" data-activates="dropdown2"><img class="responsive-img circle" src="users/default.jpg" width="32" style="margin-top: 15px;">
                        <i class="material-icons right dark-text">arrow_drop_down</i></a></li>
            </ul>
        </div>
    </nav>
</div>

<style>
.baskett {
    position: relative;
}
</style>
