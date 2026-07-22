<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.js"></script>
<script>
/*home-Banner-script*/      
jQuery('.solutions-grid').owlCarousel({
    loop:true,
    margin:15,
    autoplay: true,
    autoplayTimeout: 6000,
    autoplayHoverPause: true,
    touchDrag: true,
    mouseDrag: true,
    smartSpeed: 6000,
    slideTransition: 'linear',
    nav:false,
    dots:false,
    responsive:{
        0:{ items:1.2 },
        600:{ items:2.5 },
        1000:{ items:3.5 },
        1200:{ items:3.5 },
        1400:{ items:3.5 },
        1600:{ items:4.5 }
    }
});


</script> 

<script>
jQuery(document).ready(function () {
  if (jQuery(window).width() > 320) {
    jQuery(window).on('scroll', function () {
      if (jQuery(this).scrollTop() > 0) {
        jQuery('.hdr-menu-main').addClass("fixed-header");
      } else {
        jQuery('.hdr-menu-main').removeClass("fixed-header");
      }
    });
  }
});
</script>
<script>
document.querySelectorAll('.timeline-item').forEach(item => {
  item.addEventListener('click', function() {
    document.querySelectorAll('.timeline-item').forEach(i => i.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    this.classList.add('active');
    this.querySelector('.tab-content').classList.add('active');
  });
});
</script>
<script>
(function() {
    function fixElementorMode() {
        const w = window.innerWidth;
        let mode = "desktop";

        if (w <= 1024) mode = "tablet";
        if (w <= 767) mode = "mobile";

        document.documentElement.setAttribute("data-elementor-device-mode", mode);
    }

    window.addEventListener("resize", fixElementorMode);
    fixElementorMode();
})();
</script>



<script>

document.addEventListener("DOMContentLoaded", () => {

    const canvas = document.getElementById("fluidCanvas");

    if (!canvas) return;

    const gl =
        canvas.getContext("webgl", {
            antialias:true,
            alpha:true
        });

    if (!gl) {
        console.error("WebGL not supported");
        return;
    }

    /* =========================
    SHADERS
    ========================= */

    const vertexShaderSrc = `

        attribute vec2 a_position;

        varying vec2 v_uv;

        void main(){

            v_uv = a_position * 0.5 + 0.5;

            v_uv.y = 1.0 - v_uv.y;

            gl_Position = vec4(a_position,0.0,1.0);
        }

    `;

    const fragmentShaderSrc = `

        precision highp float;

        uniform sampler2D u_image;

        uniform vec2 u_scale;

        uniform float u_aspect;

        uniform vec2 u_mouse[20];

        uniform float u_time;

        uniform float u_strength;

        varying vec2 v_uv;

        void main(){

            vec2 uv = v_uv;

            vec2 distortion = vec2(0.0);

            distortion.x +=
                sin(uv.y * 3.0 + u_time * 0.3) * 0.003;

            distortion.y +=
                cos(uv.x * 3.0 + u_time * 0.3) * 0.003;

            for(int i = 0; i < 20; i++){

                if(u_mouse[i].x < 0.0) continue;

                vec2 aspectUV =
                    uv * vec2(u_aspect,1.0);

                vec2 aspectMouse =
                    u_mouse[i] * vec2(u_aspect,1.0);

                vec2 dir = aspectUV - aspectMouse;

                float dist = length(dir);

                if(dist > 0.0 && dist < 0.4){

                    float trailStrength =
                        float(20 - i) / 20.0;

                    float wave =
                        sin(dist * 20.0 - u_time * 2.8);

                    float falloff =
                        exp(-dist * 14.0);

                    vec2 displacementDir =
                        normalize(dir) /
                        vec2(u_aspect,1.0);

                    distortion +=
                        displacementDir *
                        wave *
                        falloff *
                        0.010 *
                        trailStrength *
                        u_strength;
                }
            }

            vec2 distorted_uv =
                uv + distortion;

            vec2 image_uv =
                (distorted_uv - 0.5) / u_scale + 0.5;

            if(
                image_uv.x < 0.0 ||
                image_uv.x > 1.0 ||
                image_uv.y < 0.0 ||
                image_uv.y > 1.0
            ){

                gl_FragColor =
                    vec4(0.0,0.0,0.0,0.0);

            }else{

                gl_FragColor =
                    texture2D(u_image,image_uv);
            }
        }

    `;

    /* =========================
    COMPILE SHADER
    ========================= */

    function compileShader(type, source){

        const shader = gl.createShader(type);

        gl.shaderSource(shader, source);

        gl.compileShader(shader);

        if(
            !gl.getShaderParameter(
                shader,
                gl.COMPILE_STATUS
            )
        ){
            console.error(
                gl.getShaderInfoLog(shader)
            );
        }

        return shader;
    }

    const vs =
        compileShader(
            gl.VERTEX_SHADER,
            vertexShaderSrc
        );

    const fs =
        compileShader(
            gl.FRAGMENT_SHADER,
            fragmentShaderSrc
        );

    const program = gl.createProgram();

    gl.attachShader(program,vs);

    gl.attachShader(program,fs);

    gl.linkProgram(program);

    gl.useProgram(program);

    /* =========================
    POSITION BUFFER
    ========================= */

    const positionBuffer =
        gl.createBuffer();

    gl.bindBuffer(
        gl.ARRAY_BUFFER,
        positionBuffer
    );

    gl.bufferData(
        gl.ARRAY_BUFFER,
        new Float32Array([
            -1,-1,
             1,-1,
            -1, 1,
            -1, 1,
             1,-1,
             1, 1
        ]),
        gl.STATIC_DRAW
    );

    const posLocation =
        gl.getAttribLocation(
            program,
            "a_position"
        );

    gl.enableVertexAttribArray(posLocation);

    gl.vertexAttribPointer(
        posLocation,
        2,
        gl.FLOAT,
        false,
        0,
        0
    );

    /* =========================
    UNIFORMS
    ========================= */

    const uScaleLoc =
        gl.getUniformLocation(
            program,
            "u_scale"
        );

    const uAspectLoc =
        gl.getUniformLocation(
            program,
            "u_aspect"
        );

    const uMouseLoc =
        gl.getUniformLocation(
            program,
            "u_mouse"
        );

    const uTimeLoc =
        gl.getUniformLocation(
            program,
            "u_time"
        );

    const uStrengthLoc =
        gl.getUniformLocation(
            program,
            "u_strength"
        );

    /* =========================
    TEXTURE
    ========================= */

    const texture =
        gl.createTexture();

    gl.bindTexture(
        gl.TEXTURE_2D,
        texture
    );

    gl.texImage2D(
        gl.TEXTURE_2D,
        0,
        gl.RGBA,
        1,
        1,
        0,
        gl.RGBA,
        gl.UNSIGNED_BYTE,
        new Uint8Array([0,0,0,0])
    );

    gl.texParameteri(
        gl.TEXTURE_2D,
        gl.TEXTURE_WRAP_S,
        gl.CLAMP_TO_EDGE
    );

    gl.texParameteri(
        gl.TEXTURE_2D,
        gl.TEXTURE_WRAP_T,
        gl.CLAMP_TO_EDGE
    );

    gl.texParameteri(
        gl.TEXTURE_2D,
        gl.TEXTURE_MIN_FILTER,
        gl.LINEAR
    );

    /* =========================
    IMAGE
    ========================= */

    let imageAspect = 1;

    const image = new Image();

    image.crossOrigin = "anonymous";

    image.src =
        canvas.getAttribute("data-src");

    image.onload = () => {

        gl.bindTexture(
            gl.TEXTURE_2D,
            texture
        );

        gl.texImage2D(
            gl.TEXTURE_2D,
            0,
            gl.RGBA,
            gl.RGBA,
            gl.UNSIGNED_BYTE,
            image
        );

        imageAspect =
            image.width / image.height;

        resize();
    };

    /* =========================
    MOUSE
    ========================= */

    const mouseTrail =
        new Float32Array(20 * 2).fill(-1);

    let targetMouse = {
        x:-1,
        y:-1
    };

    let currentMouse = {
        x:-1,
        y:-1
    };

    let effectStrength = 1.0;

    function updateMouseTrail(){

        currentMouse.x +=
            (targetMouse.x - currentMouse.x) * 0.1;

        currentMouse.y +=
            (targetMouse.y - currentMouse.y) * 0.1;

        for(let i = 19; i > 0; i--){

            mouseTrail[i*2] =
                mouseTrail[(i-1)*2];

            mouseTrail[i*2+1] =
                mouseTrail[(i-1)*2+1];
        }

        mouseTrail[0] =
            currentMouse.x / canvas.width;

        mouseTrail[1] =
            1.0 - (
                currentMouse.y / canvas.height
            );
    }

    /* =========================
    RESIZE
    ========================= */

    function resize(){

        const rect =
            canvas.parentElement.getBoundingClientRect();

        canvas.width = rect.width;

        canvas.height = rect.height;

        const displayWidth = rect.width;

        const displayHeight = rect.height;

        const canvasAspect =
            displayWidth / displayHeight;

        let targetW = displayWidth;

        let targetH =
            displayWidth / imageAspect;

        if(targetH > displayHeight){

            targetH = displayHeight;

            targetW =
                displayHeight * imageAspect;
        }

        gl.uniform2f(
            uScaleLoc,
            targetW / displayWidth,
            targetH / displayHeight
        );

        gl.uniform1f(
            uAspectLoc,
            canvasAspect
        );

        gl.viewport(
            0,
            0,
            canvas.width,
            canvas.height
        );
    }

    /* =========================
    RENDER
    ========================= */

    const startTime = Date.now();

    function render(){

        updateMouseTrail();

        const time =
            (Date.now() - startTime) / 1000;

        gl.clearColor(0,0,0,0);

        gl.clear(gl.COLOR_BUFFER_BIT);

        gl.uniform1f(uTimeLoc,time);

        gl.uniform2fv(
            uMouseLoc,
            mouseTrail
        );

        gl.uniform1f(
            uStrengthLoc,
            effectStrength
        );

        gl.enable(gl.BLEND);

        gl.blendFunc(
            gl.SRC_ALPHA,
            gl.ONE_MINUS_SRC_ALPHA
        );

        gl.drawArrays(
            gl.TRIANGLES,
            0,
            6
        );

        requestAnimationFrame(render);
    }

    /* =========================
    EVENTS
    ========================= */

    window.addEventListener(
        "mousemove",
        (e)=>{

            const rect =
                canvas.getBoundingClientRect();

            targetMouse.x =
                e.clientX - rect.left;

            targetMouse.y =
                e.clientY - rect.top;
        }
    );

    window.addEventListener(
        "resize",
        resize
    );

    render();

});

</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const items = document.querySelectorAll(".cp-faq-item");

    // NEW: Force all items to be closed on initial page load
    items.forEach((item) => {
      item.classList.remove("active");
      const content = item.querySelector(".cp-faq-content");
      content.style.height = "0px";
      content.style.overflow = "hidden"; // Ensures text doesn't spill out
    });

    // Your existing click logic
    items.forEach((item) => {
      const trigger = item.querySelector(".cp-faq-trigger");
      const content = item.querySelector(".cp-faq-content");
      
      trigger.addEventListener("click", function () {
        const isActive = item.classList.contains("active");
        
        // Close all items
        items.forEach((i) => {
          i.classList.remove("active");
          i.querySelector(".cp-faq-content").style.height = "0px";
        });
        
        // Open the clicked one if it wasn't already active
        if (!isActive) {
          item.classList.add("active");
          content.style.height = content.scrollHeight + "px";
        }
      });
    });
  });
