const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 1200 });
  await page.goto("http://ceramic-pro-new.local/ceramic-coating/", { waitUntil: "networkidle2", timeout: 60000 });
  const data = await page.evaluate(() => {
    const pick = (sel) => {
      const el = document.querySelector(sel);
      if (!el) return { sel, missing: true };
      const cs = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      return {
        sel,
        tag: el.tagName,
        className: el.className,
        text: (el.innerText || "").slice(0, 80),
        display: cs.display,
        visibility: cs.visibility,
        opacity: cs.opacity,
        color: cs.color,
        fontSize: cs.fontSize,
        width: Math.round(r.width),
        height: Math.round(r.height),
        top: Math.round(r.top),
        left: Math.round(r.left),
        overflow: cs.overflow,
        position: cs.position,
        zIndex: cs.zIndex,
      };
    };
    const sections = [...document.querySelectorAll(".page-content > .wp-block-group, .page-content > section")].map((el,i) => {
      const r = el.getBoundingClientRect();
      const cs = getComputedStyle(el);
      return {
        i,
        className: el.className.slice(0,120),
        h: Math.round(r.height),
        w: Math.round(r.width),
        display: cs.display,
        minHeight: cs.minHeight,
        paddingTop: cs.paddingTop,
        paddingBottom: cs.paddingBottom,
        bg: cs.backgroundImage.slice(0,60),
      };
    });
    return {
      h1: pick("h1"),
      h1s: [...document.querySelectorAll("h1")].map(el => ({t: el.innerText, c: el.className, h: el.getBoundingClientRect().height, top: el.getBoundingClientRect().top, display: getComputedStyle(el).display, color: getComputedStyle(el).color, fontSize: getComputedStyle(el).fontSize})),
      hero: pick(".cp-hero-section"),
      heroGrid: pick(".cp-hero-grid"),
      heroLeft: pick(".cp-hero-left"),
      about: pick(".cp-about-container"),
      aboutLeft: pick(".cp-about-left"),
      aboutRight: pick(".cp-about-right"),
      textCols: pick(".cp-text-columns"),
      shineRow: pick(".shine-features-matrix-row"),
      whyGrid: pick(".cp-why-grid"),
      sections,
    };
  });
  console.log(JSON.stringify(data, null, 2));
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
