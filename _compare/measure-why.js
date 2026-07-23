const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  for (const w of [1024, 991, 900, 820, 768, 700]) {
    const page = await browser.newPage();
    await page.setViewport({ width: w, height: 900 });
    await page.goto("http://ceramic-pro-new.local/ceramic-coating/", { waitUntil: "domcontentloaded", timeout: 40000 });
    await new Promise(r => setTimeout(r, 500));
    await page.evaluate(() => document.querySelector(".cp-why-section")?.scrollIntoView());
    await new Promise(r => setTimeout(r, 200));
    const data = await page.evaluate((vw) => {
      const grid = document.querySelector(".cp-why-grid");
      const cs = getComputedStyle(grid);
      const cards = [...document.querySelectorAll(".cp-why-card")].map(el => {
        const r = el.getBoundingClientRect();
        return { w: Math.round(r.width), left: Math.round(r.left), right: Math.round(r.right), overflow: r.right > vw + 1 };
      });
      return {
        gtc: cs.gridTemplateColumns,
        gap: cs.gap,
        gridW: Math.round(grid.getBoundingClientRect().width),
        cards,
        anyOverflow: cards.some(c => c.overflow),
        scrollW: document.documentElement.scrollWidth,
      };
    }, w);
    console.log(w, JSON.stringify(data));
    await page.screenshot({ path: `why-${w}.png` });
    await page.close();
  }
  // also screenshot footer/contact/products tablet mobile
  for (const [slug, w] of [["contact-us",768],["products",768],["ceramic-pro",768],["about-us",390],["ceramic-coating",390]]) {
    const page = await browser.newPage();
    await page.setViewport({ width: w, height: 1100, isMobile: w < 500 });
    await page.goto(`http://ceramic-pro-new.local/${slug}/`, { waitUntil: "domcontentloaded", timeout: 40000 });
    await new Promise(r => setTimeout(r, 600));
    await page.screenshot({ path: `snap-${slug}-${w}.png` });
    await page.close();
  }
  await browser.close();
})().catch(e=>{console.error(e);process.exit(1);});
