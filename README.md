# 🧾 Forminator WooCommerce Smart Checkout Kit Flow

A complete solution for connecting Forminator form submissions with WooCommerce checkout. It supports dynamic kit selection, optional shipping, and seamless redirect, while integrating with Google Sheets for order tracking and product code translation.

---

## ✅ Features

- 🔄 Automatically adds WooCommerce products after Forminator form submission.
- 🎯 Adds fixed product (e.g., registration), optional kit, and shipping based on user input.
- 🧠 Detects success via DOM mutation — no PHP hooks needed.
- 🧼 Clears WooCommerce cart before adding new products to prevent duplicates.
- 🚀 Instantly redirects users to `/checkout`.
- 💬 Shows "Processing..." message for improved UX.
- 💾 Stores kit selection in `sessionStorage`.
- 📤 Optional backend endpoints for AJAX cart clearing.
- 📊 Google Sheets integration for tracking orders and replacing product codes with names.
- 👥 Role-based user logic (via optional file).

---

## 📌 Requirements

- WordPress 6.0 or later
- [WooCommerce](https://woocommerce.com/)
- [Forminator Pro](https://wpmudev.com/project/forminator-pro/)

---

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate it from **Plugins > Installed Plugins**.
3. Ensure both **Forminator** and **WooCommerce** are active.
4. Add a **select field** in your Forminator form to represent kit choices (e.g., `select-2`).
5. Add a **radio field** to ask whether the user wants a kit (e.g., `radio-1`).
6. You're ready to go — no extra configuration needed.

---

## 📘 Usage Guide

### 🧾 1. Forminator Form Setup

Include the following fields:

- 🧩 **Radio Field** to ask "Do you want a kit?" → `radio-1`
- 📦 **Select Field** to choose kit ID → `select-2` (values should be WooCommerce product IDs)

> **Note**: Adjust field names/IDs as needed. These are used in `redirect-final.js`.

---

### 🧪 2. Frontend Workflow

Once the form is submitted:

1. JavaScript detects the Forminator success message.
2. Shows a **"Processing..."** message inside the form.
3. Clears WooCommerce cart via `?empty-cart=yes`.
4. Adds:
   - ✅ Registration product (ID: `1384`)
   - 📦 Selected kit (if user selected "Yes")
   - 🚚 Shipping product (ID: `1511`)
5. Redirects the user to `/checkout`.

---

## 🖥 Backend Endpoints

The plugin uses lightweight PHP endpoints to handle cart operations:

- `?empty-cart=yes` → Clears cart (see `watch-new-order.php`)
- `?wc-ajax=add_to_cart` → Standard WooCommerce AJAX call to add products

> These are triggered via frontend JavaScript in `redirect-final.js`.

---

## 🧠 Google Sheets Integration (Optional)

Track WooCommerce orders based on Forminator submissions using **Google Apps Script**.

### ✅ What It Does

- Receives `POST` data: `email`, `amount`, `order_id`, `status`
- Searches **Column S** (index 19) from bottom to top to find latest matching email
- Updates:
  - 🆔 Column D → Order ID
  - 💵 Column E → Order Amount
  - 📦 Column F → Order Status

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

### 🔧 Setup

1. Open [Google Apps Script](https://script.google.com/).
2. Link your target Google Sheet.
3. Paste the following script:

```javascript
function doPost(e) {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var data = JSON.parse(e.postData.contents);

  var email   = String(data.email || "").toLowerCase().trim();
  var amount  = data.amount;
  var status  = data.status;
  var orderId = data.order_id;

  var lastRow = sheet.getLastRow();
  var emailColumn = sheet.getRange(1, 19, lastRow).getValues(); // Column S
  var targetRow = -1;

  for (var i = lastRow - 1; i >= 1; i--) {
    var sheetEmailRaw = emailColumn[i][0];
    if (sheetEmailRaw) {
      var sheetEmail = String(sheetEmailRaw).toLowerCase().trim();
      if (sheetEmail === email) {
        targetRow = i + 1;
        break;
      }
    }
  }

  if (targetRow !== -1) {
    sheet.getRange(targetRow, 4).setValue(orderId);  // D
    sheet.getRange(targetRow, 5).setValue(amount);   // E
    sheet.getRange(targetRow, 6).setValue(status);   // F
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
  const range = sheet.getRange("M2:M");
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
