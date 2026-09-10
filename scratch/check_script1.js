const fs = require('fs');
const content = fs.readFileSync('resources/views/principal/staff/index.blade.php', 'utf8');
const scripts = content.match(/<script>([\s\S]*?)<\/script>/g);
const code = scripts[1].replace(/<\/?script>/g, '');

// Strip blade directives like @json(...) for syntax checking
const cleanCode = code.replace(/@json\(.*?\)/g, '[]');

fs.writeFileSync('scratch/script1_clean.js', cleanCode);

try {
    require('vm').runInNewContext(cleanCode);
    console.log('Script 1 syntax is valid!');
} catch (e) {
    console.error('Script 1 error:', e.message);
    console.error(e.stack);
}
