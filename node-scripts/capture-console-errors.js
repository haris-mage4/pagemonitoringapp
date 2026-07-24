const puppeteer = require('puppeteer-core');

async function main() {
  const [, , url, chromePath, timeoutMs] = process.argv;

  if (!url || !chromePath) {
    console.error('Usage: capture-console-errors.js <url> <chromePath> [timeoutMs]');
    process.exit(1);
  }

  const timeout = Number(timeoutMs) || 30000;
  const errors = [];

  const browser = await puppeteer.launch({
    executablePath: chromePath,
    headless: true,
    args: ['--no-sandbox', '--headless=new'],
  });

  try {
    const page = await browser.newPage();
    await page.setViewport({ width: 375, height: 667, isMobile: true, hasTouch: true });
    await page.setUserAgent(
      'Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36'
    );

    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        const location = msg.location();
        errors.push({
          message: msg.text(),
          source: location.url ? `${location.url}:${location.lineNumber ?? ''}` : null,
          stack: null,
        });
      }
    });

    page.on('pageerror', (err) => {
      errors.push({
        message: err.message,
        source: null,
        stack: err.stack ?? null,
      });
    });

    await page.goto(url, { waitUntil: 'load', timeout });
    await new Promise((resolve) => setTimeout(resolve, 5000));
  } finally {
    await browser.close();
  }

  process.stdout.write(JSON.stringify({ success: true, errors }));
}

main().catch((err) => {
  process.stdout.write(JSON.stringify({ success: false, error: err.message }));
  process.exit(1);
});
