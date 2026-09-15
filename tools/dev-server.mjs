/**
 * SazehShop — local development / preview server
 * ---------------------------------------------------------------------------
 * این سرور مخصوص «پیش‌نمایش» است و جای وب‌سرور واقعی را نمی‌گیرد.
 *
 * روی هاست واقعی (cPanel / DirectAdmin / سرور اختصاصی) فقط به Apache + PHP 8
 * با اکستنشن‌های pdo_sqlite, mbstring, gd, fileinfo نیاز دارید و باید
 * DocumentRoot دامنه را روی پوشه `public/` تنظیم کنید (فایل‌های .htaccess هم
 * همراه پروژه هست). این فایل در پروداکشن اصلاً استفاده نمی‌شود.
 *
 * دلیل وجود این فایل: در این محیط، PHP نصب نیست و apt در دسترس نیست؛ پس
 * همان کد PHP پروژه با موتور WebAssembly و کاملاً آفلاین اجرا می‌شود و
 * درخواست‌های HTTP اینجا به PHP پاس داده می‌شوند. رفتار آن مشابه mod_rewrite
 * آپاچی است: هر مسیری که فایل استاتیک نباشد به public/index.php می‌رود.
 */

import http from 'node:http';
import fs from 'node:fs';
import fsp from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const PUBLIC_DIR = path.join(ROOT, 'public');
const PORT = Number(process.env.PORT || 8080);
const HOST = process.env.HOST || '0.0.0.0';

const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.mjs': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.webmanifest': 'application/manifest+json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.gif': 'image/gif',
  '.ico': 'image/x-icon',
  '.woff2': 'font/woff2',
  '.woff': 'font/woff',
  '.ttf': 'font/ttf',
  '.txt': 'text/plain; charset=utf-8',
  '.xml': 'application/xml; charset=utf-8',
  '.map': 'application/json; charset=utf-8',
  '.php': 'text/html; charset=utf-8',
};

/** هدرهایی که PHP برای درست کار کردن به آن‌ها نیاز دارد (با حرف بزرگ، چون PHP نسبت به آن حساس است) */
const HEADER_CASE = {
  cookie: 'Cookie',
  'content-type': 'Content-Type',
  'content-length': 'Content-Length',
  'user-agent': 'User-Agent',
  accept: 'Accept',
  'accept-language': 'Accept-Language',
  'accept-encoding': 'Accept-Encoding',
  referer: 'Referer',
  origin: 'Origin',
  authorization: 'Authorization',
  'x-requested-with': 'X-Requested-With',
  'x-csrf-token': 'X-CSRF-Token',
  'x-forwarded-proto': 'X-Forwarded-Proto',
  'x-forwarded-host': 'X-Forwarded-Host',
  'if-none-match': 'If-None-Match',
  'if-modified-since': 'If-Modified-Since',
};

function log(...args) {
  console.log(`[dev]`, ...args);
}

function sendStatic(req, res, filePath) {
  const stat = fs.statSync(filePath);
  const ext = path.extname(filePath).toLowerCase();
  const etag = `W/"${stat.size}-${Number(stat.mtimeMs).toString(36)}"`;

  // برای سرویس‌ورکر و مانیفست کش طولانی خطرناک است
  const isPwaFile = /\/(sw\.js|manifest\.webmanifest|offline\.html)$/.test(filePath);
  const cacheControl = isPwaFile ? 'no-cache' : 'public, max-age=86400';

  if (req.headers['if-none-match'] === etag) {
    res.writeHead(304, { ETag: etag, 'Cache-Control': cacheControl });
    res.end();
    return;
  }

  res.writeHead(200, {
    'Content-Type': MIME[ext] || 'application/octet-stream',
    'Content-Length': stat.size,
    'Cache-Control': cacheControl,
    ETag: etag,
  });
  if (req.method === 'HEAD') {
    res.end();
    return;
  }
  fs.createReadStream(filePath).pipe(res);
}

function collectBody(req) {
  return new Promise((resolve, reject) => {
    const chunks = [];
    req.on('data', (c) => chunks.push(c));
    req.on('end', () => resolve(Buffer.concat(chunks)));
    req.on('error', reject);
  });
}