</script>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Find all links inside the standard WordPress/Elementor menu
    const menuLinks = document.querySelectorAll('.menu-item a');
    
    menuLinks.forEach(link => {
        // Skip if it's already been wrapped (prevents glitches)
        if (link.querySelector('.rolling-text-wrap')) return;
        
        // Get the original text
        const text = link.innerText.trim();
        
        if (text) {
            // Replace text with the rolling text span structure
            link.innerHTML = `
                <div class="rolling-text-wrap">
                    <span>${text}</span>
                    <span>${text}</span>
                </div>
            `;
        }
    });
});
</script>

<!-- <script>
document.addEventListener("DOMContentLoaded", function () {
  const productData = {
    "prod-9h": {
      title: "CERAMIC PRO 9H",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-9H.webp",
      p1: "Ceramic Pro 9H is a Permanent Nano-Ceramic Paint Coating that features a High Gloss finish, unmatched Super Hydrophobic Effect, Scratch Resistance, Chemical Resistance, UV Resistance, Thermal Resistance and Anti-Graffiti.",
      p2: "Both the Super Hydrophobic and Anti-Graffiti effect combined mean the surface coated with 9H will stay cleaner for longer as dirt and grime will not stick to the surface and the super hydrophobic effect of the coating will cause water to bead up and roll off the surface with any dirt and grime, the hard ceramic film also offers superior protection from damaging contamination and harsh chemicals.",
      p3: "9H forms a permanent bond to the paint work and will not wash away or break down, 9H can only be removed by abrasion making it a highly durable protective coating to protect your paint work for damaging contaminants.",
      p4: "The unique formulation of 9H has enabled it to be multi-layered which means the thickness of the coating can be increased with additional layers allowing a thicker/harder film that will increase its scratch resistance.",
      pointsConfig: [
        { text: "— 9H Hardness"},
        { text: "— Super Hydrophobic" },
        { text: "— UV Resistance" },
        { text: "— High Gloss Finish" }
      ],
    },
    "prod-topcoat": {
      title: "CERAMIC PRO LIGHT",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-LIGHT.webp",
      p1: "Ceramic Pro Light is a protective coating with a durability of up to 24 months that features a High Gloss finish, superior Super Hydrophobic Effect, Chemical Resistance, UV Resistance, Thermal Resistance and Anti-Graffiti.",
      p2: "Both the Super Hydrophobic and Anti-Graffiti effect combined mean the surface coated with Light will stay cleaner for longer as dirt and grime will not stick to the surface and the super hydrophobic effect of the coating will cause water to bead up and roll off the surface with any dirt and grime.",
      p3: "The unique formulation of Light enables it to be layered up to 2 times for even more gloss and protection, for best results Ceramic Pro Light can be applied over Ceramic Pro 9H to increase gloss and super hydrophobic effect.",
      p4: "",
      pointsConfig: [
        { text: "— Up to 24 Months" },
        { text: "— Anti-Graffiti" },
        { text: "— Super Gloss" }
      ],
    },
    "prod-kavaca": {
      title: "CERAMIC PRO TEXTILE",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-TEXTILE.webp",
      p1: "Ceramic Pro Textile comes from the high tech industry of the photoelectron semiconductor assembly which is reliable and an inorganic compound. Ceramic Pro Textile dramatically reduces the surface energy of textile or suede, so that when liquids come into contact with it, they form beads and simply roll off while keeping the textile substrate completely dry.",
      p2: "This enables the fabric, suede and tissue surface to be free from the water/dust and all other liquids, without affecting the look or feel.",
      p3: "",
      p4: "",
      pointsConfig: [
        { text: "— Fabric & Suede" },
        { text: "— Liquid Repellent" },
        { text: "— Keeps Original Feel"}
      ],
    },
    "prod-leather": {
      title: "CERAMIC PRO LEATHER",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-LEATHER.webp",
      p1: "Ceramic Pro Leather is a true protective coating for all leather surfaces. Leather surfaces coating with Ceramic Pro Leather will stay cleaner for longer reducing dirt and grime from becoming ingrained in the leather substrate.",
      p2: "The Leather coating also features a super hydrophobic effect so that any liquid spills will simply bead up on the surface and can be easily wiped away without affecting the leather substrate.",
      p3: "The UV Resistance of the coating will help reduce the ageing of the leather from UV damage and keep the leather soft whilst still keeping the factory look and feel.",
      p4: "",
      pointsConfig: [
        { text: "— True Protection" },
        { text: "— Spill Repellent" },
        { text: "— Prevents Ageing" }
      ],
    },
    "prod-glass": {
      title: "CERAMIC PRO PLASTIC",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-PLASTIC.webp",
      p1: "Ceramic Pro Plastic is a unique coating for plastic and rubber surfaces, suitable for both interior and exterior use. Plastic and rubber surfaces coated with Ceramic Pro Plastic will feature a super hydrophobic effect with excellent wear resistance.",
      p2: "The coating will add a moderate sheen to the substrate making it a great permanent dressing for both exterior and interior plastics whether they are new or need restoring.",
      p3: "",
      p4: "",
      pointsConfig: [
        { text: "— Interior & Exterior" },
        { text: "— Moderate Sheen" },
        { text: "— Wear Resistant" }
      ],
    },
    "prod-wheel": {
      title: "CERAMIC PRO RAIN",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-RAIN.webp",
      p1: "Ceramic Pro Rain is a coating specifically designed for glass with excellent durability without affecting the motion of the front wiper blades. The super hydrophobic effect of the coating means water will simply bead up and run off the glass whilst you are driving.",
      p2: "Unprotected glass can be a hazard in the rain as water can stick and sheet over the windows decreasing visibility and become a safety hazard. By having Ceramic Pro Rain on all windows this will increase visibility by repelling water and allowing it to bead up and roll straight off the glass, this will also keep the glass cleaner for longer as dirt and grime will no longer stick.",
      p3: "",
      p4: "",
      pointsConfig: [
        { text: "— Enhanced Visibility" },
        { text: "— Water Beads Off" },
        { text: "— Excellent Durability" }
      ],
    },
  };

  const tabBtns = document.querySelectorAll(".cp-prod-tab-btn");
  const showcasePanel = document.getElementById("cpProdShowcasePanel");
  const showcaseBg = document.getElementById("cpShowcaseBg");
  const showcaseTitle = document.getElementById("cpShowcaseTitle");
  const showcasePoints = document.getElementById("cpShowcasePoints");
  const showcaseP1 = document.getElementById("cpShowcaseParagraphOne");
  const showcaseP2 = document.getElementById("cpShowcaseParagraphTwo");
  const showcaseP3 = document.getElementById("cpShowcaseParagraphThree");
  const showcaseP4 = document.getElementById("cpShowcaseParagraphFour");

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      if (this.classList.contains("active")) return;

      tabBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");

      const targetId = this.getAttribute("data-target");
      const data = productData[targetId];

      if (data) {
        showcasePanel.style.opacity = "0.3";

        setTimeout(() => {
          showcaseBg.style.backgroundImage = `url('${data.image}')`;
          showcaseTitle.textContent = data.title;
          showcaseP1.textContent = data.p1;
          
          if (showcaseP2) {
            showcaseP2.textContent = data.p2 ? data.p2 : "";
            showcaseP2.style.display = data.p2 ? "block" : "none";
          }
          
          if (showcaseP3) {
            showcaseP3.textContent = data.p3 ? data.p3 : "";
            showcaseP3.style.display = data.p3 ? "block" : "none";
          }

          if (showcaseP4) {
            showcaseP4.textContent = data.p4 ? data.p4 : "";
            showcaseP4.style.display = data.p4 ? "block" : "none";
          }
          
          showcasePoints.innerHTML = "";

          data.pointsConfig.forEach((item) => {
            const pointDiv = document.createElement("div");
            pointDiv.className = "cp-point-tag";
            pointDiv.innerHTML = `<span></span> ${item.text.toUpperCase()}`;
            showcasePoints.appendChild(pointDiv);
          });

          showcasePanel.style.opacity = "1";
        }, 180);
      }
    });
  });
});
</script> -->


