import { chromium } from "playwright";

async function checkPage(url) {
  const browser = await chromium.launch({
    headless: true
  });

  const page = await browser.newPage();

  let consoleErrors = [];
  let jsErrors = [];
  let networkErrors = [];

  page.on("console", msg => {
    if (msg.type() === "error") {
      consoleErrors.push(msg.text());
    }
  });

  page.on("pageerror", err => {
    jsErrors.push(err.message);
  });

  page.on("requestfailed", req => {
    networkErrors.push({
      url: req.url(),
      error: req.failure().errorText
    });
  });

  console.log(`⏳ 正在检查：${url}`);
  await page.goto(url, { timeout: 20000 });

  console.log(`\n==== F12 报错结果（${url}） ====\n`);

  console.log("📌 Console 错误：");
  console.log(consoleErrors.length ? consoleErrors : "无");

  console.log("\n📌 JS 错误：");
  console.log(jsErrors.length ? jsErrors : "无");

  console.log("\n📌 网络错误 (requestfailed)：");
  console.log(networkErrors.length ? networkErrors : "无");

  await browser.close();
}

checkPage("https://copla.top/index.html");
