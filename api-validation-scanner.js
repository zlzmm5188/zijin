#!/usr/bin/env node

/**
 * Providence API Integration Validation Scanner
 * 
 * Purpose: Scan frontend (providence) and backend (providence-admin/api) 
 * to identify API integration issues, field mismatches, and errors.
 * 
 * Based on requirements from:
 * - ✅上线前最终检查清单.md
 * - 生产环境终极检查报告.md
 * - PR #1 comment: "扫描前后端接口,提取所有错误"
 */

const fs = require('fs');
const path = require('path');

// Configuration
const FRONTEND_DIR = path.join(__dirname, 'providence');
const BACKEND_API_DIR = path.join(__dirname, 'providence-admin', 'api');
const REPORT_OUTPUT = path.join(__dirname, 'API_VALIDATION_REPORT.md');

// Results storage
const results = {
  frontendAPICalls: [],
  backendAPIs: [],
  mismatches: [],
  fieldIssues: [],
  returnCodeIssues: [],
  pathIssues: [],
  warnings: [],
  summary: {
    totalFrontendCalls: 0,
    totalBackendAPIs: 0,
    totalErrors: 0,
    totalWarnings: 0
  }
};

/**
 * Extract API calls from JavaScript/HTML files
 */
function extractFrontendAPICalls(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf8');
    const fileName = path.relative(FRONTEND_DIR, filePath);
    
    // Skip backup files
    if (fileName.includes('backup') || fileName.includes('.bak') || 
        fileName.includes('node_modules') || fileName.includes('.old')) {
      return;
    }
    
    const calls = [];
    
    // Pattern 1: axios/fetch calls with URL paths
    const axiosPattern = /(?:axios\.|fetch\(|\.get\(|\.post\(|\.put\(|\.delete\()['"](\/[^'"]+)['"]/g;
    let match;
    
    while ((match = axiosPattern.exec(content)) !== null) {
      const apiPath = match[1];
      const lineNumber = content.substring(0, match.index).split('\n').length;
      
      calls.push({
        file: fileName,
        line: lineNumber,
        path: apiPath,
        type: 'axios/fetch',
        context: content.substring(Math.max(0, match.index - 50), match.index + 100)
      });
    }
    
    // Pattern 2: API_CONFIG.baseURL + path
    const baseURLPattern = /API_CONFIG\.baseURL\s*\+\s*['"]([^'"]+)['"]/g;
    while ((match = baseURLPattern.exec(content)) !== null) {
      const apiPath = match[1];
      const lineNumber = content.substring(0, match.index).split('\n').length;
      
      calls.push({
        file: fileName,
        line: lineNumber,
        path: apiPath,
        type: 'API_CONFIG',
        context: content.substring(Math.max(0, match.index - 50), match.index + 100)
      });
    }
    
    // Pattern 3: Direct URL strings in API calls
    const urlPattern = /['"]https?:\/\/[^'"]*\/([^'"?#]+)['"][,\s]*\{/g;
    while ((match = urlPattern.exec(content)) !== null) {
      const apiPath = '/' + match[1];
      const lineNumber = content.substring(0, match.index).split('\n').length;
      
      calls.push({
        file: fileName,
        line: lineNumber,
        path: apiPath,
        type: 'direct-url',
        context: content.substring(Math.max(0, match.index - 50), match.index + 100)
      });
    }
    
    // Check return code handling
    const codeCheckPattern = /(?:code\s*===\s*(\d+)|response\.code\s*===\s*(\d+))/g;
    while ((match = codeCheckPattern.exec(content)) !== null) {
      const code = match[1] || match[2];
      const lineNumber = content.substring(0, match.index).split('\n').length;
      
      // Check if using both 1 and 200 (recommended)
      const hasCodeOne = /code\s*===\s*1/.test(content);
      const hasCode200 = /code\s*===\s*200/.test(content);
      const hasOrCheck = /code\s*===\s*1\s*\|\|\s*code\s*===\s*200/.test(content);
      
      if (!hasOrCheck && (hasCodeOne || hasCode200)) {
        results.returnCodeIssues.push({
          file: fileName,
          line: lineNumber,
          issue: `Only checking for code === ${code}, should use: code === 1 || code === 200`,
          severity: 'warning'
        });
      }
    }
    
    if (calls.length > 0) {
      results.frontendAPICalls.push(...calls);
      results.summary.totalFrontendCalls += calls.length;
    }
    
  } catch (error) {
    results.warnings.push(`Error reading ${filePath}: ${error.message}`);
  }
}

/**
 * Extract API implementations from PHP files
 */
function extractBackendAPIs(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf8');
    const fileName = path.relative(BACKEND_API_DIR, filePath);
    
    // Skip backup files
    if (fileName.includes('backup') || fileName.includes('.bak')) {
      return;
    }
    
    // Determine API route from file path and structure
    const route = inferAPIRoute(filePath, content);
    
    // Extract fields from PHP code
    const fields = extractPHPFields(content);
    
    // Check response format
    const responseFormat = checkPHPResponseFormat(content);
    
    results.backendAPIs.push({
      file: fileName,
      route: route,
      fields: fields,
      responseFormat: responseFormat
    });
    
    results.summary.totalBackendAPIs++;
    
  } catch (error) {
    results.warnings.push(`Error reading ${filePath}: ${error.message}`);
  }
}

/**
 * Infer API route from file path and content
 */
function inferAPIRoute(filePath, content) {
  // Get relative path from api directory
  const relativePath = path.relative(BACKEND_API_DIR, filePath);
  
  // Convert file path to API route
  // e.g., user/info.php -> /user/user/index or similar
  const pathParts = relativePath.replace('.php', '').split(path.sep);
  
  // Check if there's a route definition in the file
  const routeMatch = content.match(/\$_SERVER\['REQUEST_URI'\].*['"]([^'"]+)['"]/);
  if (routeMatch) {
    return routeMatch[1];
  }
  
  // Common pattern mappings based on the checklist document
  const route = '/' + pathParts.join('/');
  return route;
}

/**
 * Extract fields from PHP code
 */
function extractPHPFields(content) {
  const fields = {
    input: [],
    output: []
  };
  
  // Extract input fields from $_POST, $_GET, $data
  const inputPattern = /\$_(POST|GET)\[['"]([^'"]+)['"]\]/g;
  let match;
  
  while ((match = inputPattern.exec(content)) !== null) {
    if (!fields.input.includes(match[2])) {
      fields.input.push(match[2]);
    }
  }
  
  // Extract output fields from json response
  const outputPattern = /['"]([a-z_]+)['"]\s*=>\s*\$/g;
  while ((match = outputPattern.exec(content)) !== null) {
    if (!fields.output.includes(match[1])) {
      fields.output.push(match[1]);
    }
  }
  
  return fields;
}

/**
 * Check PHP response format
 */
function checkPHPResponseFormat(content) {
  const hasCode1 = /['"]code['"]\s*=>\s*1/.test(content);
  const hasCode200 = /['"]code['"]\s*=>\s*200/.test(content);
  const hasCode0 = /['"]code['"]\s*=>\s*0/.test(content);
  
  return {
    usesCode1: hasCode1,
    usesCode200: hasCode200,
    usesCode0: hasCode0,
    usesMessage: /['"]message['"]\s*=>/.test(content),
    usesData: /['"]data['"]\s*=>/.test(content)
  };
}

/**
 * Cross-validate frontend calls with backend APIs
 */
function validateAPIs() {
  console.log('\n🔍 Validating API integration...\n');
  
  // Build backend API map for quick lookup
  const backendMap = new Map();
  results.backendAPIs.forEach(api => {
    backendMap.set(api.route, api);
  });
  
  // Check each frontend call
  results.frontendAPICalls.forEach(call => {
    const path = call.path;
    
    // Normalize path (remove query params, trailing slash)
    const normalizedPath = path.split('?')[0].replace(/\/$/, '');
    
    // Check if backend API exists
    const matchingBackend = findMatchingBackendAPI(normalizedPath, backendMap);
    
    if (!matchingBackend) {
      results.mismatches.push({
        type: 'missing-backend',
        frontendFile: call.file,
        frontendLine: call.line,
        apiPath: path,
        severity: 'error',
        message: `Frontend calls ${path} but no matching backend API found`
      });
      results.summary.totalErrors++;
    }
  });
  
  // Check for unused backend APIs
  const usedBackendPaths = new Set(results.frontendAPICalls.map(c => c.path));
  results.backendAPIs.forEach(api => {
    if (!isAPIUsed(api.route, usedBackendPaths)) {
      results.warnings.push({
        type: 'unused-backend',
        backendFile: api.file,
        apiRoute: api.route,
        severity: 'info',
        message: `Backend API ${api.route} exists but may not be used by frontend`
      });
      results.summary.totalWarnings++;
    }
  });
}

/**
 * Find matching backend API (fuzzy matching)
 */
function findMatchingBackendAPI(path, backendMap) {
  // Direct match
  if (backendMap.has(path)) {
    return backendMap.get(path);
  }
  
  // Try variations
  const variations = [
    path,
    path + '.php',
    path.replace(/^\//, ''),
    path.replace(/\//g, '-'),
  ];
  
  for (const variant of variations) {
    if (backendMap.has(variant)) {
      return backendMap.get(variant);
    }
  }
  
  // Fuzzy match by checking if any backend route contains the path
  for (const [route, api] of backendMap) {
    if (route.includes(path) || path.includes(route)) {
      return api;
    }
  }
  
  return null;
}

/**
 * Check if API is used
 */
function isAPIUsed(route, usedPaths) {
  for (const path of usedPaths) {
    if (path.includes(route) || route.includes(path)) {
      return true;
    }
  }
  return false;
}

/**
 * Recursively scan directory
 */
function scanDirectory(dir, callback, extensions = []) {
  if (!fs.existsSync(dir)) {
    console.warn(`⚠️  Directory not found: ${dir}`);
    return;
  }
  
  const files = fs.readdirSync(dir);
  
  files.forEach(file => {
    const filePath = path.join(dir, file);
    let stat;
    
    try {
      stat = fs.lstatSync(filePath);
      
      // Skip symbolic links
      if (stat.isSymbolicLink()) {
        return;
      }
      
      // For directories and files, use statSync to follow any links
      stat = fs.statSync(filePath);
    } catch (error) {
      // Skip files that can't be accessed
      console.warn(`⚠️  Cannot access: ${filePath}`);
      return;
    }
    
    if (stat.isDirectory()) {
      // Skip certain directories
      if (!file.startsWith('.') && file !== 'node_modules' && file !== 'vendor' && file !== 'backup' && file !== 'backups') {
        scanDirectory(filePath, callback, extensions);
      }
    } else if (stat.isFile()) {
      const ext = path.extname(file);
      if (extensions.length === 0 || extensions.includes(ext)) {
        callback(filePath);
      }
    }
  });
}

/**
 * Generate markdown report
 */
function generateReport() {
  console.log('\n📝 Generating report...\n');
  
  let report = `# Providence API Integration Validation Report

**Generated**: ${new Date().toISOString()}
**Scan Type**: Frontend-Backend API Integration Check

---

## 📊 Summary

| Metric | Count |
|--------|-------|
| Frontend API Calls Found | ${results.summary.totalFrontendCalls} |
| Backend APIs Found | ${results.summary.totalBackendAPIs} |
| **Errors Found** | **${results.summary.totalErrors}** |
| Warnings | ${results.summary.totalWarnings} |

---

## ❌ Critical Errors

`;

  if (results.mismatches.length === 0) {
    report += '✅ No critical API mismatches found!\n\n';
  } else {
    report += `Found ${results.mismatches.length} API mismatches:\n\n`;
    
    results.mismatches.forEach((mismatch, idx) => {
      report += `### ${idx + 1}. ${mismatch.message}\n\n`;
      report += `- **Type**: ${mismatch.type}\n`;
      report += `- **Severity**: ${mismatch.severity}\n`;
      report += `- **Frontend File**: ${mismatch.frontendFile}:${mismatch.frontendLine}\n`;
      report += `- **API Path**: \`${mismatch.apiPath}\`\n\n`;
    });
  }

  report += `---

## ⚠️ Return Code Issues

`;

  if (results.returnCodeIssues.length === 0) {
    report += '✅ No return code handling issues found!\n\n';
  } else {
    report += `Found ${results.returnCodeIssues.length} return code issues:\n\n`;
    
    results.returnCodeIssues.forEach((issue, idx) => {
      report += `### ${idx + 1}. ${issue.file}:${issue.line}\n\n`;
      report += `- **Issue**: ${issue.issue}\n`;
      report += `- **Severity**: ${issue.severity}\n`;
      report += `- **Recommendation**: Use \`code === 1 || code === 200\` for compatibility\n\n`;
    });
  }

  report += `---

## ℹ️ Warnings

`;

  if (results.warnings.length === 0) {
    report += '✅ No warnings!\n\n';
  } else {
    report += `Found ${results.warnings.length} warnings:\n\n`;
    
    results.warnings.slice(0, 20).forEach((warning, idx) => {
      if (typeof warning === 'string') {
        report += `${idx + 1}. ${warning}\n`;
      } else {
        report += `${idx + 1}. [${warning.type}] ${warning.message}\n`;
        if (warning.backendFile) {
          report += `   - Backend: ${warning.backendFile}\n`;
        }
      }
    });
    
    if (results.warnings.length > 20) {
      report += `\n... and ${results.warnings.length - 20} more warnings\n`;
    }
  }

  report += `\n---

## 📋 Frontend API Calls (Sample)

`;

  // Show first 30 unique API paths
  const uniquePaths = [...new Set(results.frontendAPICalls.map(c => c.path))];
  report += `Total unique API paths called: ${uniquePaths.length}\n\n`;
  
  uniquePaths.slice(0, 30).forEach(path => {
    const calls = results.frontendAPICalls.filter(c => c.path === path);
    report += `- \`${path}\` (called in ${calls.length} place(s))\n`;
  });
  
  if (uniquePaths.length > 30) {
    report += `\n... and ${uniquePaths.length - 30} more API paths\n`;
  }

  report += `\n---

## 📋 Backend APIs (Sample)

`;

  results.backendAPIs.slice(0, 30).forEach(api => {
    report += `### ${api.file}\n\n`;
    report += `- **Route**: \`${api.route}\`\n`;
    report += `- **Response Format**: \n`;
    report += `  - Uses code=1: ${api.responseFormat.usesCode1 ? '✅' : '❌'}\n`;
    report += `  - Uses code=200: ${api.responseFormat.usesCode200 ? '✅' : '❌'}\n`;
    report += `  - Has message field: ${api.responseFormat.usesMessage ? '✅' : '❌'}\n`;
    report += `  - Has data field: ${api.responseFormat.usesData ? '✅' : '❌'}\n`;
    if (api.fields.input.length > 0) {
      report += `- **Input Fields**: ${api.fields.input.join(', ')}\n`;
    }
    report += `\n`;
  });
  
  if (results.backendAPIs.length > 30) {
    report += `\n... and ${results.backendAPIs.length - 30} more backend APIs\n`;
  }

  report += `\n---

## 🎯 Recommendations

Based on the scan results:

1. **API Path Consistency**: Ensure all frontend API calls have matching backend implementations
2. **Return Code Handling**: Use \`code === 1 || code === 200\` for maximum compatibility
3. **Field Validation**: Verify that all required fields are sent from frontend and processed by backend
4. **Error Handling**: Ensure proper error responses are handled on frontend
5. **Documentation**: Update API documentation for any new or changed endpoints

---

## 📚 Reference Documents

This scan was based on requirements from:
- ✅上线前最终检查清单.md
- 生产环境终极检查报告.md
- 修复完成报告.md

---

**Scan completed successfully** ✅
`;

  return report;
}

/**
 * Main execution
 */
function main() {
  console.log('🚀 Starting Providence API Integration Validation Scanner\n');
  console.log('📂 Frontend Directory:', FRONTEND_DIR);
  console.log('📂 Backend API Directory:', BACKEND_API_DIR);
  console.log('');
  
  // Scan frontend files
  console.log('🔍 Scanning frontend files...');
  scanDirectory(FRONTEND_DIR, extractFrontendAPICalls, ['.html', '.js']);
  console.log(`✅ Found ${results.summary.totalFrontendCalls} API calls in frontend\n`);
  
  // Scan backend files
  console.log('🔍 Scanning backend API files...');
  scanDirectory(BACKEND_API_DIR, extractBackendAPIs, ['.php']);
  console.log(`✅ Found ${results.summary.totalBackendAPIs} API implementations in backend\n`);
  
  // Validate
  validateAPIs();
  
  // Generate report
  const report = generateReport();
  
  // Write report
  fs.writeFileSync(REPORT_OUTPUT, report, 'utf8');
  console.log(`✅ Report generated: ${REPORT_OUTPUT}\n`);
  
  // Print summary
  console.log('═══════════════════════════════════════');
  console.log('📊 SCAN SUMMARY');
  console.log('═══════════════════════════════════════');
  console.log(`Frontend API Calls: ${results.summary.totalFrontendCalls}`);
  console.log(`Backend APIs: ${results.summary.totalBackendAPIs}`);
  console.log(`Errors: ${results.summary.totalErrors}`);
  console.log(`Warnings: ${results.summary.totalWarnings}`);
  console.log('═══════════════════════════════════════\n');
  
  if (results.summary.totalErrors > 0) {
    console.log('❌ Issues found! Please review the report.');
    process.exit(1);
  } else {
    console.log('✅ No critical issues found!');
    process.exit(0);
  }
}

// Run the scanner
if (require.main === module) {
  main();
}

module.exports = { main, results };