<script>
document.addEventListener("DOMContentLoaded", function () {
  const productData = {
    "prod-9h": {
      title: "CERAMIC PRO 9H",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-9H.webp",
      p1: "Ceramic Pro 9H is a Permanent Nano-Ceramic Paint Coating that features a High Gloss finish, unmatched Super Hydrophobic Effect, Scratch Resistance, Chemical Resistance, UV Resistance, Thermal Resistance and Anti-Graffiti.",
      p2: "Both the Super Hydrophobic and Anti-Graffiti effect combined mean the surface coated with 9H will stay cleaner for longer as dirt and grime will not stick to the surface and the super hydrophobic effect of the coating will cause water to bead up and roll off the surface with any dirt and grime, the hard ceramic film also offers superior protection from damaging contamination and harsh chemicals.",
      p3: "9H forms a permanent bond to the paint work and will not wash away or break down, 9H can only be removed by abrasion making it a highly durable protective coating to protect your paint work for damaging contaminants.",
      p4: "The unique formulation of 9H has enabled it to be multi-layered which means the thickness of the coating can be increased with additional layers allowing a thicker/harder film that will increase its scratch resistance.",
      pointsConfig: [
        { text: "— 9H Hardness"},
        { text: "— Super Hydrophobic" },
        { text: "— UV Resistance" },
        { text: "— High Gloss Finish" }
      ],
    },
    "prod-topcoat": {
      title: "CERAMIC PRO LIGHT",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-LIGHT.webp",
      p1: "Ceramic Pro Light is a protective coating with a durability of up to 24 months that features a High Gloss finish, superior Super Hydrophobic Effect, Chemical Resistance, UV Resistance, Thermal Resistance and Anti-Graffiti.",
      p2: "Both the Super Hydrophobic and Anti-Graffiti effect combined mean the surface coated with Light will stay cleaner for longer as dirt and grime will not stick to the surface and the super hydrophobic effect of the coating will cause water to bead up and roll off the surface with any dirt and grime.",
      p3: "The unique formulation of Light enables it to be layered up to 2 times for even more gloss and protection, for best results Ceramic Pro Light can be applied over Ceramic Pro 9H to increase gloss and super hydrophobic effect.",
      p4: "",
      pointsConfig: [
        { text: "— Up to 24 Months" },
        { text: "— Anti-Graffiti" },
        { text: "— Super Gloss" }
      ],
    },
    "prod-kavaca": {
      title: "CERAMIC PRO TEXTILE",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-TEXTILE.webp",
      p1: "Ceramic Pro Textile comes from the high tech industry of the photoelectron semiconductor assembly which is reliable and an inorganic compound. Ceramic Pro Textile dramatically reduces the surface energy of textile or suede, so that when liquids come into contact with it, they form beads and simply roll off while keeping the textile substrate completely dry.",
      p2: "This enables the fabric, suede and tissue surface to be free from the water/dust and all other liquids, without affecting the look or feel.",
      p3: "",
      p4: "",
      pointsConfig: [
        { text: "— Fabric & Suede" },
        { text: "— Liquid Repellent" },
        { text: "— Keeps Original Feel"}
      ],
    },
    "prod-leather": {
      title: "CERAMIC PRO LEATHER",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-LEATHER.webp",
      p1: "Ceramic Pro Leather is a true protective coating for all leather surfaces. Leather surfaces coating with Ceramic Pro Leather will stay cleaner for longer reducing dirt and grime from becoming ingrained in the leather substrate.",
      p2: "The Leather coating also features a super hydrophobic effect so that any liquid spills will simply bead up on the surface and can be easily wiped away without affecting the leather substrate.",
      p3: "The UV Resistance of the coating will help reduce the ageing of the leather from UV damage and keep the leather soft whilst still keeping the factory look and feel.",
      p4: "",
      pointsConfig: [
        { text: "— True Protection" },
        { text: "— Spill Repellent" },
        { text: "— Prevents Ageing" }
      ],
    },
    "prod-glass": {
      title: "CERAMIC PRO PLASTIC",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-PLASTIC.webp",
      p1: "Ceramic Pro Plastic is a unique coating for plastic and rubber surfaces, suitable for both interior and exterior use. Plastic and rubber surfaces coated with Ceramic Pro Plastic will feature a super hydrophobic effect with excellent wear resistance.",
      p2: "The coating will add a moderate sheen to the substrate making it a great permanent dressing for both exterior and interior plastics whether they are new or need restoring.",
      p3: "",
      p4: "",
      pointsConfig: [
        { text: "— Interior & Exterior" },
        { text: "— Moderate Sheen" },
        { text: "— Wear Resistant" }
      ],
    },
    "prod-wheel": {
      title: "CERAMIC PRO RAIN",
      image: "/wp-content/uploads/2026/05/CER-PRO-PRODUCT-PAGE-CERAMIC-PRO-RAIN.webp",
      p1: "Ceramic Pro Rain is a coating specifically designed for glass with excellent durability without affecting the motion of the front wiper blades. The super hydrophobic effect of the coating means water will simply bead up and run off the glass whilst you are driving.",
      p2: "Unprotected glass can be a hazard in the rain as water can stick and sheet over the windows decreasing visibility and become a safety hazard. By having Ceramic Pro Rain on all windows this will increase visibility by repelling water and allowing it to bead up and roll straight off the glass, this will also keep the glass cleaner for longer as dirt and grime will no longer stick.",
      p3: "",
      p4: "",
      pointsConfig: [
        { text: "— Enhanced Visibility" },
        { text: "— Water Beads Off" },
        { text: "— Excellent Durability" }
      ],
    },
  };

  const tabBtns = document.querySelectorAll(".cp-prod-tab-btn");
  const showcasePanel = document.getElementById("cpProdShowcasePanel");
  const showcaseBg = document.getElementById("cpShowcaseBg");
  const showcaseTitle = document.getElementById("cpShowcaseTitle");
  
  // Mapped Selectors for both desktop and mobile specification areas
  const showcasePointsDesktop = document.getElementById("cpShowcasePointsDesktop");
  const showcasePointsMobile = document.getElementById("cpShowcasePointsMobile");
  
  const showcaseP1 = document.getElementById("cpShowcaseParagraphOne");
  const showcaseP2 = document.getElementById("cpShowcaseParagraphTwo");
  const showcaseP3 = document.getElementById("cpShowcaseParagraphThree");
  const showcaseP4 = document.getElementById("cpShowcaseParagraphFour");

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      if (this.classList.contains("active")) return;

      tabBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");

      const targetId = this.getAttribute("data-target");
      const data = productData[targetId];

      if (data) {
        showcasePanel.style.opacity = "0.3";

        setTimeout(() => {
          showcaseBg.style.backgroundImage = `url('${data.image}')`;
          showcaseTitle.textContent = data.title;
          showcaseP1.textContent = data.p1;
          
          if (showcaseP2) {
            showcaseP2.textContent = data.p2 ? data.p2 : "";
            showcaseP2.style.display = data.p2 ? "block" : "none";
          }
          
          if (showcaseP3) {
            showcaseP3.textContent = data.p3 ? data.p3 : "";
            showcaseP3.style.display = data.p3 ? "block" : "none";
          }

          if (showcaseP4) {
            showcaseP4.textContent = data.p4 ? data.p4 : "";
            showcaseP4.style.display = data.p4 ? "block" : "none";
          }
          
          // Clear older elements out of both view container wrappers
          if (showcasePointsDesktop) showcasePointsDesktop.innerHTML = "";
          if (showcasePointsMobile) showcasePointsMobile.innerHTML = "";

          // Populate text points into both view targets contextually
          data.pointsConfig.forEach((item) => {
            const clearText = item.text.replace(/^—\s*/, ''); // Cleans extra hyphens out seamlessly
            
            if (showcasePointsDesktop) {
              const dPoint = document.createElement("div");
              dPoint.className = "cp-point-tag";
              dPoint.innerHTML = `<span>— </span> ${clearText.toUpperCase()}`;
              showcasePointsDesktop.appendChild(dPoint);
            }
            
            if (showcasePointsMobile) {
              const mPoint = document.createElement("div");
              mPoint.className = "cp-point-tag";
              mPoint.innerHTML = `<span>— </span> ${clearText.toUpperCase()}`;
              showcasePointsMobile.appendChild(mPoint);
            }
          });

          showcasePanel.style.opacity = "1";
        }, 180);
      }
    });
  });
});
</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const items = document.querySelectorAll(".cp-gallery-item");
    const lightbox = document.getElementById("cpGlobalLightbox");
    const lightboxImg = document.getElementById("cpLightboxMainImage");
    const closeBtn = document.querySelector(".cp-lightbox-close");
    const leftBtn = document.querySelector(".cp-nav-left");
    const rightBtn = document.querySelector(".cp-nav-right");

    if (!lightbox || !lightboxImg || !closeBtn || !leftBtn || !rightBtn || !items.length) {
      return;
    }
    
    let activeIndex = 0;

    const updateDisplayImage = (index) => {
      lightboxImg.style.opacity = "0";
      setTimeout(() => {
        const targetSrc = items[index].querySelector("img:not(.cp-gallery-icon-wrapper img)").getAttribute("src");
        lightboxImg.src = targetSrc;
        lightboxImg.style.opacity = "1";
      }, 100);
    };

    items.forEach((element, index) => {
      element.addEventListener("click", function () {
        activeIndex = index;
        lightbox.style.display = "flex";
        lightbox.offsetHeight; 
        lightbox.classList.add("active");
        document.body.style.overflow = "hidden";
        updateDisplayImage(activeIndex);
      });
    });

    const closeGalleryView = () => {
      lightbox.classList.remove("active");
      document.body.style.overflow = "";
      setTimeout(() => {
        lightbox.style.display = "none";
        lightboxImg.src = "";
      }, 300);
    };

    const handleNextSlide = (e) => {
      e.stopPropagation();
      activeIndex = (activeIndex + 1) % items.length;
      updateDisplayImage(activeIndex);
    };

    const handlePrevSlide = (e) => {
      e.stopPropagation();
      activeIndex = (activeIndex - 1 + items.length) % items.length;
      updateDisplayImage(activeIndex);
    };

    rightBtn.addEventListener("click", handleNextSlide);
    leftBtn.addEventListener("click", handlePrevSlide);
    closeBtn.addEventListener("click", closeGalleryView);
    
    lightbox.addEventListener("click", function (e) {
      if (e.target === lightbox || e.target.classList.contains("cp-lightbox-content-box")) {
        closeGalleryView();
      }
    });

    document.addEventListener("keydown", function (e) {
      if (!lightbox.classList.contains("active")) return;
      if (e.key === "Escape") closeGalleryView();
      if (e.key === "ArrowRight" || e.key === "Right") rightBtn.click();
      if (e.key === "ArrowLeft" || e.key === "Left") leftBtn.click();
    });
  });
