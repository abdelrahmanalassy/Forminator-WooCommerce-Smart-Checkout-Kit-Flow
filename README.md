# 🧾 Instant Checkout via Forminator

Automatically adds WooCommerce products to the cart upon **Forminator form submission** and redirects the user to the **checkout page**. Ideal for registration flows, competitions, or onboarding products.

---

## ✅ Features

- 🔄 Auto-add predefined and user-selected products to the WooCommerce cart after successful Forminator form submission.
- 🛒 Supports multiple product additions (e.g., fixed registration product + optional kit).
- 🧠 Detects successful form submission via DOM mutation (no need to hook into PHP submission events).
- 🧼 Automatically clears the cart before adding products to avoid duplicates.
- 🚀 Instantly redirects the user to the WooCommerce checkout page.
- 💾 Saves selected product IDs and Forminator submission ID using cookies and session storage.
- 🖼 Displays a user-friendly "Processing..." message to improve UX.
- 📦 Fully compatible with **Forminator Pro** (WPMU DEV) and **WooCommerce**.

---

## 📌 Requirements

- WordPress 6.0 or later
- [WooCommerce](https://woocommerce.com/)
- [Forminator Pro](https://wpmudev.com/project/forminator-pro/)

---

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin from your WordPress dashboard under **Plugins > Installed Plugins**.
3. Ensure both **Forminator** and **WooCommerce** are active.
4. Add a **select/dropdown field** in your Forminator form that holds WooCommerce product IDs (e.g., kits).
6. No additional settings are required — the plugin will automatically detect form success and handle the rest.

---

## 📘 Usage Guide

### 1. Form Setup

- Create your form in **Forminator**.
- Include the following fields:
  - **Select field** for kit options (with WooCommerce Product IDs as values).

### 2. Frontend Flow

Upon form submission:
- The plugin detects the success message.
- Injects the Forminator submission ID into a hidden field.
- Clears the WooCommerce cart.
- Adds:
  - A **fixed registration product** (hardcoded as product ID `1384` in `redirect-final.js`)
  - A **dynamic product** based on the selected kit.
- Displays a “We are processing your registration…” message.
- Redirects the user to `/checkout`.

---

## 🔐 Backend Endpoints

The plugin listens for these backend events:

- `?empty-cart=yes`: Used to empty the WooCommerce cart (via PHP in `watch-new-order.php`).
- `?wc-ajax=add_to_cart`: Standard WooCommerce endpoint used by JS to add products via AJAX.

---

## 📤 Google Sheets Integration (Updated)

If you want to **track WooCommerce orders** based on Forminator form submissions, use the following **Google Apps Script** to automatically update Google Sheets rows based on user email.

### ✅ What It Does (Updated Logic)

- Accepts a POST request with `email`, `amount`, `order_id`, and `status`.
- **Searches from bottom to top** in your Google Sheet (Column **S**, index 19) to find the **most recent matching email**.
- Once matched, it updates:
  - 🆔 **Order ID** in **Column D**
  - 💵 **Amount** in **Column E**
  - 📦 **Status** in **Column F**

This avoids overwriting old data when a user submits multiple entries with the same email (e.g., during retry or multi-step registration).

---

### 💡 Use Case

Perfect for:
- Updating participant status after WooCommerce checkout
- Keeping Google Sheets in sync with the most **recent** order per user
- Avoiding accidental updates to earlier records

---

### 🔧 Setup Instructions

1. Open [Google Apps Script](https://script.google.com/) and create a new project.
2. Link it to your target Google Sheet.
3. Paste the updated script below.
4. Deploy as a **Web App**:
   - **Execute as**: Me  
   - **Access**: Anyone (even anonymous)
5. Copy the Web App URL — use it as a webhook endpoint in your WooCommerce hook/PHP file.

---

### 🧠 Script

```javascript
function doPost(e) {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var data = JSON.parse(e.postData.contents);

  var email   = String(data.email || "").toLowerCase().trim();
  var amount  = data.amount;
  var status  = data.status;
  var orderId = data.order_id;

  var lastRow = sheet.getLastRow();
  Logger.log("📌 lastRow: " + lastRow);

  var emailColumn = sheet.getRange(1, 19, lastRow).getValues(); // Column S
  var targetRow = -1;

  for (var i = lastRow - 1; i >= 1; i--) {
    var sheetEmailRaw = emailColumn[i][0];

    if (sheetEmailRaw) {
      var sheetEmail = String(sheetEmailRaw).toLowerCase().trim();
      Logger.log("🔍 Comparing row " + (i + 1) + ": " + sheetEmail + " === " + email);

      if (sheetEmail === email) {
        targetRow = i + 1;
        Logger.log("✅ Match found at row " + targetRow);
        break;
      }
    }
  }

  if (targetRow !== -1) {
    sheet.getRange(targetRow, 4).setValue(orderId);  // D
    sheet.getRange(targetRow, 5).setValue(amount);   // E
    sheet.getRange(targetRow, 6).setValue(status);   // F
    Logger.log("✏️ Updated row: " + targetRow);
  } else {
    Logger.log("❌ No match for email: " + email);
  }

  return ContentService.createTextOutput("Processed for email: " + email);
}

```
## 🧠 Auto-Translate Kit IDs in Google Sheets

If you're collecting **WooCommerce product IDs** (e.g., selected kits) via Forminator and storing them in Google Sheets, this Apps Script automatically **replaces those numeric IDs with human-readable product names** — directly inside the sheet.

### ✅ What It Does

- Monitors **Column M** for incoming product codes.
- Replaces numeric product IDs like `1385` with corresponding names like `"Drive Pack"`.
- Runs automatically on any sheet change — including entries pushed by Forminator.
- Modifies the cell in-place (no need for extra columns or formulas).

### 🧩 Use Case

Great for:
- Making submission data readable and export-friendly.
- Replacing internal product codes with names for your team or reports.
- Eliminating the need for VLOOKUP or helper columns.

---

### 🔧 Setup Instructions

1. Open your target Google Sheet.
2. Go to **Extensions > Apps Script**.
3. Paste the following script:

```javascript
function onChange(e) {
  const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  const range = sheet.getRange("M2:M"); // Assuming data starts from row 2 in column M
  const values = range.getValues();

  const codeMap = {
    1385: "Product-1",
    1386: "Product-2",
    1387: "Product-3",
    1389: "Product-4"
  };

  for (let i = 0; i < values.length; i++) {
    const code = values[i][0];
    if (codeMap.hasOwnProperty(code)) {
      range.getCell(i + 1, 1).setValue(codeMap[code]);
    }
  }
}
```

## 🧩 File Overview

| File | Description |
|------|-------------|
| `forminator-kit-redirect.php` | Main plugin bootstrapper for WordPress |
| `redirect-final.js` | Handles DOM watching, cart logic, and redirect |
| `watch-new-order.php` | Backend logic to support cart clearing on query param `empty-cart` |

---

## 📸 Example Flow

1. User selects a kit from a dropdown in the form.
2. On success:
   - “Thank you” message is detected.
   - Cart is cleared.
   - Registration product and selected kit are added.
   - User is redirected to `/checkout`.

---

## 🔗 Author Info

- 🧑‍💻 **Developer**: [Abdelrahman Ashraf](https://www.linkedin.com/in/abdelrahman-ashraf-elassy/)
- 🌐 **Website**: [AE Projects](https://aeprojects.org/)

---

## 🧠 Notes & Tips

- 🔒 If you're storing submission IDs for reporting or validation, ensure they're also stored in your backend.
- 💡 Works perfectly for registration forms in competitions, workshops, educational kits, and more.
- ⚠️ Make sure the form ID (`#forminator-module-1433`) and field name (`select-3`) match your actual form configuration.

---

## 🛡 License

MIT License © 2025 Abdelrahman Ashraf