async function main() {
  let loadNodeRuntime;
  let createNodeFsMountHandler;
  let PHP;
  let PHPRequestHandler;
  try {
    ({ loadNodeRuntime, createNodeFsMountHandler } = await import('@php-wasm/node'));
    ({ PHP, PHPRequestHandler } = await import('@php-wasm/universal'));
  } catch (error) {
    console.error(
      '\n[dev] پکیج‌های لازم نصب نیستند. لطفاً اول این دستور را اجرا کنید:\n\n    npm install\n'
    );
    console.error(String(error?.message || error));
    process.exit(1);
  }

  log('booting PHP 8.3 (WebAssembly runtime) …');
  const t0 = Date.now();
  const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
  const handler = new PHPRequestHandler({
    php,
    documentRoot: '/shop/public',
    absoluteUrl: `http://localhost:${PORT}`,
  });
  try {
    await php.mkdir('/shop');
  } catch {
    /* already mounted */
  }
  // کل پروژه داخل PHP مونت می‌شود تا کد app/ خوانده و storage/ نوشته شود
  await php.mount('/shop', createNodeFsMountHandler(ROOT));

  // نصب خودکار دیتابیس (فقط بار اول) تا اولین بازدید کاربر کند نباشد
  const warm = await handler.request({
    url: '/index.php',
    method: 'GET',
    headers: { Host: `localhost:${PORT}`, 'X-Forwarded-Uri': '/', 'X-Forwarded-Proto': 'http' },
  });
  log(`PHP ready in ${((Date.now() - t0) / 1000).toFixed(1)}s — warmup status ${warm.httpStatusCode}`);

  const server = http.createServer(async (req, res) => {
    try {
      const rawPath = (req.url || '/').split('?')[0];
      let pathname;
      try {
        pathname = decodeURIComponent(rawPath);
      } catch {
        pathname = rawPath;
      }

      // جلوگیری از path traversal
      const safePath = path.normalize(pathname).replace(/^(\.\.[/\\])+/, '');
      const candidate = path.join(PUBLIC_DIR, safePath);

      if (
        candidate.startsWith(PUBLIC_DIR) &&
        safePath !== '/' &&
        !safePath.endsWith('/') &&
        !safePath.endsWith('index.php') &&
        fs.existsSync(candidate) &&
        fs.statSync(candidate).isFile()
      ) {
        sendStatic(req, res, candidate);
        return;
      }

      const body = await collectBody(req);
      const headers = {};
      for (const [key, value] of Object.entries(req.headers)) {
        if (value === undefined) continue;
        const name = HEADER_CASE[key.toLowerCase()] || key;
        headers[name] = Array.isArray(value) ? value.join(', ') : value;
      }
      headers['X-Forwarded-Uri'] = req.url || '/';
      headers['Host'] = headers['X-Forwarded-Host'] || req.headers.host || `localhost:${PORT}`;

      const search = (req.url || '').includes('?') ? (req.url || '').slice(rawPath.length) : '';
      const phpResponse = await handler.request({
        // مسیر همیشه به front-controller می‌رود؛ مسیر اصلی در X-Forwarded-Uri است
        url: '/index.php' + search,
        method: req.method || 'GET',
        headers,
        body: body.length ? new Uint8Array(body) : undefined,
      });

      const outHeaders = {};
      for (const [key, values] of Object.entries(phpResponse.headers || {})) {
        if (key.toLowerCase() === 'transfer-encoding') continue;
        outHeaders[key] = Array.isArray(values) ? values : [values];
      }
      outHeaders['X-Powered-By'] = ['PHP/8.3 (php-wasm dev preview)'];
      const bytes = Buffer.from(phpResponse.bytes || []);
      if (!outHeaders['Content-Length']) outHeaders['Content-Length'] = [bytes.length];
      res.writeHead(phpResponse.httpStatusCode || 200, outHeaders);
      res.end(req.method === 'HEAD' ? undefined : bytes);
    } catch (error) {
      console.error('[dev] request failed:', error);
      res.writeHead(500, { 'Content-Type': 'text/plain; charset=utf-8' });
      res.end('Internal dev-server error:\n' + String(error?.stack || error));
    }
  });

  server.listen(PORT, HOST, () => {
    log(`SazehShop preview → http://${HOST}:${PORT}`);
  });
}

main().catch((error) => {
  console.error('[dev] fatal:', error);
  process.exit(1);
});