</script>
<script>
    document.querySelectorAll('.cp-prod-tab-btn').forEach(function(button) {
      button.addEventListener('click', function() {
        this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      });
    });
  </script>
<script>
  window.addEventListener('DOMContentLoaded', () => {
      const track = document.getElementById('continuous-ticker-track');
      if (!track) return; // Safeguard if ticker element is absent on the page
      
      const originalContent = track.innerHTML;
      track.innerHTML = originalContent + originalContent + originalContent + originalContent;

      let translationPosition = 0;
      const velocitySpeed = 1.0;

      function animateTickerLoop() {
          translationPosition -= velocitySpeed;
          if (Math.abs(translationPosition) >= (track.scrollWidth / 2)) {
              translationPosition = 0;
          }
          // Fix: Added proper backticks and string template syntax structure
          track.style.transform = `translateX(${translationPosition}px)`;
          requestAnimationFrame(animateTickerLoop);
      }
      requestAnimationFrame(animateTickerLoop);
  });
</script>
<script>
    const waCloseBtn = document.querySelector('.whatsapp-popup-close');
    if(waCloseBtn) {
	    waCloseBtn.addEventListener('click', function() {
            const waBox = document.querySelector('.whatsapp-popup-box');
            if(waBox) waBox.style.display = 'none';
        });
    }
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const serviceRows = document.querySelectorAll('.service-row-item');
  const captionTarget = document.querySelector('.image-caption-overlay p');
  
  // Select the button and the specific text span inside the button
  const discoverBtn = document.querySelector('.discover-process-btn');
  const discoverBtnText = document.querySelector('.discover-process-btn .btn-text');

  // 1. Master Data Dictionary: Controls Captions, Links, and Button Text
  const serviceData = {
    "pane-01": {
      caption: "Deep gloss. Long-lasting protection.",
      link: "/ceramic-coating/",
      btnText: "Discover Process"
    },
    "pane-02": {
      caption: "Flexible shield. Lasting brilliance.",
      link: "/paint-protection-film-ppf/",
      btnText: "Discover Process"
    },
    "pane-03": {
      caption: "Restore clarity. Enhance comfort.",
      link: "/interior-cleaning-polishing-conditioning/",
      btnText: "Discover Process"
    },
    "pane-04": {
      caption: "Maximum protection. Invisible defense.",
      link: "/composite-protection-film-cpf/",
      btnText: "Discover Process"
    },
    "pane-05": {
      caption: "Elegant finish. Lasting protection.",
      link: "/furniture-coating/",
      btnText: "Discover Process"
    }
  };

  serviceRows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      // Manage Active Highlight Rows Class
      serviceRows.forEach(r => r.classList.remove('active-row'));
      this.classList.add('active-row');

      const targetPaneId = this.getAttribute('data-target');
      const data = serviceData[targetPaneId];

      // 2. Change Left-Side Image Pane
      document.querySelectorAll('.service-view-pane').forEach(pane => {
        pane.classList.remove('active-pane');
      });
      const activePane = document.getElementById(targetPaneId);
      if (activePane) {
        activePane.classList.add('active-pane');
      }

      // 3. Dynamically update Text, Button Link, and Button Text
      if (data) {
        // Update the button URL
        if (discoverBtn) discoverBtn.setAttribute('href', data.link);
        
        // Update the button text
        if (discoverBtnText) discoverBtnText.textContent = data.btnText;

        // Update the paragraph caption smoothly
        if (captionTarget) {
          captionTarget.style.opacity = "0";
          setTimeout(() => {
            captionTarget.textContent = data.caption;
            captionTarget.style.opacity = "1";
          }, 150);
        }
      }
    });
  });
});
</script>

