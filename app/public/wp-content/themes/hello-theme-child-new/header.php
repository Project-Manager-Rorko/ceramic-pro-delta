<?php
/**
 * Header template for Hello Elementor Child.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$logo_url = content_url( '/uploads/2026/05/ceramic-logos1.svg' );
$phone_icon_url = content_url( '/uploads/2026/05/ic_outline-phone.svg' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<!-- Owl Carousel CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css" />
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="main-menu hdr-menu-main pdng-lt-rt fw hdr-menu-main-inr">
  <div class="container nav-wrapper">
    <div class="logo">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Ceramic Pro">
      </a>
    </div>
    <nav class="nav-links"><?php wp_nav_menu( array( 'menu' => 'Top Menu', 'menu_class' => 'primary-menu' ) ); ?></nav>
    <div class="contact-us-btn">
      <a href="tel:+916364268555" class="btn btn-red">
        <img src="<?php echo esc_url( $phone_icon_url ); ?>" alt="Call">
        +91 63642 68555
      </a>
    </div>
  </div>
</div>
