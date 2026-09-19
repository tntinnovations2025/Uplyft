import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const OUTPUT_DIR = path.join(__dirname, 'public', 'docs', 'screenshots');
if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

const BASE_URL = 'http://127.0.0.1:8000';
const PASSWORD = 'Password123!';

async function capture() {
    console.log('Launching browser with local Chrome...');
    const browser = await puppeteer.launch({
        headless: 'new',
        executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 2 });

    async function loginRole(roleUrl, email) {
        console.log(`Logging in to ${roleUrl} with ${email}...`);
        await page.goto(`${BASE_URL}${roleUrl}`, { waitUntil: 'networkidle2' });
        
        await page.waitForSelector('#credential');
        await page.type('#credential', email, { delay: 10 });
        await page.type('#password', PASSWORD, { delay: 10 });
        
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.evaluate(() => {
                const form = document.querySelector('#unifiedLoginForm') || document.querySelector('form');
                form.submit();
            })
        ]);
        console.log(`Successfully logged in as ${email}`);
    }

    async function takeScreenshot(url, filename) {
        console.log(`Navigating to ${url}...`);
        try {
            await page.goto(`${BASE_URL}${url}`, { waitUntil: 'networkidle2', timeout: 20000 });
            await new Promise(r => setTimeout(r, 1500)); // wait for charts / animations
            const filePath = path.join(OUTPUT_DIR, filename);
            await page.screenshot({ path: filePath });
            console.log(`Saved screenshot: ${filename}`);
        } catch (e) {
            console.error(`Failed to capture ${url}:`, e.message);
        }
    }

    try {
        // 1. Principal Portal
        await loginRole('/principal/login', 'principal.cambridge@apex.edu.pk');
        await takeScreenshot('/principal/dashboard', 'principal_dashboard.png');
        await takeScreenshot('/principal/timetables', 'principal_timetables.png');
        await takeScreenshot('/principal/classes-subjects', 'principal_classes.png');
        await takeScreenshot('/principal/staff', 'principal_staff.png');
        await takeScreenshot('/principal/students', 'principal_students.png');
        await takeScreenshot('/principal/invoices', 'principal_invoices.png');
        await takeScreenshot('/principal/accounts', 'principal_accounts.png');
        await takeScreenshot('/principal/directory', 'principal_directory.png');

        // 2. Teacher Portal
        await loginRole('/faculty/login', 'ahmed.camb@apex.edu.pk');
        await takeScreenshot('/teacher/dashboard', 'teacher_dashboard.png');
        await takeScreenshot('/teacher/attendance', 'teacher_attendance.png');
        await takeScreenshot('/teacher/diary', 'teacher_diary.png');
        await takeScreenshot('/lms/mocks', 'teacher_mocks.png');
        await takeScreenshot('/lms/chatbot', 'teacher_chatbot.png');

        // 3. Accountant Portal
        await loginRole('/faculty/login', 'accountant.cambridge@apex.edu.pk');
        await takeScreenshot('/staff/dashboard', 'accountant_dashboard.png');
        await takeScreenshot('/staff/invoices', 'accountant_invoices.png');
        await takeScreenshot('/staff/accounts', 'accountant_accounts.png');

        // 4. Student Portal
        await loginRole('/student/login', 'daniyal.cambridge@student.apex.edu.pk');
        await takeScreenshot('/student/dashboard', 'student_dashboard.png');
        await takeScreenshot('/student/timetable', 'student_timetable.png');
        await takeScreenshot('/student/fees', 'student_fees.png');
        await takeScreenshot('/student/diary', 'student_diary.png');
        await takeScreenshot('/student/rag/chat', 'student_ai_tutor.png');

        console.log('SUCCESS: All portal screenshots captured!');
    } catch (err) {
        console.error('Error during execution:', err);
    } finally {
        await browser.close();
    }
}

capture();