<!-- <script>
document.addEventListener("DOMContentLoaded", () => {
  const serviceRows = document.querySelectorAll('.service-row-item');
  const captionTarget = document.querySelector('.image-caption-overlay p');

  // Text Dictionary
  const serviceCaptions = {
    "pane-01": "Deep gloss. Long-lasting protection.",
    "pane-02": "Flexible shield. Lasting brilliance.",
    "pane-03": "Restore clarity. Enhance comfort.",
    "pane-04": "Maximum protection. Invisible defense.",
    "pane-05": "Elegant finish. Lasting protection."
  };

  serviceRows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      // 1. Manage Active Highlight Rows Class
      serviceRows.forEach(r => r.classList.remove('active-row'));
      this.classList.add('active-row');

      const targetPaneId = this.getAttribute('data-target');

      // 2. Change Left-Side Image Pane Visibility
      document.querySelectorAll('.service-view-pane').forEach(pane => {
        pane.classList.remove('active-pane');
      });
      const activePane = document.getElementById(targetPaneId);
      if (activePane) {
        activePane.classList.add('active-pane');
      }

      // 3. Toggle the correct Discover Process Button
      document.querySelectorAll('.discover-process-btn').forEach(btn => {
        btn.style.display = 'none'; // Hide all 5 buttons
      });
      const activeBtn = document.querySelector(`.discover-process-btn[data-btn="${targetPaneId}"]`);
      if (activeBtn) {
        activeBtn.style.display = ''; // Reveal the matched button
      }

      // 4. Change Text Content Smoothly
      if (captionTarget && serviceCaptions[targetPaneId]) {
        captionTarget.style.opacity = "0";
        setTimeout(() => {
          captionTarget.textContent = serviceCaptions[targetPaneId];
          captionTarget.style.opacity = "1";
        }, 150);
      }
    });
  });
});
</script> -->

