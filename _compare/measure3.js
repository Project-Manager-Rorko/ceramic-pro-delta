const puppeteer = require("puppeteer");
(async () => {
  const browser = await puppeteer.launch({ headless: "new", args: ["--no-sandbox", "--host-resolver-rules=MAP ceramic-pro.local 127.0.0.1"] });
  async function run(url, label) {
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 1200 });
    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 60000 });
    await new Promise(r => setTimeout(r, 1200));
    const data = await page.evaluate(() => {
      const info = (sel) => {
        const el = document.querySelector(sel);
        if (!el) return {sel, missing:true};
        const r = el.getBoundingClientRect();
        const cs = getComputedStyle(el);
        return {
          sel, className: (el.className+'').slice(0,140),
          d: cs.display, vis: cs.visibility, op: cs.opacity,
          w: Math.round(r.width), h: Math.round(r.height),
          top: Math.round(r.top), left: Math.round(r.left),
          color: cs.color, fontSize: cs.fontSize,
          text: (el.innerText||'').slice(0,100).replace(/\n/g,' | '),
          pos: cs.position, z: cs.zIndex, overflow: cs.overflow,
          transform: cs.transform, maxH: cs.maxHeight
        };
      };
      return {
        hero: info(".premium-hero-container, .cp-landing-hero, .about-hero, [class*=hero]"),
        allHeroCandidates: [...document.querySelectorAll("[class*='hero'], [class*='premium']")].slice(0,12).map(el=>{
          const r=el.getBoundingClientRect(); const cs=getComputedStyle(el);
          return {c: el.className.slice(0,100), h:Math.round(r.height), w:Math.round(r.width), top:Math.round(r.top), d:cs.display, text:(el.innerText||'').slice(0,60).replace(/\n/g,'|')};
        }),
        pageContentFirst: (()=>{
          const pc=document.querySelector('.page-content');
          if(!pc) return null;
          return [...pc.children].slice(0,6).map(el=>{
            const r=el.getBoundingClientRect();
            return {c:el.className.slice(0,120), h:Math.round(r.height), text:(el.innerText||'').slice(0,80).replace(/\n/g,'|')};
          });
        })(),
        whyRight: info(".why-studio-right-grid"),
        whyRightNew: info(".why-studio-right-grid.new-sec"),
        studioCards: [...document.querySelectorAll(".studio-feature-card")].map(el=>{
          const r=el.getBoundingClientRect();
          return {h:Math.round(r.height), w:Math.round(r.width), top:Math.round(r.top), left:Math.round(r.left)};
        }),
      };
    });
    console.log("==== "+label+" ====");
    console.log(JSON.stringify(data,null,2));
    await page.close();
  }
  await run("http://ceramic-pro-new.local/about-us/", "NEW about");
  await run("http://ceramic-pro.local:10010/about-us/", "OLD about");
  await browser.close();
})().catch(e=>{console.error(e); process.exit(1);});
