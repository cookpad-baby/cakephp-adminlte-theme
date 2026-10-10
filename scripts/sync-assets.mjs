import { mkdir, readFile, readdir, rm, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../', import.meta.url));
const check = process.argv.includes('--check');
const { dependencies } = JSON.parse(await readFile(join(root, 'package.json'), 'utf8'));

// Copy browser distributions and attribution only; upstream build manifests stay
// in node_modules. Keep the existing paths used by Composer consumers.
const assets = {
  bootstrap: [
    'dist/css', 'dist/fonts', 'dist/js/bootstrap.js', 'dist/js/bootstrap.min.js',
    'fonts', 'LICENSE', 'README.md', 'CHANGELOG.md',
  ],
  fastclick: ['lib/fastclick.js', 'LICENSE', 'README.md'],
  'font-awesome': ['css', 'fonts', 'README.md', 'HELP-US-OUT.txt'],
  jquery: [
    'dist/jquery.js', 'dist/jquery.min.js', 'dist/jquery.min.map',
    'AUTHORS.txt', 'LICENSE.txt', 'README.md',
  ],
  'jquery-slimscroll': ['jquery.slimscroll.js', 'jquery.slimscroll.min.js', 'README.md'],
};

const obsolete = [
  'bootstrap/package.json',
  'bootstrap/dist/js/npm.js',
  'fastclick/bower.json',
  'font-awesome/bower.json',
  'jquery/bower.json',
  'jquery-slimscroll/bower.json',
  'jquery-slimscroll/package.json',
];

async function filesAt(path) {
  try {
    const entries = await readdir(path, { withFileTypes: true });
    const nested = await Promise.all(entries.map(entry => filesAt(join(path, entry.name))));
    return nested.flat().sort();
  } catch (error) {
    if (error.code === 'ENOTDIR') return [path];
    throw error;
  }
}

// Read and validate every source before updating any committed asset.
const copies = [];
for (const [name, paths] of Object.entries(assets)) {
  const source = join(root, 'node_modules', name);
  const metadata = JSON.parse(await readFile(join(source, 'package.json'), 'utf8'));
  if (metadata.version !== dependencies[name]) {
    throw new Error(`${name}: expected ${dependencies[name]}, found ${metadata.version}; run npm ci`);
  }
  for (const path of paths) {
    for (const file of await filesAt(join(source, path))) {
      const destination = join(root, 'webroot/bower_components', name, file.slice(source.length + 1));
      copies.push({ destination, contents: await readFile(file) });
    }
  }
}

const mismatches = [];
for (const { destination, contents } of copies) {
  const current = await readFile(destination).catch(error => {
    if (error.code === 'ENOENT') return null;
    throw error;
  });
  if (current?.equals(contents)) continue;
  if (check) {
    mismatches.push(destination);
  } else {
    await mkdir(dirname(destination), { recursive: true });
    await writeFile(destination, contents);
  }
}

for (const path of obsolete) {
  const destination = join(root, 'webroot/bower_components', path);
  if (check) {
    const present = await readFile(destination).catch(error => {
      if (error.code === 'ENOENT') return null;
      throw error;
    });
    if (present !== null) mismatches.push(destination);
  } else {
    await rm(destination, { force: true });
  }
}

if (mismatches.length) {
  console.error('Browser assets differ from the locked packages; run npm run assets:sync:');
  for (const path of mismatches) console.error(path.slice(root.length));
  process.exitCode = 1;
} else {
  console.log(`${check ? 'Verified' : 'Synced'} ${copies.length} browser asset files.`);
}
