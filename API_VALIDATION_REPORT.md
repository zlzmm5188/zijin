# Providence API Integration Validation Report

**Generated**: 2025-11-19T10:52:24.694Z
**Scan Type**: Frontend-Backend API Integration Check

---

## 📊 Summary

| Metric | Count |
|--------|-------|
| Frontend API Calls Found | 41 |
| Backend APIs Found | 113 |
| **Errors Found** | **14** |
| Warnings | 94 |

---

## ❌ Critical Errors

Found 14 API mismatches:

### 1. Frontend calls /idcard but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: ai-password-reset.js:170
- **API Path**: `/idcard`

### 2. Frontend calls /face-idcard but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: ai-password-reset.js:313
- **API Path**: `/face-idcard`

### 3. Frontend calls /reset-verified but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: ai-password-reset.js:435
- **API Path**: `/reset-verified`

### 4. Frontend calls /exchange-history but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: points-history.html:207
- **API Path**: `/exchange-history`

### 5. Frontend calls /submitFaceForReview but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: reset-password-new.html:690
- **API Path**: `/submitFaceForReview`

### 6. Frontend calls /resetPassword but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: reset-password-new.html:776
- **API Path**: `/resetPassword`

### 7. Frontend calls /submitFaceForReview but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: reset-password-optimized.html:732
- **API Path**: `/submitFaceForReview`

### 8. Frontend calls /resetPassword but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: reset-password-optimized.html:816
- **API Path**: `/resetPassword`

### 9. Frontend calls /checkUsername but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: reset-password.html:480
- **API Path**: `/checkUsername`

### 10. Frontend calls /submitFaceForReview but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: reset-password.html:539
- **API Path**: `/submitFaceForReview`

### 11. Frontend calls /resetPassword but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: reset-password.html:593
- **API Path**: `/resetPassword`

### 12. Frontend calls /transfer-in but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: ribao-dual-currency.html:1058
- **API Path**: `/transfer-in`

### 13. Frontend calls /kyc-face-verify.php but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: test-kyc-api.html:130
- **API Path**: `/kyc-face-verify.php`

### 14. Frontend calls /kyc-face-verify.php but no matching backend API found

- **Type**: missing-backend
- **Severity**: error
- **Frontend File**: test-kyc-token.html:95
- **API Path**: `/kyc-face-verify.php`

---

## ⚠️ Return Code Issues

Found 200 return code issues:

### 1. ai-hybrid-dispatcher.js:57

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 2. ai-hybrid-dispatcher.js:57

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 3. ai-password-reset.js:183

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 4. ai-password-reset.js:327

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 5. ai-password-reset.js:449

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 6. ai-service-local.js:52

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 7. ai-service-local.js:280

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 8. ai-service-local.js:316

- **Issue**: Only checking for code === 501, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 9. ai-service-local.js:425

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 10. ai-service-local.js:444

- **Issue**: Only checking for code === 501, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 11. app.js:52

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 12. app.js:101

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 13. app.js:117

- **Issue**: Only checking for code === 401, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 14. bank-cards.html:548

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 15. bank-cards.html:551

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 16. bank-cards.html:644

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 17. bank-cards.html:810

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 18. checkin.js:102

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 19. checkin.js:145

- **Issue**: Only checking for code === 501, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 20. daily-checkin-api.js:84

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 21. daily-checkin-api.js:198

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 22. daily-checkin.html:527

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 23. daily-checkin.html:573

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 24. deposit.js:90

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 25. deposit.js:347

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 26. deposit.js:769

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 27. forgot.js:61

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 28. forgot.js:61

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 29. forgot.js:137

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 30. invite-share.html:854

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 31. invite-share.html:887

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 32. invite-share.html:971

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 33. js/points-exchange.js:68

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 34. js/points-exchange.js:214

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 35. kyc-verification-ocr.js:566

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 36. login-debug.html:254

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 37. login-standalone.html:263

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 38. login.html:545

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 39. login.html:545

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 40. login.js:209

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 41. login.js:209

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 42. login.js:303

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 43. login.js:303

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 44. login.js:353

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 45. login.js:353

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 46. my-investments.html:418

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 47. my-investments.html:418

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 48. my-investments.js:94

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 49. points-exchange-history.html:206

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 50. points-exchange-original.html:628

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 51. points-exchange-original.html:707

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 52. points-exchange.html:774

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 53. points-exchange.html:872

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 54. points-history.html:222

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 55. points-logs.html:325

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 56. points-record.html:211

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 57. profile.js:128

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 58. profile.js:157

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 59. profile.js:196