<script>
document.addEventListener("DOMContentLoaded", () => {
    const trackContainer = document.getElementById("automated-marquee-track");
    const sliderWindow = document.getElementById("slider-viewport-container");

    // Safety check to prevent console errors if elements are missing
    if (!trackContainer || !sliderWindow) return;

    const baseElementsHTML = trackContainer.innerHTML;
    trackContainer.innerHTML = baseElementsHTML + baseElementsHTML + baseElementsHTML;

    let currentOffsetPosition = 0;
    const scrollVelocityStep = 0.8;
    let pauseStateTrigger = false;

    // Small timeout ensures CSS has time to apply widths before math starts
    setTimeout(() => {
        function executionMarqueeLoop() {
            if (!pauseStateTrigger) {
                currentOffsetPosition -= scrollVelocityStep;
                if (Math.abs(currentOffsetPosition) >= trackContainer.scrollWidth / 3) {
                    currentOffsetPosition = 0;
                }
                // translate3d forces hardware acceleration for smoother animation
                trackContainer.style.transform = `translate3d(${currentOffsetPosition}px, 0, 0)`;
            }
            requestAnimationFrame(executionMarqueeLoop);
        }

        sliderWindow.addEventListener("mouseenter", () => pauseStateTrigger = true);
        sliderWindow.addEventListener("mouseleave", () => pauseStateTrigger = false);
        sliderWindow.addEventListener("touchstart", () => pauseStateTrigger = true, { passive: true });
        sliderWindow.addEventListener("touchend", () => pauseStateTrigger = false, { passive: true });

        requestAnimationFrame(executionMarqueeLoop);
    }, 100); 
});
</script>


<style>

/* =========================================
LENIS
========================================= */

html.lenis,
html.lenis body {
    height: auto;
}

.lenis.lenis-smooth {
    scroll-behavior: auto !important;
}

.lenis.lenis-smooth [data-lenis-prevent] {
    overscroll-behavior: contain;
}

/* =========================================
TEXT REVEAL
========================================= */

.bsi-reveal {
    visibility: hidden;
}

/* Line wrapper */

.bsi-line-wrap {
    display: block;
    overflow: hidden;
}

/* Split line */

.bsi-reveal .line {
    display: block;
    will-change: transform;
}

/* Inline elements */

a.bsi-reveal,
button.bsi-reveal,
span.bsi-reveal {
    display: inline-block;
}

