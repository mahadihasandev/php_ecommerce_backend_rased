import fs from 'fs';
import path from 'path';

try {
    const manifestPath = path.resolve('public/build/manifest.json');
    if (fs.existsSync(manifestPath)) {
        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
        fs.mkdirSync(path.resolve('public/css'), { recursive: true });
        fs.mkdirSync(path.resolve('public/js'), { recursive: true });
        
        if (manifest['resources/css/app.css']?.file) {
            fs.copyFileSync(
                path.resolve('public/build', manifest['resources/css/app.css'].file),
                path.resolve('public/css/admin.css')
            );
        }
        if (manifest['resources/js/app.js']?.file) {
            fs.copyFileSync(
                path.resolve('public/build', manifest['resources/js/app.js'].file),
                path.resolve('public/js/admin.js')
            );
        }
        console.log('✓ Successfully copied static assets to public/css/admin.css and public/js/admin.js');
    }
} catch (err) {
    console.error('Error syncing assets:', err);
}
