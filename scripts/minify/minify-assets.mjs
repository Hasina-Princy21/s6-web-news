import { promises as fs } from 'node:fs';
import path from 'node:path';
import { transform } from 'esbuild';

const PROJECT_ROOT = '/workspace';

const TARGET_DIRS = [
  path.join(PROJECT_ROOT, 'backoffice', 'assets'),
  path.join(PROJECT_ROOT, 'frontoffice', 'assets'),
];

async function exists(dirPath) {
  try {
    await fs.access(dirPath);
    return true;
  } catch {
    return false;
  }
}

async function collectFiles(dirPath) {
  const files = [];
  const stack = [dirPath];

  while (stack.length > 0) {
    const current = stack.pop();
    const entries = await fs.readdir(current, { withFileTypes: true });

    for (const entry of entries) {
      const fullPath = path.join(current, entry.name);

      if (entry.isDirectory()) {
        stack.push(fullPath);
        continue;
      }

      const isCss = entry.name.endsWith('.css');
      const isJs = entry.name.endsWith('.js');
      const isMinified = entry.name.endsWith('.min.css') || entry.name.endsWith('.min.js');

      if ((isCss || isJs) && !isMinified) {
        files.push(fullPath);
      }
    }
  }

  return files;
}

function outputPathFor(inputPath) {
  if (inputPath.endsWith('.css')) {
    return inputPath.slice(0, -4) + '.min.css';
  }

  if (inputPath.endsWith('.js')) {
    return inputPath.slice(0, -3) + '.min.js';
  }

  return inputPath;
}

function loaderFor(inputPath) {
  return inputPath.endsWith('.css') ? 'css' : 'js';
}

async function minifyFile(inputPath) {
  const source = await fs.readFile(inputPath, 'utf8');
  const transformed = await transform(source, {
    loader: loaderFor(inputPath),
    minify: true,
    legalComments: 'none',
    charset: 'utf8',
  });

  const targetPath = outputPathFor(inputPath);
  await fs.writeFile(targetPath, transformed.code, 'utf8');

  return { inputPath, targetPath };
}

async function main() {
  const targetFiles = [];

  for (const targetDir of TARGET_DIRS) {
    if (!(await exists(targetDir))) {
      continue;
    }

    const files = await collectFiles(targetDir);
    targetFiles.push(...files);
  }

  if (targetFiles.length === 0) {
    console.log('No CSS or JS asset files found under backoffice/assets or frontoffice/assets.');
    return;
  }

  const results = [];
  for (const filePath of targetFiles) {
    const result = await minifyFile(filePath);
    results.push(result);
  }

  console.log(`Minified ${results.length} asset file(s):`);
  for (const result of results) {
    const relativeInput = path.relative(PROJECT_ROOT, result.inputPath);
    const relativeTarget = path.relative(PROJECT_ROOT, result.targetPath);
    console.log(`- ${relativeInput} -> ${relativeTarget}`);
  }
}

main().catch((error) => {
  console.error('Asset minification failed:', error);
  process.exitCode = 1;
});
