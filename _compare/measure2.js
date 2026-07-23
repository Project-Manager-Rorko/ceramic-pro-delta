const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({
    headless: "new",
    args: ["--no-sandbox", "--host-resolver-rules=MAP ceramic-pro.local 127.0.0.1"]
  });
  async function measure(url, label) {
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 1200 });
    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 60000 });
    await new Promise(r => setTimeout(r, 1500));
    const data = await page.evaluate(() => {
      const info = (sel) => {
        const el = document.querySelector(sel);
        if (!el) return null;
        const r = el.getBoundingClientRect();
        const cs = getComputedStyle(el);
        return {
          sel,
          d: cs.display,
          w: Math.round(r.width),
          h: Math.round(r.height),
          widthCSS: cs.width,
          flexDir: cs.flexDirection,
          gtc: cs.gridTemplateColumns,
          gap: cs.gap
        };
      };
      return {
        certifiedMain: info(".certified-main-container"),
        certifiedLeft: info(".certified-content-left"),
        certifiedRight: info(".certified-cards-right"),
        badges: info(".certified-badges-row"),
        expertise: info(".expertise-split-container"),
        powered: info(".powered-grid"),
        whyStudio: info(".why-studio-layout"),
        whyRight: info(".why-studio-right-grid"),
        prodLayout: info(".cp-prod-layout-grid"),
        formGrid: info(".cp-form-main-grid"),
        gallery: info(".cp-gallery-grid"),
        define: info(".cp-define-split-grid"),
        ion: info(".cp-ion-split-grid"),
        layer: info(".cp-layering-split-grid"),
        process: info(".cp-process-layout-grid"),
        heroGrid: info(".cp-hero-grid"),
        about: info(".cp-about-container"),
      };
    });
    console.log("==== " + label + " ====");
    console.log(JSON.stringify(data, null, 2));
    await page.close();
  }
  await measure("http://ceramic-pro-new.local/about-us/", "NEW about-us");
  await measure("http://ceramic-pro-new.local/products/", "NEW products");
  await measure("http://ceramic-pro-new.local/contact-us/", "NEW contact");
  await measure("http://ceramic-pro-new.local/ceramic-pro/", "NEW ceramic-pro");
  await measure("http://ceramic-pro.local:10010/about-us/", "OLD about-us");
  await measure("http://ceramic-pro.local:10010/ceramic-coating/", "OLD coating");
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
