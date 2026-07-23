const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  const pages = ["about-us","ceramic-coating","products","gallery","ceramic-pro","contact-us","paint-protection-film-ppf","furniture-coating"];
  const viewports = [1440,1200,1024,991,768,390,360];
  const issues = [];
  for (const w of viewports) {
    const page = await browser.newPage();
    await page.setViewport({ width: w, height: 900, isMobile: w < 500 });
    for (const slug of pages) {
      await page.goto(`http://ceramic-pro-new.local/${slug}/`, { waitUntil: "domcontentloaded", timeout: 40000 });
      await new Promise(r => setTimeout(r, 500));
      const data = await page.evaluate((vw) => {
        const flags = [];
        const scrollW = document.documentElement.scrollWidth;
        const clientW = document.documentElement.clientWidth;
        if (scrollW > clientW + 3) flags.push(`overflow+${scrollW-clientW}`);

        const why = document.querySelector(".cp-why-grid");
        if (why) {
          const cs = getComputedStyle(why);
          const cols = cs.gridTemplateColumns.split(" ").filter(Boolean).length;
          const cards = [...why.querySelectorAll(".cp-why-card")].map(el => {
            const r = el.getBoundingClientRect();
            return r.right > vw + 2;
          });
          if (cards.some(Boolean)) flags.push("why-card-clip");
          if (vw <= 1200 && vw > 767 && cols > 2) flags.push(`why-cols-${cols}`);
          if (vw <= 767 && cols > 1) flags.push(`why-mobile-cols-${cols}`);
        }
        const form = document.querySelector(".cp-form-main-grid");
        if (form && vw <= 1024) {
          const cols = getComputedStyle(form).gridTemplateColumns.split(" ").filter(Boolean).length;
          if (cols > 1) flags.push(`form-cols-${cols}`);
        }
        const prod = document.querySelector(".cp-prod-layout-grid");
        if (prod && vw <= 1200) {
          const cols = getComputedStyle(prod).gridTemplateColumns.split(" ").filter(Boolean).length;
          if (cols > 1) flags.push(`prod-cols-${cols}`);
        }
        const cert = document.querySelector(".certified-main-container");
        if (cert && vw <= 991) {
          if (getComputedStyle(cert).flexDirection === "row") flags.push("cert-row");
        }
        const about = document.querySelector(".cp-about-container");
        if (about && vw <= 991) {
          if (getComputedStyle(about).flexDirection === "row") flags.push("about-row");
        }
        const faq = document.querySelector(".cp-faq-item");
        if (faq) {
          const arrows = [...faq.querySelectorAll(".cp-faq-icon")].filter(el => {
            const a = getComputedStyle(el, "::after");
            return a.content !== "none" && a.display !== "none";
          });
          if (arrows.length > 1) flags.push("double-arrow");
        }
        const footer = document.querySelector(".footer-grid");
        if (footer) {
          const cols = getComputedStyle(footer).gridTemplateColumns.split(" ").filter(Boolean).length;
          if (vw <= 767 && cols > 1) flags.push(`footer-cols-${cols}`);
          if (vw <= 1024 && vw > 767 && cols > 2) flags.push(`footer-tab-cols-${cols}`);
        }
        return flags;
      }, w);
      if (data.length) issues.push({ w, slug, data });
    }
    await page.close();
  }
  console.log("ISSUES", JSON.stringify(issues, null, 2));
  console.log("count", issues.length);

  // quick why measure at problem widths
  for (const w of [1200, 1024, 768, 390]) {
    const page = await browser.newPage();
    await page.setViewport({ width: w, height: 900 });
    await page.goto("http://ceramic-pro-new.local/ceramic-coating/", { waitUntil: "domcontentloaded" });
    await new Promise(r => setTimeout(r, 400));
    const m = await page.evaluate((vw) => {
      const grid = document.querySelector(".cp-why-grid");
      const cs = getComputedStyle(grid);
      const cards = [...document.querySelectorAll(".cp-why-card")].map(el => {
        const r = el.getBoundingClientRect();
        return { w: Math.round(r.width), right: Math.round(r.right), overflow: r.right > vw + 1 };
      });
      return { gtc: cs.gridTemplateColumns, gap: cs.gap, cards, any: cards.some(c => c.overflow) };
    }, w);
    console.log("why", w, JSON.stringify(m));
    await page.close();
  }
  await browser.close();
})().catch(e=>{console.error(e);process.exit(1);});
