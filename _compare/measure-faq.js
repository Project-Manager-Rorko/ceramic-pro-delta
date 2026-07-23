const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto("http://ceramic-pro-new.local/ceramic-coating/", { waitUntil: "domcontentloaded", timeout: 60000 });
  await new Promise(r => setTimeout(r, 1200));
  const data = await page.evaluate(() => {
    const items = [...document.querySelectorAll(".cp-faq-item")].slice(0, 3).map((item, i) => {
      const icons = [...item.querySelectorAll(".cp-faq-icon")].map(el => {
        const after = getComputedStyle(el, "::after");
        const r = el.getBoundingClientRect();
        return {
          className: el.className.slice(0, 80),
          tag: el.tagName,
          w: Math.round(r.width),
          h: Math.round(r.height),
          afterContent: after.content,
          afterDisplay: after.display,
          border: getComputedStyle(el).border,
        };
      });
      return { i, icons };
    });
    return items;
  });
  console.log(JSON.stringify(data, null, 2));
  await page.screenshot({ path: "faq-arrows-fixed.png", fullPage: false });
  // scroll to FAQ
  await page.evaluate(() => {
    const el = document.querySelector(".cp-faq-section");
    if (el) el.scrollIntoView({ block: "start" });
  });
  await new Promise(r => setTimeout(r, 400));
  await page.screenshot({ path: "faq-arrows-section.png" });
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