</style>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // =========================================
    // REGISTER GSAP
    // =========================================

    gsap.registerPlugin(ScrollTrigger);

    // =========================================
    // PREVENT SCROLL JUMP
    // =========================================

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    // =========================================
    // LENIS INIT
    // =========================================

    const lenis = new Lenis({
        duration: 1.2,
        smoothWheel: true,
        smoothTouch: false,
        easing: (t) => 1 - Math.pow(1 - t, 4)
    });

    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }

    requestAnimationFrame(raf);

    // =========================================
    // LENIS + SCROLLTRIGGER
    // =========================================

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => {
        lenis.raf(time * 1000);
    });

    gsap.ticker.lagSmoothing(0);

    // =========================================
    // AUTO TARGET ELEMENTS
    // =========================================

    const revealElements = document.querySelectorAll(`
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        li,
        blockquote,
        .btn,
        button,
        a,
        a.elementor-button
    `);

    revealElements.forEach(el => {

        // =========================================
        // SKIP FOOTER SECTION
        // =========================================

        if (el.closest('.cp-bottom-social-strip')) return;

        // =========================================
        // SKIP EMPTY ELEMENTS
        // =========================================

        if (!el.textContent.trim()) return;

        // =========================================
        // PREVENT DUPLICATES
        // =========================================

        if (el.classList.contains('bsi-reveal')) return;

        // =========================================
        // SKIP NESTED ELEMENTS
        // =========================================

        if (el.closest('.bsi-reveal')) return;

        // =========================================
        // ADD REVEAL CLASS
        // =========================================

        el.classList.add('bsi-reveal');

    });

    // =========================================
    // WAIT FOR FONTS
    // =========================================

    document.fonts.ready.then(() => {

        const animatedElements = document.querySelectorAll('.bsi-reveal');

        animatedElements.forEach((el) => {

            // =========================================
            // SHOW ELEMENT
            // =========================================

            gsap.set(el, {
                autoAlpha: 1
            });

            // =========================================
            // HEADINGS
            // =========================================

            if (el.matches('h1,h2,h3,h4,h5,h6')) {

                const split = new SplitType(el, {
                    types: 'lines',
                    tagName: 'span'
                });

                split.lines.forEach((line) => {

                    const wrapper = document.createElement('span');

                    wrapper.classList.add('bsi-line-wrap');

                    line.parentNode.insertBefore(wrapper, line);

                    wrapper.appendChild(line);

                });

                gsap.from(split.lines, {

                    yPercent: 120,
                    opacity: 0,
                    duration: 1.2,
                    stagger: 0.12,
                    ease: "power4.out",
                    force3D: true,

                    scrollTrigger: {
                        trigger: el,
                        start: "top 92%",
                        once: true
                    },

                    onComplete: () => {

                        // =========================================
                        // CLEAN HTML AFTER ANIMATION
                        // =========================================

                        split.revert();

                    }

                });

            }

            // =========================================
            // TEXT / BUTTONS / LINKS
            // =========================================

            else {

                gsap.from(el, {

                    y: 40,
                    opacity: 0,
                    duration: 1,
                    ease: "power3.out",
                    force3D: true,

                    scrollTrigger: {
                        trigger: el,
                        start: "top 94%",
                        once: true
                    }

                });

            }

        });

        // =========================================
        // REFRESH
        // =========================================

        setTimeout(() => {
            ScrollTrigger.refresh();
        }, 300);

    });

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const grid = document.querySelector(".portfolio-mosaic-grid");
    if (!grid) return;

    let scrollSpeed = 1; // Speed setting: Higher = faster, Lower = slower/smoother
    let animationFrameId = null;
    let isCloned = false;
    let isUserTouching = false;
    let resumeTimeout = null;

    // 1. Clone the gallery elements to build a seamless infinite loop track natively
    function setupMarqueeTrack() {
        if (window.innerWidth <= 768 && !isCloned) {
            const originalItems = Array.from(grid.children);
            originalItems.forEach(item => {
                const clone = item.cloneNode(true);
                grid.appendChild(clone);
            });
            isCloned = true;
        }
    }

    // 2. High-performance animation loop running natively on hardware refresh ticks
    function renderMarqueeCrawl() {
        if (window.innerWidth <= 768) {
            // Only auto-scroll if the user is NOT manually dragging/touching the grid
            if (!isUserTouching) {
                grid.scrollLeft += scrollSpeed;
            }

            // Infinite loop handling: 
            // If the container is scrolled manually or automatically past the halfway mark, 
            // snap or wrap around seamlessly to keep infinite items available.
            const halfWidth = grid.scrollWidth / 2;
            if (grid.scrollLeft >= halfWidth) {
                grid.scrollLeft -= halfWidth; // Subtract instead of setting to 0 to preserve manual scroll momentum
            } else if (grid.scrollLeft <= 0) {
                grid.scrollLeft += halfWidth; // Allows flawless manual scrolling backwards too!
            }

            // Request the next frame iteration from the browser engine safely
            animationFrameId = requestAnimationFrame(renderMarqueeCrawl);
        }
    }

    // 3. Orchestration engine to start/stop animations based on active screens
    function runEngineControlPipeline() {
        if (window.innerWidth <= 768) {
            setupMarqueeTrack();
            if (!animationFrameId) {
                animationFrameId = requestAnimationFrame(renderMarqueeCrawl);
            }
        } else {
            // Desktop safety cleanup routines
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
            grid.scrollLeft = 0; // Reset position cleanly for desktop grid views
        }
    }

    // --- SMOOTH MANUAL TOUCH INTERACTION HANDLERS ---
    
    grid.addEventListener("touchstart", () => {
        isUserTouching = true;
        clearTimeout(resumeTimeout); // Clear any pending resume timeouts
    }, { passive: true });

    grid.addEventListener("touchmove", () => {
        isUserTouching = true; // Safety guard during active dragging
    }, { passive: true });

    grid.addEventListener("touchend", () => {
        clearTimeout(resumeTimeout);
        // Wait 1.5 seconds after user releases their finger before starting auto-scroll again
        resumeTimeout = setTimeout(() => {
            isUserTouching = false;
        }, 1500);
    }, { passive: true });

    // 4. Initial startup boots and resize event hooks
    runEngineControlPipeline();
    window.addEventListener("resize", runEngineControlPipeline);
});
</script>
<!-- <script>
  document.addEventListener("DOMContentLoaded", () => {
    const track1 = document.getElementById('portfolio-track-row-1');
    const track2 = document.getElementById('portfolio-track-row-2');

    if (!track1 || !track2) return;

    // Clone list nodes to establish seamless infinite loop margins
    track1.innerHTML += track1.innerHTML;
    track2.innerHTML += track2.innerHTML;

    let posRow1 = 0;
    let posRow2 = 0;
    const scrollSpeed = 0.8; // Control velocity tracking speed metrics here
    
    // NEW: Add a variable to track the pause state
    let isPaused = false;

    function renderMarqueeLoop() {
      // NEW: Only update positions if not paused
      if (!isPaused) {
        // Row 1 Logic: Left to Right movement
        posRow1 += scrollSpeed;
        if (posRow1 >= 0) {
          posRow1 = -(track1.scrollWidth / 2) + 20; // Re-align loop boundary offset metrics cleanly
        }
        track1.style.transform = `translateX(${posRow1}px)`;

        // Row 2 Logic: Right to Left movement
        posRow2 -= scrollSpeed;
        if (Math.abs(posRow2) >= (track2.scrollWidth / 2)) {
          posRow2 = 0;
        }
        track2.style.transform = `translateX(${posRow2}px)`;
      }

      requestAnimationFrame(renderMarqueeLoop);
    }

    // Initialize row 1 starting position state seamlessly off-screen to avoid empty gaps
    posRow1 = -(track1.scrollWidth / 4);
    
    // NEW: Add event listeners to pause on hover
    const gridContainer = document.querySelector('.portfolio-mosaic-grid');
    if (gridContainer) {
      gridContainer.addEventListener('mouseenter', () => {
        isPaused = true;
      });
      gridContainer.addEventListener('mouseleave', () => {
        isPaused = false;
      });
    }

    requestAnimationFrame(renderMarqueeLoop);
  });
</script> -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

<!-- SplitType -->
<script src="https://unpkg.com/split-type"></script>

