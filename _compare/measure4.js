const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox", "--host-resolver-rules=MAP ceramic-pro.local 127.0.0.1"] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 1200 });
  await page.goto("http://ceramic-pro-new.local/ceramic-coating/", { waitUntil: "domcontentloaded", timeout: 60000 });
  await new Promise(r => setTimeout(r, 1000));
  const data = await page.evaluate(() => {
    const info = (sel) => {
      const el = document.querySelector(sel);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const cs = getComputedStyle(el);
      return { sel, d: cs.display, w: Math.round(r.width), h: Math.round(r.height), gtc: cs.gridTemplateColumns, gap: cs.gap };
    };
    return {
      heroGrid: info(".cp-hero-grid"),
      about: info(".cp-about-container"),
      aboutL: info(".cp-about-left"),
      aboutR: info(".cp-about-right"),
      textCols: info(".cp-text-columns"),
      shine: info(".shine-features-matrix-row"),
      why: info(".cp-why-grid"),
    };
  });
  console.log(JSON.stringify(data, null, 2));
  await page.goto("http://ceramic-pro.local:10010/ceramic-coating/", { waitUntil: "domcontentloaded", timeout: 60000 });
  await new Promise(r => setTimeout(r, 1000));
  const old = await page.evaluate(() => {
    const info = (sel) => {
      const el = document.querySelector(sel);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const cs = getComputedStyle(el);
      return { sel, d: cs.display, w: Math.round(r.width), h: Math.round(r.height), gtc: cs.gridTemplateColumns, gap: cs.gap };
    };
    return {
      heroGrid: info(".cp-hero-grid"),
      about: info(".cp-about-container"),
      aboutL: info(".cp-about-left"),
      aboutR: info(".cp-about-right"),
      textCols: info(".cp-text-columns"),
      shine: info(".shine-features-matrix-row"),
      why: info(".cp-why-grid"),
    };
  });
  console.log("OLD", JSON.stringify(old, null, 2));
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
