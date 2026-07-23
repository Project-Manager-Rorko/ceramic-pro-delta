const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  // bust cache
  await page.goto("http://ceramic-pro-new.local/?nocache=" + Date.now(), { waitUntil: "networkidle2", timeout: 60000 });
  await new Promise(r => setTimeout(r, 1500));
  const data = await page.evaluate(() => {
    const pc = document.querySelector(".page-content");
    const kids = pc ? [...pc.children].map(el => ({
      tag: el.tagName,
      cls: (el.className+"").slice(0,100),
      h: Math.round(el.getBoundingClientRect().height),
      text: (el.innerText||"").trim().slice(0,50).replace(/\n/g,"|")
    })) : [];
    const sections = [...document.querySelectorAll(".page-content > section, .page-content > .wp-block-group, .page-content section")].slice(0,15).map(el => ({
      tag: el.tagName,
      cls: (el.className+"").slice(0,90),
      h: Math.round(el.getBoundingClientRect().height)
    }));
    return {
      kids,
      sections,
      hero: (() => {
        const h = document.querySelector("section.hero, .hero");
        if (!h) return null;
        const r = h.getBoundingClientRect();
        return { h: Math.round(r.height), d: getComputedStyle(h).display, w: Math.round(r.width) };
      })(),
      overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      hasMastering: !!document.body.innerText.match(/Mastering the Art of Paint Protection/),
      hasAbout: !!document.body.innerText.match(/authorized Ceramic Pro studio/),
    };
  });
  console.log(JSON.stringify(data, null, 2));
  await page.screenshot({ path: "home-fixed-top.png" });
  await page.evaluate(() => window.scrollTo(0, 1200));
  await new Promise(r => setTimeout(r, 300));
  await page.screenshot({ path: "home-fixed-mid.png" });
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
