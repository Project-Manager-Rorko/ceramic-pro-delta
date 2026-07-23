const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto("http://ceramic-pro-new.local/", { waitUntil: "networkidle2", timeout: 60000 });
  await new Promise(r => setTimeout(r, 1500));
  const data = await page.evaluate(() => {
    const sections = [...document.querySelectorAll(".page-content > .wp-block-group, .page-content > section")].map(el => ({
      cls: (el.className+"").slice(0,90),
      h: Math.round(el.getBoundingClientRect().height),
      text: (el.innerText||"").slice(0,60).replace(/\n/g,"|")
    }));
    const hero = document.querySelector(".hero");
    const mastering = [...document.querySelectorAll("h4")].find(h => /Mastering the Art/i.test(h.textContent||""));
    return {
      sectionCount: sections.length,
      sections,
      heroH: hero ? Math.round(hero.getBoundingClientRect().height) : null,
      heroDisplay: hero ? getComputedStyle(hero).display : null,
      hasMastering: !!mastering,
      masteringVisible: mastering ? getComputedStyle(mastering).display !== "none" && mastering.getBoundingClientRect().height > 0 : false,
      scrollW: document.documentElement.scrollWidth,
      clientW: document.documentElement.clientWidth,
    };
  });
  console.log(JSON.stringify(data, null, 2));
  await page.screenshot({ path: "home-after-blocks.png", fullPage: false });
  // scroll to matrix
  await page.evaluate(() => {
    const el = [...document.querySelectorAll("h4")].find(h => /Mastering the Art/i.test(h.textContent||""));
    if (el) el.scrollIntoView({ block: "center" });
  });
  await new Promise(r => setTimeout(r, 400));
  await page.screenshot({ path: "home-mastering.png" });
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
