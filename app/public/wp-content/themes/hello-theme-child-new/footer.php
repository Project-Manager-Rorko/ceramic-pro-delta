<?php
/**
 * Footer template for Hello Elementor Child.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$logo_url = content_url( '/uploads/2026/05/ceramic-logos1.svg' );
$wheel_icon_url = content_url( '/uploads/2026/05/ceramic-logos-2.svg' );
$location_icon_url = content_url( '/uploads/2026/05/locacation-footer1.svg' );
$phone_icon_url = content_url( '/uploads/2026/05/mob-footer1.svg' );
$email_icon_url = content_url( '/uploads/2026/05/email-footer1.svg' );
?>

<footer class="cp-footer-section pdng-lt-rt all-heading">
  <div class="cp-footer-container">
    <div class="cp-top-content-row">
      <div class="cp-brand-block">
        <div class="cp-logo-wrapper">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <img src="<?php echo esc_url( $logo_url ); ?>" alt="Ceramic Pro Mangalore" class="cp-main-logo">
          </a>
        </div>
        <div class="cp-graphic-wrapper desktop-only">
          <img src="<?php echo esc_url( $wheel_icon_url ); ?>" alt="Icon" class="cp-wheel-icon">
        </div>
      </div>

      <div class="cp-link-group">
        <p class="cp-footer-heading">QUICK LINKS</p>
        <ul class="cp-footer-list cp-split-list">
          <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
          <li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Insights</a></li>
          <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About</a></li>
          <li><a href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">Gallery</a></li>
          <li><a href="<?php echo esc_url( home_url( '/ceramic-pro/' ) ); ?>">Ceramic Pro</a></li>
          <li><a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Contact Us</a></li>
        </ul>
      </div>

      <div class="cp-link-group">
        <p class="cp-footer-heading">SERVICES</p>
        <ul class="cp-footer-list">
          <li><a href="<?php echo esc_url( home_url( '/ceramic-coating/' ) ); ?>">Ceramic Coating</a></li>
          <li><a href="<?php echo esc_url( home_url( '/paint-protection-film-ppf/' ) ); ?>">Kavaca PPF</a></li>
          <li><a href="<?php echo esc_url( home_url( '/furniture-coating/' ) ); ?>">Furniture Coating</a></li>
          <li><a href="<?php echo esc_url( home_url( '/interior-cleaning-polishing-conditioning/' ) ); ?>">Cleaning, Polishing & Conditioning</a></li>
          <li><a href="<?php echo esc_url( home_url( '/composite-protection-film-cpf/' ) ); ?>">Composite Protection Film (CPF)</a></li>
        </ul>
      </div>

      <div class="cp-info-group all-heading">
        <p class="cp-footer-heading">STUDIO INFORMATION</p>
        <ul class="cp-info-list">
          <li>
            <span class="cp-icon"><img src="<?php echo esc_url( $location_icon_url ); ?>" alt="Address"></span>
            <span class="all-heading">#G01 Delta House kulur ferry road, kulur Mangalore</span>
          </li>
          <li>
            <span class="cp-icon"><img src="<?php echo esc_url( $phone_icon_url ); ?>" alt="Phone"></span>
            <span class="all-heading">+916364268555</span>
          </li>
          <li>
            <span class="cp-icon"><img src="<?php echo esc_url( $email_icon_url ); ?>" alt="Hours"></span>
            <span class="all-heading">Mon - Sat: 9:00 AM - 7:00 PM</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="cp-bottom-social-strip">
      <div class="cp-social-links">
        <a href="https://www.facebook.com/CeramicProMangalore55/#" rel="noopener noreferrer">Facebook</a>
        <a href="https://www.instagram.com/ceramicpro_mangalore" rel="noopener noreferrer">Instagram</a>
        <a href="https://www.youtube.com/channel/UCrOtdAr6t01GwRzqxnEzwPw" rel="noopener noreferrer">Youtube</a>
      </div>
      <div class="privacy-btm">
        <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a> |
        <a href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>">Terms & Conditions</a>
      </div>
    </div>
  </div>
</footer>

<style>
.cp-footer-section{background-color:#161616;color:#fff;width:100%;max-width:100%;float:left;font-family:"Geist Sans","geist",sans-serif}
.cp-footer-heading{font-size:16px!important;font-weight:700!important;line-height:26px!important;font-family:"geist",sans-serif;text-transform:uppercase;letter-spacing:2.4px;margin-bottom:25px;color:#fff!important;padding-bottom:8px;border-bottom:1px solid #fffc;display:inline-block}
.cp-footer-container{display:flex;flex-direction:column;padding:60px 0 30px;box-sizing:border-box}
.cp-top-content-row{display:grid;grid-template-columns:1fr 1fr 1fr 1.2fr;gap:40px;padding-bottom:50px;align-items:flex-start}
.cp-brand-block{display:flex;flex-direction:column;align-items:flex-start;gap:40px}
.cp-footer-list{list-style:none;padding:0;margin:0;margin-top:16px!important}
.cp-footer-list li{margin-bottom:12px}
.cp-footer-list li a{color:#fffc;text-decoration:none;font-size:clamp(1rem,0.975rem + 0.125vw,1.125rem)!important;transition:color .2s ease}
.cp-footer-list li a:hover{color:#fff}
.cp-split-list{display:grid;grid-template-columns:1fr 1fr;gap:10px 0;margin-top:16px!important}
.cp-info-list{list-style:none;padding:0;margin:0;margin-top:16px!important}
.cp-info-list li{display:flex;align-items:flex-start;gap:12px;margin-bottom:15px;color:#ccc;font-size:14px}
.cp-info-list .cp-icon{font-size:16px;opacity:.8;margin-top:2px}
.cp-bottom-social-strip{border-top:1px solid #FFF3;padding-top:26px;display:flex;justify-content:space-between;width:100%;gap:16px;align-items:flex-start}
.privacy-btm{width:auto;float:left;display:flex;gap:10px;flex-wrap:wrap}
.privacy-btm a{color:#fffc}
.privacy-btm a:hover{color:#fff}
.cp-social-links{display:flex;gap:25px;flex-wrap:wrap}
.privacy{color:#000!important;width:100%;float:left;margin-top:90px}
.privacy.all-heading p,.privacy.all-heading ul li{font-size:clamp(1rem,0.975rem + 0.125vw,1.125rem)!important;font-weight:400!important;line-height:1.5!important;margin:0;font-family:"Geist",Sans-serif!important;margin-bottom:0;letter-spacing:.01em!important;color:#000;margin-bottom:15px;position:relative}
.privacy.all-heading p:last-child,.privacy.all-heading ul li:last-child{margin-bottom:0}
.privacy.all-heading h2{font-size:clamp(1.875rem,1.4464rem + 1.4286vw,2.875rem)!important;font-weight:400!important;line-height:1.2!important;margin:0;display:block;font-family:"Geist",sans-serif!important;letter-spacing:-.05em!important;color:#000;margin-top:50px}
.privacy.all-heading h3{font-size:clamp(1.5rem,1.3746rem + 0.5004vw,1.875rem)!important;font-weight:400!important;line-height:1.4!important;margin:0;display:block;font-family:"Geist",sans-serif!important;letter-spacing:-.05em!important;color:#000;margin-top:20px}
.privacy.all-heading h4{font-size:clamp(1.375rem,1.2679rem + 0.3571vw,1.625rem)!important;font-weight:400!important;line-height:1.4!important;margin:0;display:block;font-family:"Geist",sans-serif!important;letter-spacing:-.05em!important;color:#000;margin-top:20px}
.privacy.all-heading ul li{position:relative;padding-left:20px}
.privacy.all-heading ul li:before{width:6px;height:6px;position:absolute;content:'';left:5px;top:8px;background:#000;border-radius:50%}
.cp-social-links a{color:#FFFC!important;text-decoration:none;font-size:20px;line-height:30px;transition:color .2s ease;opacity:1!important;transform:translate3d(0px,0px,0px)!important}
.cp-social-links a:hover{color:#fff}
.cp-logo-wrapper img{max-width:250px;height:auto}
.cp-graphic-wrapper img{max-width:140px;height:auto;opacity:.9}

@media (max-width:1100px){
  .cp-top-content-row{grid-template-columns:1fr 1fr;gap:40px}
}
@media (max-width:767px){
  .cp-top-content-row{display:block!important;width:100%!important}
  .cp-brand-block,.cp-info-group{width:100%!important;clear:both!important;margin-bottom:26px}
  .cp-link-group{display:block!important;float:left!important;width:50%!important;box-sizing:border-box!important;padding-right:15px;margin-bottom:30px}
  .cp-link-group:nth-of-type(3){margin-bottom:30px}
  .cp-info-group{clear:both!important}
  .cp-footer-list.cp-split-list{display:block!important;grid-template-columns:none!important}
  .cp-footer-list{display:block!important;width:100%!important}
  .cp-footer-list li{width:100%!important;display:block!important;margin-bottom:12px!important}
  .cp-bottom-social-strip{flex-direction:column!important;gap:16px;align-items:center!important}
  .cp-top-content-row{padding-bottom:16px}.cp-brand-block{gap:16px}
}
</style>

<?php get_template_part('template-parts/custom-scripts'); ?>

<?php wp_footer(); ?>
</body>
</html>
