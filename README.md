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

## 📤 Google Sheets Integration

If you want to **track orders in Google Sheets** based on the form submission email, use the following **Apps Script** to handle incoming order data from your website.

### ✅ What It Does

- Accepts a POST request containing `email`, `amount`, `order_id`, and `status`.
- Searches for a matching email in your Google Sheet (column 19).
- If a match is found:
  - Fills the order ID in **Column D**
  - Fills the amount in **Column E**
  - Fills the status in **Column F**

### 💡 Use Case

This is useful for:
- Updating participant information after checkout
- Tracking payments for competitions or events
- Cross-referencing WooCommerce data with form submissions

### 🔧 Setup Steps

1. Go to [Google Apps Script](https://script.google.com/).
2. Create a new project linked to your target Google Sheet.
3. Replace the default code with the script below.
4. Deploy it as a **Web App**:
   - Select `Execute as: Me`
   - Access: `Anyone (even anonymous)`
5. Copy the Web App URL — this will be your webhook endpoint.

### 🧠 Script

```javascript
function doPost(e) {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var data = JSON.parse(e.postData.contents);

  var email   = data.email;
  var amount  = data.amount;
  var status  = data.status;
  var orderId = data.order_id;

  var range = sheet.getDataRange();
  var values = range.getValues();

  for (var i = 1; i < values.length; i++) {
    // Log the email being checked
    Logger.log("🔍 Checking row " + i + " with email: " + values[i][18]);

    // Email comparison with safety
    if (values[i][18].toLowerCase().trim() == email.toLowerCase().trim()) {
      sheet.getRange(i + 1, 4).setValue(orderId);  // Column D
      sheet.getRange(i + 1, 5).setValue(amount);   // Column E
      sheet.getRange(i + 1, 6).setValue(status);   // Column F
      Logger.log("✅ Data matched and written at row " + (i+1));
      break;
    }
  }

  return ContentService.createTextOutput("Data updated for email: " + email);
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