- **Issue**: Only checking for code === 501, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 60. profile.js:349

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 61. profile.js:602

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 62. profit-calendar.html:430

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 63. profit-calendar.js:84

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 64. project-detail-tailwind.html:180

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 65. project-detail-tailwind.html:193

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 66. project-detail-tailwind.html:324

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 67. project-detail.js:113

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 68. project-detail.js:113

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 69. project-detail.js:312

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 70. projects-list.html:432

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 71. projects.html:819

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 72. projects.html:819

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 73. projects.html:925

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 74. recharge-new.html:320

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 75. recharge-new.html:389

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 76. recharge-new.html:413

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 77. recharge.html:1164

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 78. recharge.html:1320

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 79. recharge.html:1586

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 80. recharge.html:1635

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 81. recharge.html:1795

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 82. recharge.html:1883

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 83. recharge.html:1925

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 84. recharge.html:2031

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 85. records.html:398

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 86. records.html:398

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 87. register.js:326

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 88. register.js:326

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 89. reset-password-local.html:629

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 90. reset-password-local.html:629

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 91. reset-password-local.html:882

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 92. reset-password-local.html:882

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 93. reset-password-local.html:976

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 94. reset-password-local.html:976

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 95. reset-password-local.html:1012

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 96. reset-password-new.html:703

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 97. reset-password-new.html:790

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 98. reset-password-optimized.html:745

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 99. reset-password-optimized.html:830

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 100. reset-password.html:489

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 101. reset-password.html:548

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 102. reset-password.html:605

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 103. reset-password.js:117

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 104. reset-password.js:234

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 105. ribao-debug.html:179

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 106. ribao-debug.html:182

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 107. ribao-dual-currency.html:828

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 108. ribao-dual-currency.html:854

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 109. ribao-dual-currency.html:1036

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 110. ribao-dual-currency.html:1079

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 111. ribao-history.html:371

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 112. ribao-history.html:371

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 113. ribao-new.html:866

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 114. ribao-new.html:866

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 115. ribao-new.html:909

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 116. ribao-new.html:909

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 117. ribao-new.html:1025

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 118. ribao-new.html:1025

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 119. ribao.html:1072

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 120. ribao.html:1322

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 121. ribao.html:1349

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 122. ribao.html:1349

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 123. ribao.html:1421

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 124. ribao.js:82

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 125. ribao.js:94

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 126. ribao.js:146

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 127. ribao.js:419

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 128. ribao.js:482

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 129. ribao_CLEAN_1762914403.html:633

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 130. ribao_CLEAN_1762914403.html:691

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 131. ribao_CLEAN_1762914403.html:716

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 132. ribao_FINAL.html:595

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 133. ribao_FINAL.html:653

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 134. ribao_FINAL.html:678

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 135. ribao_FIXED_1762913533.html:942

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 136. ribao_FIXED_1762913533.html:942

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 137. ribao_FIXED_1762913533.html:991

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 138. ribao_FIXED_1762913533.html:991

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 139. ribao_FIXED_1762913533.html:1107

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 140. ribao_FIXED_1762913533.html:1107

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 141. ribao_HOME_STYLE.html:528

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 142. ribao_HOME_STYLE.html:592

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 143. ribao_HOME_STYLE.html:618

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 144. ribao_LAYER_1762913467.html:890

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 145. ribao_LAYER_1762913467.html:890

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 146. ribao_LAYER_1762913467.html:939

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 147. ribao_LAYER_1762913467.html:939

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 148. ribao_LAYER_1762913467.html:1055

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 149. ribao_LAYER_1762913467.html:1055

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 150. ribao_PROJECTS_STYLE.html:641

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 151. ribao_PROJECTS_STYLE.html:699

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 152. ribao_PROJECTS_STYLE.html:724

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 153. ribao_REDESIGN_1762912971.html:878

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 154. ribao_REDESIGN_1762912971.html:878

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 155. ribao_REDESIGN_1762912971.html:927

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 156. ribao_REDESIGN_1762912971.html:927

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 157. ribao_REDESIGN_1762912971.html:1043

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 158. ribao_REDESIGN_1762912971.html:1043

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 159. ribao_final_1762911512.html:867

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 160. ribao_final_1762911512.html:867

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 161. ribao_final_1762911512.html:910

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 162. ribao_final_1762911512.html:910

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 163. ribao_final_1762911512.html:1026

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 164. ribao_final_1762911512.html:1026

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 165. ribao_test_1762911321.html:867

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 166. ribao_test_1762911321.html:867

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 167. ribao_test_1762911321.html:910

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 168. ribao_test_1762911321.html:910

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 169. ribao_test_1762911321.html:1026

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 170. ribao_test_1762911321.html:1026

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 171. ribao_v2_1762912759.html:874

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 172. ribao_v2_1762912759.html:874

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 173. ribao_v2_1762912759.html:923

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 174. ribao_v2_1762912759.html:923

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 175. ribao_v2_1762912759.html:1039

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 176. ribao_v2_1762912759.html:1039

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 177. set-pay-password.html:405

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 178. set-pay-password.html:536

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 179. set-pay-password.html:536

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 180. team-rewards.js:79

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 181. team-rewards.js:191

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 182. test-ai.html:62

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 183. test-ai.html:62

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 184. test-api.html:93

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 185. test-config.html:263

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 186. test-config.html:263

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 187. tp/recharge.html:687

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 188. tp/recharge.html:822

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 189. trial-money-popup.js:391

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 190. trial-money.html:445

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 191. vip-level.html:431

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 192. vip-level.html:431

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 193. vip-level.html:457

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 194. vip-level.html:457

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 195. withdraw.html:981

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 196. withdraw.html:1057

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 197. withdraw.html:1083

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 198. withdraw.html:1202

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 199. withdraw.html:1327