<!-- Lenis -->
<script src="https://unpkg.com/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
<!-- <script>
    let activeSlideIndex = 0;
    const originalSlidesCount = 3;
    const autoScrollDuration = 5000; 
    let slideTimerEngine;
    let isTransitioning = false;

    const dynamicSlideTrack = document.getElementById('testimonial-slide-track');
    const dotNavigationItems = document.querySelectorAll('.dot-nav-item');

    // Setup function to copy elements for the infinite sequence loop array natively
    function setupInfiniteCarousel() {
      const originalCards = Array.from(dynamicSlideTrack.children);
      
      // Clone original setup list items and append to back of track container grid
      originalCards.forEach(card => {
        const clone = card.cloneNode(true);
        dynamicSlideTrack.appendChild(clone);
      });

      // Give track container standard animation capabilities safely after DOM settles
      dynamicSlideTrack.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
    }

    function switchActiveSlide(targetSlideIndex, animate = true) {
      if (isTransitioning && animate) return;
      if (!animate) {
        dynamicSlideTrack.style.transition = 'none';
      } else {
        dynamicSlideTrack.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
        isTransitioning = true;
      }

      activeSlideIndex = targetSlideIndex;
      const cardWidthUnit = dynamicSlideTrack.children[0].getBoundingClientRect().width;
      
      // Shifts tracking row horizontally via hardware matrix transformation calculations
      dynamicSlideTrack.style.transform = `translateX(${-activeSlideIndex * cardWidthUnit}px)`;

      // Standardize active mapping indices reference keys to activate correct visual dots
      const normalizedDotIndex = activeSlideIndex % originalSlidesCount;
      dotNavigationItems.forEach((dotElement, loopIndex) => {
        if (loopIndex === normalizedDotIndex) {
          dotElement.classList.add('active-dot');
        } else {
          dotElement.classList.remove('active-dot');
        }
      });
    }

    // Listens for structural transitions to jump track locations flawlessly
    dynamicSlideTrack.addEventListener('transitionend', () => {
      isTransitioning = false;
      
      // If the engine hits the clone section block baseline, instantly snap back without animation
      if (activeSlideIndex >= originalSlidesCount) {
        switchActiveSlide(0, false);
      }
    });

    function processNextSlideStep() {
      switchActiveSlide(activeSlideIndex + 1);
    }

    function handleDotClick(targetIndex) {
      switchActiveSlide(targetIndex);
      initializeAutoScrollTimer(); // Refresh system automated countdown clock matrices
    }

    function initializeAutoScrollTimer() {
      clearInterval(slideTimerEngine);
      slideTimerEngine = setInterval(processNextSlideStep, autoScrollDuration);
    }

    window.addEventListener('resize', () => {
      // Re-align layouts dynamically to safe viewport width parameters during screen rotation flips
      switchActiveSlide(activeSlideIndex, false);
    });

    document.addEventListener("DOMContentLoaded", () => {
      setupInfiniteCarousel();
      initializeAutoScrollTimer();
    });
  </script> -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  let activeSlideIndex = 0;
  const originalSlidesCount = 3;
  const autoScrollDuration = 5000; 
  let slideTimerEngine;
  let isTransitioning = false;

  // Modern Touch Gesture Variables
  let touchStartX = 0;
  let touchCurrentX = 0;
  let isSwiping = false;
  let currentTrackTranslation = 0;

  // Target selectors matching your HTML structure
  const dynamicSlideTrack = document.getElementById('automated-marquee-track') || document.getElementById('testimonial-slide-track');
  const dotNavigationItems = document.querySelectorAll('.dot-nav-item');

  if (!dynamicSlideTrack) return;

  // 1. Setup the track structure safely
  const originalCardsCount = dynamicSlideTrack.children.length;

  function updateNavigationDots(index) {
    const normalizedDotIndex = index % originalSlidesCount;
    dotNavigationItems.forEach((dotElement, loopIndex) => {
      if (loopIndex === normalizedDotIndex) {
        dotElement.classList.add('active-dot');
      } else {
        dotElement.classList.remove('active-dot');
      }
    });
  }

  // 2. Core Slider Animation Mechanics (Moves the track safely)
  function moveSliderToTrackIndex(index, animate = true) {
    if (!dynamicSlideTrack.children.length) return;

    if (!animate) {
      dynamicSlideTrack.style.transition = 'none';
    } else {
      dynamicSlideTrack.style.transition = 'transform 0.4s cubic-bezier(0.25, 1, 0.5, 1)';
      isTransitioning = true;
    }

    activeSlideIndex = index;
    const cardWidth = dynamicSlideTrack.children[0].getBoundingClientRect().width;
    currentTrackTranslation = -activeSlideIndex * cardWidth;
    
    dynamicSlideTrack.style.transform = `translate3d(${currentTrackTranslation}px, 0px, 0px)`;
    updateNavigationDots(activeSlideIndex);
  }

  // Handle slide wrap-around safely without visual flashing
  dynamicSlideTrack.addEventListener('transitionend', () => {
    isTransitioning = false;
    
    // If user swipes past the original cards, seamlessly warp back to index 0
    if (activeSlideIndex >= originalCardsCount) {
      moveSliderToTrackIndex(0, false);
    }
    // If user swipes backward past the start, warp to the end
    if (activeSlideIndex < 0) {
      moveSliderToTrackIndex(originalCardsCount - 1, false);
    }
  });

  // 3. Automated Desktop Cycle Loop
  function executeAutoCycleStep() {
    if (window.innerWidth > 767 && !isTransitioning) {
      moveSliderToTrackIndex((activeSlideIndex + 1) % originalCardsCount);
    }
  }

  function startAutoCycleEngine() {
    clearInterval(slideTimerEngine);
    if (window.innerWidth > 767) {
      slideTimerEngine = setInterval(executeAutoCycleStep, autoScrollDuration);
    }
  }

  // 4. Mobile Layout/Resize Synchronization
  function syncDeviceOrientationLayout() {
    clearInterval(slideTimerEngine);
    isTransitioning = false;
    
    // Clear styles so layout recalculates fresh
    dynamicSlideTrack.style.transition = 'none';
    
    if (window.innerWidth <= 767) {
      // Direct layout setup for mobile view execution
      moveSliderToTrackIndex(0, false);
    } else {
      // Desktop state initialization
      moveSliderToTrackIndex(0, false);
      startAutoCycleEngine();
    }
  }

  // --- 5. ROBUST JAVASCRIPT TOUCH-SWIPE ENGINE FOR MOBILE ---
  dynamicSlideTrack.addEventListener("touchstart", (e) => {
    if (isTransitioning) return; // Prevent spamming gestures during animation
    isSwiping = true;
    
    // Turn off transitions immediately so the element tracks the user's finger directly
    dynamicSlideTrack.style.transition = 'none';
    touchStartX = e.touches[0].clientX;
  }, { passive: true });

  dynamicSlideTrack.addEventListener("touchmove", (e) => {
    if (!isSwiping) return;

    touchCurrentX = e.touches[0].clientX;
    const dragDistanceDelta = touchCurrentX - touchStartX;

    // Move the track container in real-time under the user's finger pointer
    const immediateTrackPosition = currentTrackTranslation + dragDistanceDelta;
    dynamicSlideTrack.style.transform = `translate3d(${immediateTrackPosition}px, 0px, 0px)`;
  }, { passive: true });

  dynamicSlideTrack.addEventListener("touchend", (e) => {
    if (!isSwiping) return;
    isSwiping = false;

    const touchEndLimitX = e.changedTouches[0].clientX;
    const finalSwipeDistance = touchEndLimitX - touchStartX;
    const cardWidth = dynamicSlideTrack.children[0].getBoundingClientRect().width;
    const swipeThreshold = cardWidth * 0.2; // User must swipe at least 20% of card width to change it

    // Check swipe thresholds to determine whether to go forward, backward, or snap back
    if (finalSwipeDistance < -swipeThreshold) {
      // User swiped Left -> Go to Next Card content smoothly
      if (activeSlideIndex < originalCardsCount - 1) {
        moveSliderToTrackIndex(activeSlideIndex + 1);
      } else {
        // Infinite wrap forward fallback
        moveSliderToTrackIndex(0);
      }
    } else if (finalSwipeDistance > swipeThreshold) {
      // User swiped Right -> Go to Previous Card content smoothly
      if (activeSlideIndex > 0) {
        moveSliderToTrackIndex(activeSlideIndex - 1);
      } else {
        // Infinite wrap backward fallback
        moveSliderToTrackIndex(originalCardsCount - 1);
      }
    } else {
      // User didn't swipe far enough -> Snap right back into place cleanly
      moveSliderToTrackIndex(activeSlideIndex);
    }
  });

  // 6. Initialize global hooks
  window.addEventListener('resize', syncDeviceOrientationLayout);
  
  dotNavigationItems.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      if (window.innerWidth > 767) {
        moveSliderToTrackIndex(index);
        startAutoCycleEngine();
      }
    });
  });

  // Initial Boot Run
  syncDeviceOrientationLayout();
});
</script>

<script>
document.documentElement.style.scrollBehavior = "auto";
</script>
<a href="https://wa.me/916364268555" class="whatsapp-float-btn bsi-text" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp">
    <img src="/wp-content/uploads/2026/05/Group-8438.png">
</a>
