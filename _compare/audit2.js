const puppeteer = require("puppeteer");
const pages = ["about-us","ceramic-coating","products","gallery","ceramic-pro","contact-us"];
const viewports = [
  { name: "desktop", w: 1440 },
  { name: "laptop", w: 1200 },
  { name: "tablet", w: 768 },
  { name: "mobile", w: 390 },
  { name: "small", w: 360 },
];
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  const report = [];
  for (const vp of viewports) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.w, height: 900, isMobile: vp.w < 500 });
    for (const slug of pages) {
      await page.goto(`http://ceramic-pro-new.local/${slug}/`, { waitUntil: "domcontentloaded", timeout: 45000 });
      await new Promise(r => setTimeout(r, 600));
      const data = await page.evaluate((vw) => {
        const out = { scrollW: document.documentElement.scrollWidth, clientW: document.documentElement.clientWidth };
        const pick = (sel) => {
          const el = document.querySelector(sel);
          if (!el) return null;
          const r = el.getBoundingClientRect();
          const cs = getComputedStyle(el);
          return {
            d: cs.display, fd: cs.flexDirection,
            gtc: cs.gridTemplateColumns.split(" ").filter(Boolean).length,
            gtcRaw: cs.gridTemplateColumns.slice(0,80),
            w: Math.round(r.width), h: Math.round(r.height),
            overflowX: r.right > vw + 2,
            childCount: el.children.length,
          };
        };
        out.footer = pick(".footer-grid");
        out.nav = pick(".nav-wrapper");
        out.whyCards = [...document.querySelectorAll(".cp-why-card")].slice(0,4).map(el => {
          const r = el.getBoundingClientRect();
          return { w: Math.round(r.width), left: Math.round(r.left), right: Math.round(r.right) };
        });
        out.whyGrid = pick(".cp-why-grid");
        out.shine = pick(".shine-features-matrix-row");
        out.about = pick(".cp-about-container");
        out.prod = pick(".cp-prod-layout-grid");
        out.form = pick(".cp-form-main-grid");
        out.gallery = pick(".cp-gallery-grid");
        out.define = pick(".cp-define-split-grid");
        out.ion = pick(".cp-ion-split-grid");
        out.layer = pick(".cp-layering-split-grid");
        out.process = pick(".cp-process-layout-grid");
        out.certified = pick(".certified-main-container");
        out.whyStudio = pick(".why-studio-layout");
        out.whyRight = pick(".why-studio-right-grid");
        out.powered = pick(".powered-grid");
        out.heroGrid = pick(".cp-hero-grid");
        out.pdng = (() => {
          const el = document.querySelector(".pdng-lt-rt");
          if (!el) return null;
          const cs = getComputedStyle(el);
          return { pl: cs.paddingLeft, pr: cs.paddingRight };
        })();
        // any element causing page scroll width
        if (out.scrollW > out.clientW + 2) {
          const offenders = [];
          document.querySelectorAll("body *").forEach(el => {
            const r = el.getBoundingClientRect();
            if (r.right > out.clientW + 5 && getComputedStyle(el).position !== "fixed") {
              const parent = el.parentElement;
              const pcs = parent ? getComputedStyle(parent) : null;
              if (pcs && (pcs.overflowX === "hidden" || pcs.overflow === "hidden")) return;
              if (offenders.length < 6) offenders.push({ c: (el.className+"").slice(0,70), right: Math.round(r.right), w: Math.round(r.width) });
            }
          });
          out.offenders = offenders;
        }
        return out;
      }, vp.w);
      const flags = [];
      if (data.scrollW > data.clientW + 2) flags.push(`H-OVERFLOW +${data.scrollW-data.clientW}`);
      if (data.whyCards.some(c => c.right > vp.w + 2)) flags.push("why-card overflow");
      if (data.whyGrid && vp.w <= 600 && data.whyGrid.gtc >= 4) flags.push(`why-grid still ${data.whyGrid.gtc}col`);
      if (data.shine && vp.w <= 600 && data.shine.gtc >= 3) flags.push(`shine still ${data.shine.gtc}col`);
      if (data.powered && vp.w <= 600 && data.powered.gtc >= 3) flags.push(`powered still ${data.powered.gtc}col`);
      if (data.footer && vp.w <= 768 && data.footer.gtc >= 4) flags.push(`footer still ${data.footer.gtc}col`);
      if (data.about && vp.w <= 600 && data.about.fd === "row" && data.about.w > 0) {
        // row on mobile may be ok if children stack via wrap - check heights
      }
      if (data.about && vp.w <= 767) {
        // expect column or full width children
      }
      if (data.prod && vp.w <= 768 && data.prod.gtc >= 2 && data.prod.gtcRaw.includes("487px")) flags.push("prod still 487px col");
      if (data.form && vp.w <= 768 && data.form.gtc >= 2) flags.push("form still 2col");
      if (data.define && vp.w <= 768 && data.define.gtc >= 2) flags.push("define still 2col");
      if (data.ion && vp.w <= 768 && data.ion.fd === "row" && data.ion.w > 0) {
        // ion is flex row - check if children overflow
        if (data.ion.overflowX) flags.push("ion overflow");
      }
      if (data.certified && vp.w <= 767 && data.certified.fd === "row") flags.push("certified still row mobile");
      if (data.whyStudio && vp.w <= 767 && data.whyStudio.fd === "row") flags.push("whyStudio still row mobile");
      if (data.heroGrid && vp.w <= 600 && data.heroGrid.w < vp.w * 0.85 && data.heroGrid.w > 0) flags.push(`heroGrid only ${data.heroGrid.w}px`);

      report.push({ vp: vp.name, w: vp.w, slug, flags, whyGtc: data.whyGrid?.gtc, shineGtc: data.shine?.gtc, footerGtc: data.footer?.gtc, aboutFd: data.about?.fd, prodGtc: data.prod?.gtc, formGtc: data.form?.gtc, defineGtc: data.define?.gtc, ionFd: data.ion?.fd, certifiedFd: data.certified?.fd, whyStudioFd: data.whyStudio?.fd, whyRightGtc: data.whyRight?.gtc, poweredGtc: data.powered?.gtc, heroW: data.heroGrid?.w, galleryGtc: data.gallery?.gtc, pdng: data.pdng, scroll: `${data.scrollW}/${data.clientW}`, offenders: data.offenders, whyCards: data.whyCards });
    }
    await page.close();
  }
  // print only problematic or summary
  console.log("===== FLAGGED =====");
  report.filter(r => r.flags.length).forEach(r => console.log(JSON.stringify(r)));
  console.log("\n===== SUMMARY ALL =====");
  report.forEach(r => {
    console.log(`${r.vp.padEnd(8)} ${r.slug.padEnd(42)} flags=${r.flags.join("|")||"ok"} why=${r.whyGtc} shine=${r.shineGtc} foot=${r.footerGtc} about=${r.aboutFd} cert=${r.certifiedFd} heroW=${r.heroW} form=${r.formGtc} prod=${r.prodGtc} gal=${r.galleryGtc} def=${r.defineGtc} ion=${r.ionFd} pad=${r.pdng?.pl}`);
  });
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