- **Issue**: Only checking for code === 1, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

### 200. zone-detail.js:88

- **Issue**: Only checking for code === 200, should use: code === 1 || code === 200
- **Severity**: warning
- **Recommendation**: Use `code === 1 || code === 200` for compatibility

---

## ℹ️ Warnings

Found 94 warnings:

1. [unused-backend] Backend API /admin/categories exists but may not be used by frontend
   - Backend: admin/categories.php
2. [unused-backend] Backend API /admin/category-save exists but may not be used by frontend
   - Backend: admin/category-save.php
3. [unused-backend] Backend API /admin/clear-all-data exists but may not be used by frontend
   - Backend: admin/clear-all-data.php
4. [unused-backend] Backend API /admin/clear-data exists but may not be used by frontend
   - Backend: admin/clear-data.php
5. [unused-backend] Backend API /admin/kyc-approve exists but may not be used by frontend
   - Backend: admin/kyc-approve.php
6. [unused-backend] Backend API /admin/kyc-reject exists but may not be used by frontend
   - Backend: admin/kyc-reject.php
7. [unused-backend] Backend API /admin/login-logs exists but may not be used by frontend
   - Backend: admin/login-logs.php
8. [unused-backend] Backend API /admin/order-detail exists but may not be used by frontend
   - Backend: admin/order-detail.php
9. [unused-backend] Backend API /admin/orders exists but may not be used by frontend
   - Backend: admin/orders.php
10. [unused-backend] Backend API /admin/project-config exists but may not be used by frontend
   - Backend: admin/project-config.php
11. [unused-backend] Backend API /admin/project-delete exists but may not be used by frontend
   - Backend: admin/project-delete.php
12. [unused-backend] Backend API /admin/project-detail exists but may not be used by frontend
   - Backend: admin/project-detail.php
13. [unused-backend] Backend API /admin/project-save exists but may not be used by frontend
   - Backend: admin/project-save.php
14. [unused-backend] Backend API /admin/projects exists but may not be used by frontend
   - Backend: admin/projects.php
15. [unused-backend] Backend API /admin/recharge-approve exists but may not be used by frontend
   - Backend: admin/recharge-approve.php
16. [unused-backend] Backend API /admin/recharge-reject exists but may not be used by frontend
   - Backend: admin/recharge-reject.php
17. [unused-backend] Backend API /admin/recharges exists but may not be used by frontend
   - Backend: admin/recharges.php
18. [unused-backend] Backend API /admin/ribao-config-save exists but may not be used by frontend
   - Backend: admin/ribao-config-save.php
19. [unused-backend] Backend API /admin/ribao-config exists but may not be used by frontend
   - Backend: admin/ribao-config.php
20. [unused-backend] Backend API /admin/ribao-profits exists but may not be used by frontend
   - Backend: admin/ribao-profits.php

... and 74 more warnings

---

## 📋 Frontend API Calls (Sample)

Total unique API paths called: 32

