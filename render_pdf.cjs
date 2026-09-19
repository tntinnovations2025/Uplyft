const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

(async () => {
    console.log('Starting PDF generation via Puppeteer...');
    const htmlPath = path.resolve(__dirname, 'UPLYFT_User_Guide.html');
    const pdfPath = path.resolve(__dirname, 'UPLYFT_User_Guide.pdf');
    const publicPdfPath = path.resolve(__dirname, 'public/docs/UPLYFT_User_Guide.pdf');

    const fileUrl = 'file:///' + htmlPath.replace(/\\/g, '/');
    console.log('Loading URL:', fileUrl);

    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--allow-file-access-from-files', '--enable-local-file-accesses']
        });
    } catch (e) {
        console.log('Default launch failed, trying with system Chrome...', e.message);
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--allow-file-access-from-files', '--enable-local-file-accesses']
        });
    }

    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 1024, deviceScaleFactor: 2 });
    
    await page.goto(fileUrl, { waitUntil: ['load', 'networkidle0'], timeout: 60000 });

    // Wait for all img elements to finish loading
    await page.evaluate(async () => {
        const images = Array.from(document.querySelectorAll('img'));
        await Promise.all(images.map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise((resolve, reject) => {
                img.addEventListener('load', resolve);
                img.addEventListener('error', resolve);
            });
        }));
    });

    console.log('Rendering PDF to:', pdfPath);
    await page.pdf({
        path: pdfPath,
        format: 'A4',
        printBackground: true,
        preferCSSPageSize: true,
        margin: {
            top: '0mm',
            bottom: '0mm',
            left: '0mm',
            right: '0mm'
        }
    });

    await browser.close();

    // Copy to public/docs as well
    fs.copyFileSync(pdfPath, publicPdfPath);

    const stats = fs.statSync(pdfPath);
    console.log(`SUCCESS! Generated PDF size: ${(stats.size / (1024 * 1024)).toFixed(2)} MB (${stats.size} bytes)`);
    console.log(`Last modified: ${stats.mtime}`);
})();
