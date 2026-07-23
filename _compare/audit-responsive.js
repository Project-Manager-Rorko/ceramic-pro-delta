const puppeteer = require("puppeteer");

const pages = [
  "about-us",
  "ceramic-coating",
  "paint-protection-film-ppf",
  "interior-cleaning-polishing-conditioning",
  "composite-protection-film-cpf",
  "furniture-coating",
  "products",
  "gallery",
  "ceramic-pro",
  "contact-us",
  "blog",
];

const viewports = [
  { name: "desktop", width: 1440, height: 900 },
  { name: "tablet", width: 768, height: 1024 },
  { name: "mobile", width: 390, height: 844 },
];

const selectors = [
  ".cp-hero-grid",
  ".cp-about-container",
  ".cp-text-columns",
  ".shine-features-matrix-row",
  ".cp-why-grid",
  ".cp-why-header",
  ".cp-faq-trigger",
  ".certified-main-container",
  ".certified-cards-right",
  ".why-studio-layout",
  ".why-studio-right-grid",
  ".powered-grid",
  ".expertise-split-container",
  ".cp-prod-layout-grid",
  ".cp-form-main-grid",
  ".cp-gallery-grid",
  ".cp-define-split-grid",
  ".cp-process-layout-grid",
  ".cp-ion-split-grid",
  ".cp-layering-split-grid",
  ".cp-hero-split-grid",
  "header",
  ".footer-grid",
  ".nav-wrapper",
];

(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox"] });
  const issues = [];

  for (const vp of viewports) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.width, height: vp.height, isMobile: vp.width < 500, hasTouch: vp.width < 900 });

    for (const slug of pages) {
      const url = `http://ceramic-pro-new.local/${slug}/`;
      try {
        await page.goto(url, { waitUntil: "domcontentloaded", timeout: 45000 });
        await new Promise((r) => setTimeout(r, 800));

        const result = await page.evaluate((sels, vw) => {
          const pageIssues = [];
          const docW = document.documentElement.scrollWidth;
          const clientW = document.documentElement.clientWidth;
          if (docW > clientW + 2) {
            pageIssues.push({ type: "horizontal-overflow", docW, clientW, overflow: docW - clientW });
          }

          // find elements wider than viewport
          const wide = [];
          document.querySelectorAll("body *").forEach((el) => {
            const r = el.getBoundingClientRect();
            if (r.width > clientW + 4 && r.height > 0) {
              const cls = (el.className && typeof el.className === "string") ? el.className.slice(0, 80) : el.tagName;
              if (wide.length < 8) wide.push({ cls, w: Math.round(r.width) });
            }
          });
          if (wide.length) pageIssues.push({ type: "wide-elements", items: wide });

          // layout metrics for known sections
          const layouts = {};
          for (const sel of sels) {
            const el = document.querySelector(sel);
            if (!el) continue;
            const cs = getComputedStyle(el);
            const r = el.getBoundingClientRect();
            layouts[sel] = {
              d: cs.display,
              fd: cs.flexDirection,
              gtc: cs.gridTemplateColumns,
              w: Math.round(r.width),
              h: Math.round(r.height),
              overflow: r.right > clientW + 2 || r.left < -2,
            };
          }

          // FAQ double icons check
          const faqItem = document.querySelector(".cp-faq-item");
          if (faqItem) {
            const icons = [...faqItem.querySelectorAll(".cp-faq-icon")];
            const arrows = icons.filter((el) => getComputedStyle(el, "::after").content !== "none" && getComputedStyle(el, "::after").display !== "none");
            if (arrows.length > 1) pageIssues.push({ type: "double-faq-arrow", count: arrows.length });
          }

          // zero-height sections that should have content
          const sections = [...document.querySelectorAll(".page-content > .wp-block-group")];
          const collapsed = sections
            .filter((el) => {
              const r = el.getBoundingClientRect();
              const text = (el.innerText || "").trim();
              return r.height < 5 && text.length > 20;
            })
            .map((el) => el.className.slice(0, 80));
          if (collapsed.length) pageIssues.push({ type: "collapsed-sections", collapsed });

          return { pageIssues, layouts };
        }, selectors, vp.width);

        // heuristic checks by viewport
        const L = result.layouts;
        const expect = (sel, cond, msg) => {
          if (!L[sel]) return;
          if (!cond(L[sel])) issues.push({ vp: vp.name, slug, sel, msg, got: L[sel] });
        };

        if (vp.name === "desktop") {
          expect(".shine-features-matrix-row", (x) => (x.gtc || "").split(" ").length >= 4, "shine should be 4-col desktop");
          expect(".cp-why-grid", (x) => (x.gtc || "").split(" ").length >= 4, "why grid 4-col desktop");
          expect(".cp-hero-grid", (x) => x.w > 0 && x.w < vp.width * 0.7, "hero grid ~55% width");
          expect(".why-studio-right-grid", (x) => x.d === "grid" && (x.gtc || "").split(" ").length >= 2, "why studio 2-col");
          expect(".powered-grid", (x) => (x.gtc || "").split(" ").length >= 3, "powered 3-col");
          expect(".cp-gallery-grid", (x) => (x.gtc || "").split(" ").length >= 3, "gallery multi-col");
        }
        if (vp.name === "tablet") {
          // stacks or reduced columns ok
          expect(".cp-about-container", (x) => x.w <= vp.width, "about fits");
        }
        if (vp.name === "mobile") {
          expect(".cp-hero-grid", (x) => x.w >= vp.width * 0.7 || x.w === 0, "hero full-ish on mobile");
          expect(".shine-features-matrix-row", (x) => (x.gtc || "").split(" ").filter(Boolean).length <= 2 || x.d !== "grid", "shine not 4-col mobile");
          expect(".cp-why-grid", (x) => (x.gtc || "").split(" ").filter((p) => p && p !== "0px").length <= 2 || !L[".cp-why-grid"], "why not 4 fixed cols mobile");
          expect(".powered-grid", (x) => (x.gtc || "").split(" ").filter(Boolean).length <= 2 || !L[".powered-grid"], "powered single col mobile");
          expect(".cp-gallery-grid", (x) => (x.gtc || "").split(" ").filter(Boolean).length <= 3, "gallery reduced mobile");
        }

        for (const iss of result.pageIssues) {
          issues.push({ vp: vp.name, slug, ...iss });
        }
        process.stdout.write(`OK ${vp.name} ${slug}\n`);
      } catch (e) {
        issues.push({ vp: vp.name, slug, type: "load-error", msg: String(e.message || e) });
        process.stdout.write(`FAIL ${vp.name} ${slug}: ${e.message}\n`);
      }
    }
    await page.close();
  }

  console.log("\n===== ISSUES =====");
  console.log(JSON.stringify(issues, null, 2));
  console.log(`Total issues: ${issues.length}`);
  await browser.close();
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