- `/idcard` (called in 1 place(s))
- `/face-idcard` (called in 1 place(s))
- `/reset-verified` (called in 1 place(s))
- `/index.php/user/ribao/info` (called in 1 place(s))
- `/index.php/user/ribao/transfer-in` (called in 1 place(s))
- `/index.php/user/ribao/transfer-out` (called in 1 place(s))
- `/index.php/user/ribao/records` (called in 1 place(s))
- `/index.php/user/points/balance` (called in 1 place(s))
- `/index.php/user/points/exchange` (called in 2 place(s))
- `/index.php/user/points/logs` (called in 1 place(s))
- `/index.php/user/user/index` (called in 2 place(s))
- `/index.php/user/recharge/add` (called in 1 place(s))
- `/index.php/pay/pay/withdraw` (called in 1 place(s))
- `/index.php/pay/bank/list` (called in 1 place(s))
- `/index.php/pay/us/info` (called in 1 place(s))
- `/account` (called in 1 place(s))
- `/index` (called in 1 place(s))
- `/invite` (called in 1 place(s))
- `/team` (called in 1 place(s))
- `/index.php/user/kyc/submit` (called in 1 place(s))
- `/kyc-submit-simple` (called in 1 place(s))
- `/index.php/user/points-balance.php` (called in 1 place(s))
- `/index.php/user/points-exchange.php` (called in 1 place(s))
- `/exchange-history` (called in 1 place(s))
- `/logs` (called in 1 place(s))
- `/index.php/upload` (called in 1 place(s))
- `/submitFaceForReview` (called in 3 place(s))
- `/resetPassword` (called in 3 place(s))
- `/checkUsername` (called in 1 place(s))
- `/info` (called in 3 place(s))

... and 2 more API paths

---

## 📋 Backend APIs (Sample)

### admin/categories.php

- **Route**: `/admin/categories`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/category-save.php

- **Route**: `/admin/category-save`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/clear-all-data.php

- **Route**: `/admin/clear-all-data`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/clear-data.php

- **Route**: `/admin/clear-data`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/kyc-approve.php

- **Route**: `/admin/kyc-approve`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/kyc-reject.php

- **Route**: `/admin/kyc-reject`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/login-logs.php

- **Route**: `/admin/login-logs`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: page, limit, user_id, status, start_date, end_date, keyword

### admin/order-detail.php

- **Route**: `/admin/order-detail`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: id

### admin/orders.php

- **Route**: `/admin/orders`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: page, limit, status, currency, keyword, start_date, end_date

### admin/project-config.php

- **Route**: `/admin/project-config`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: id, is_index, sort, payment_type, vip_min, need_referral, team_member_required, mcount, status

### admin/project-delete.php

- **Route**: `/admin/project-delete`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/project-detail.php

- **Route**: `/admin/project-detail`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: id

### admin/project-save.php

- **Route**: `/admin/project-save`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/projects.php

- **Route**: `/admin/projects`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: page, limit, category, currency, status, keyword

### admin/recharge-approve.php

- **Route**: `/admin/recharge-approve`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/recharge-reject.php

- **Route**: `/admin/recharge-reject`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/recharges.php

- **Route**: `/admin/recharges`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: page, limit, status, currency, keyword

### admin/ribao-config-save.php

- **Route**: `/admin/ribao-config-save`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/ribao-config.php

- **Route**: `/admin/ribao-config`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/ribao-profits.php

- **Route**: `/admin/ribao-profits`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: page, limit, user_id, start_date, end_date

### admin/ribao-users.php

- **Route**: `/admin/ribao-users`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: page, limit, keyword, min_balance

### admin/stats.php

- **Route**: `/admin/stats`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/team-tree.php

- **Route**: `/admin/team-tree`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: user_id

### admin/user-detail.php

- **Route**: `/admin/user-detail`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: id

### admin/user-reset-password.php

- **Route**: `/admin/user-reset-password`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: user_id, new_password

### admin/user-set-internal.php

- **Route**: `/admin/user-set-internal`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌

### admin/user-team-tree.php

- **Route**: `/admin/user-team-tree`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: id

### admin/user-update.php

- **Route**: `/admin/user-update`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: id, phone, email, vip_level, status, is_internal, realname_status

### admin/users.php

- **Route**: `/admin/users`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ✅
- **Input Fields**: page, limit, keyword

### admin/wallet-logs.php

- **Route**: `/admin/wallet-logs`
- **Response Format**: 
  - Uses code=1: ❌
  - Uses code=200: ❌
  - Has message field: ❌
  - Has data field: ❌
- **Input Fields**: page, limit, user_id, type, currency, start_date, end_date


... and 83 more backend APIs

---

## 🎯 Recommendations

Based on the scan results:

1. **API Path Consistency**: Ensure all frontend API calls have matching backend implementations
2. **Return Code Handling**: Use `code === 1 || code === 200` for maximum compatibility
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
