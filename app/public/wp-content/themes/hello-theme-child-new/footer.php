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
<script>
document.addEventListener("DOMContentLoaded", function () {
  const productData = {
    "prod-9h": { title: "CERAMIC PRO 9H", image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-9H.webp", p1: "Ceramic Pro 9H is a Permanent Nano-Ceramic Paint Coating that features a High Gloss finish, unmatched Super Hydrophobic Effect, Scratch Resistance, Chemical Resistance, UV Resistance, Thermal Resistance and Anti-Graffiti.", p2: "Both the Super Hydrophobic and Anti-Graffiti effect combined mean the surface coated with 9H will stay cleaner for longer as dirt and grime will not stick to the surface and the super hydrophobic effect of the coating will cause water to bead up and roll off the surface with any dirt and grime, the hard ceramic film also offers superior protection from damaging contamination and harsh chemicals.", p3: "9H forms a permanent bond to the paint work and will not wash away or break down, 9H can only be removed by abrasion making it a highly durable protective coating to protect your paint work for damaging contaminants.", p4: "The unique formulation of 9H has enabled it to be multi-layered which means the thickness of the coating can be increased with additional layers allowing a thicker/harder film that will increase its scratch resistance.", points: ["9H Hardness", "Super Hydrophobic", "UV Resistance", "High Gloss Finish"] },
    "prod-topcoat": { title: "CERAMIC PRO LIGHT", image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-LIGHT.webp", p1: "Ceramic Pro Light is a protective coating with a durability of up to 24 months that features a High Gloss finish, superior Super Hydrophobic Effect, Chemical Resistance, UV Resistance, Thermal Resistance and Anti-Graffiti.", p2: "Both the Super Hydrophobic and Anti-Graffiti effect combined mean the surface coated with Light will stay cleaner for longer as dirt and grime will not stick to the surface and the super hydrophobic effect of the coating will cause water to bead up and roll off the surface with any dirt and grime.", p3: "The unique formulation of Light enables it to be layered up to 2 times for even more gloss and protection, for best results Ceramic Pro Light can be applied over Ceramic Pro 9H to increase gloss and super hydrophobic effect.", p4: "", points: ["Up to 24 Months", "Anti-Graffiti", "Super Gloss"] },
    "prod-kavaca": { title: "CERAMIC PRO TEXTILE", image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-TEXTILE.webp", p1: "Ceramic Pro Textile comes from the high tech industry of the photoelectron semiconductor assembly which is reliable and an inorganic compound. Ceramic Pro Textile dramatically reduces the surface energy of textile or suede, so that when liquids come into contact with it, they form beads and simply roll off while keeping the textile substrate completely dry.", p2: "This enables the fabric, suede and tissue surface to be free from the water/dust and all other liquids, without affecting the look or feel.", p3: "", p4: "", points: ["Fabric & Suede", "Liquid Repellent", "Keeps Original Feel"] },
    "prod-leather": { title: "CERAMIC PRO LEATHER", image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-LEATHER.webp", p1: "Ceramic Pro Leather is a true protective coating for all leather surfaces. Leather surfaces coating with Ceramic Pro Leather will stay cleaner for longer reducing dirt and grime from becoming ingrained in the leather substrate.", p2: "The Leather coating also features a super hydrophobic effect so that any liquid spills will simply bead up on the surface and can be easily wiped away without affecting the leather substrate.", p3: "The UV Resistance of the coating will help reduce the ageing of the leather from UV damage and keep the leather soft whilst still keeping the factory look and feel.", p4: "", points: ["True Protection", "Spill Repellent", "Prevents Ageing"] },
    "prod-glass": { title: "CERAMIC PRO PLASTIC", image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-PLASTIC.webp", p1: "Ceramic Pro Plastic is a unique coating for plastic and rubber surfaces, suitable for both interior and exterior use. Plastic and rubber surfaces coated with Ceramic Pro Plastic will feature a super hydrophobic effect with excellent wear resistance.", p2: "The coating will add a moderate sheen to the substrate making it a great permanent dressing for both exterior and interior plastics whether they are new or need restoring.", p3: "", p4: "", points: ["Interior & Exterior", "Moderate Sheen", "Wear Resistant"] },
    "prod-wheel": { title: "CERAMIC PRO RAIN", image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-RAIN.webp", p1: "Ceramic Pro Rain is a coating specifically designed for glass with excellent durability without affecting the motion of the front wiper blades. The super hydrophobic effect of the coating means water will simply bead up and run off the glass whilst you are driving.", p2: "Unprotected glass can be a hazard in the rain as water can stick and sheet over the windows decreasing visibility and become a safety hazard. By having Ceramic Pro Rain on all windows this will increase visibility by repelling water and allowing it to bead up and roll straight off the glass, this will also keep the glass cleaner for longer as dirt and grime will no longer stick.", p3: "", p4: "", points: ["Enhanced Visibility", "Water Beads Off", "Excellent Durability"] }
  };

  const productBtns = document.querySelectorAll('.cp-prod-tab-btn');
  const productPanel = document.querySelector('.cp-prod-showcase-panel');
  const productBg = document.getElementById('cpShowcaseBg');
  const productTitle = document.querySelector('.cp-showcase-content-overlay h4');
  const desktopPoints = document.querySelector('.cp-showcase-points-row.desktop-only');
  const mobilePoints = document.querySelector('.cp-showcase-points-row.mobile-only');
  const detailParagraphs = document.querySelectorAll('.cp-prod-details-content-panel > p');

  function renderPoints(container, points) {
    if (!container) return;
    container.innerHTML = '';
    points.forEach(function (point) {
      const wrap = document.createElement('div');
      wrap.className = 'cp-point-tag';
      wrap.innerHTML = '<span>\u2014 </span><p class="wp-block-paragraph">' + point.toUpperCase() + '</p>';
      container.appendChild(wrap);
    });
  }

  function showProduct(key) {
    const data = productData[key];
    if (!data) return;
    if (productPanel) productPanel.style.opacity = '0.3';
    window.setTimeout(function () {
      if (productBg) productBg.style.backgroundImage = "url('" + data.image + "')";
      if (productTitle) productTitle.textContent = data.title;
      const paragraphs = [data.p1, data.p2, data.p3, data.p4];
      detailParagraphs.forEach(function (paragraph, index) {
        const text = paragraphs[index] || '';
        paragraph.textContent = text;
        paragraph.style.display = text ? 'block' : 'none';
      });
      renderPoints(desktopPoints, data.points);
      renderPoints(mobilePoints, data.points);
      if (productPanel) productPanel.style.opacity = '1';
    }, 120);
  }

  if (productBtns.length) {
    productBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        productBtns.forEach(function (item) { item.classList.remove('active'); });
        btn.classList.add('active');
        showProduct(btn.getAttribute('data-target'));
      });
    });
    const activeButton = document.querySelector('.cp-prod-tab-btn.active') || productBtns[0];
    if (activeButton) showProduct(activeButton.getAttribute('data-target'));
  }

  const galleryItems = document.querySelectorAll('.cp-gallery-item');
  const lightbox = document.querySelector('.cp-lightbox-modal');
  if (galleryItems.length && lightbox) {
    let lightboxImg = lightbox.querySelector('.cp-lightbox-content-box img');
    const lightboxContent = lightbox.querySelector('.cp-lightbox-content-box');
    if (lightboxContent && !lightboxImg) {
      lightboxImg = document.createElement('img');
      lightboxImg.alt = 'Gallery preview';
      lightboxContent.appendChild(lightboxImg);
    }

    const closeBtn = lightbox.querySelector('.cp-lightbox-close');
    const leftBtn = lightbox.querySelector('.cp-nav-left');
    const rightBtn = lightbox.querySelector('.cp-nav-right');
    let activeIndex = 0;

    const getSource = function (item) {
      const img = item.querySelector('figure img') || item.querySelector('img');
      return img ? { src: img.getAttribute('src'), alt: img.getAttribute('alt') || 'Gallery preview' } : null;
    };

    const updateImage = function (index) {
      if (!lightboxImg) return;
      const source = getSource(galleryItems[index]);
      if (!source || !source.src) return;
      lightboxImg.style.opacity = '0';
      window.setTimeout(function () {
        lightboxImg.src = source.src;
        lightboxImg.alt = source.alt;
        lightboxImg.style.opacity = '1';
      }, 80);
    };

    const openLightbox = function (index) {
      activeIndex = index;
      document.body.style.overflow = 'hidden';
      lightbox.style.display = 'flex';
      lightbox.offsetHeight;
      lightbox.classList.add('active');
      updateImage(activeIndex);
    };

    const closeLightbox = function () {
      lightbox.classList.remove('active');
      document.body.style.overflow = '';
      window.setTimeout(function () {
        if (!lightbox.classList.contains('active')) {
          lightbox.style.display = 'none';
          if (lightboxImg) lightboxImg.src = '';
        }
      }, 250);
    };

    galleryItems.forEach(function (item, index) {
      item.addEventListener('click', function () {
        openLightbox(index);
      });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (leftBtn) leftBtn.addEventListener('click', function (e) { e.stopPropagation(); activeIndex = (activeIndex - 1 + galleryItems.length) % galleryItems.length; updateImage(activeIndex); });
    if (rightBtn) rightBtn.addEventListener('click', function (e) { e.stopPropagation(); activeIndex = (activeIndex + 1) % galleryItems.length; updateImage(activeIndex); });

    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox || e.target.classList.contains('cp-lightbox-content-box')) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (!lightbox.classList.contains('active')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowRight') rightBtn && rightBtn.click();
      if (e.key === 'ArrowLeft') leftBtn && leftBtn.click();
    });
  }
});
</script>

<?php wp_footer(); ?>
</body>
</html>

