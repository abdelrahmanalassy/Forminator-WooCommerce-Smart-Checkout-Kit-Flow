# 🧾 Instant Checkout via Forminator

Automatically adds WooCommerce products to the cart upon **Forminator form submission** and redirects the user to the **checkout page**. Perfect for registration flows, event signups, or product onboarding sequences.

---

## ✅ Features

- 🔄 Auto-add predefined products to the WooCommerce cart on successful form submission.
- 🛒 Supports multiple product selections (e.g., registration fee + optional kits).
- 🧠 Detects form submission success via DOM mutation.
- 🧼 Automatically clears the cart before adding new products (to avoid duplication).
- 🚀 Instant redirection to the WooCommerce checkout.
- 🎨 Displays a "Processing..." message to enhance UX.
- 📦 Works with Forminator Pro (by WPMU DEV).

---

## 📌 Requirements

- WordPress 6.0+
- [WooCommerce](https://woocommerce.com/)
- [Forminator Pro](https://wpmudev.com/project/forminator-pro/)

---

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via **Plugins > Installed Plugins** in your WordPress admin dashboard.
3. Make sure Forminator and WooCommerce are active.
4. In your Forminator form, add a dropdown/select field that stores the kit product ID.
5. The plugin will handle the rest: detect submission, clear cart, add products, and redirect.

---

## 📘 Usage

- Create a **Forminator form** with required user info.
- Add a **select field** with WooCommerce product IDs as option values.
- Upon submission, the plugin will:
  - Detect the success message,
  - Grab the selected product ID,
  - Clear the WooCommerce cart,
  - Add products to cart (fixed + dynamic),
  - Redirect the user to `/checkout`.

---

## 📸 Example Flow

1. User fills out a Forminator form and selects a kit.
2. On successful submission:
    - “Thank you” message replaced by “Processing your registration...”
    - Products are auto-added.
    - User is redirected to WooCommerce checkout.

---

## 🔗 Links

- 🧑‍💻 **Author**: [Abdelrahman Ashraf](https://www.linkedin.com/in/abdelrahman-ashraf-elassy/)
- 📂 **Website**: [View on GitHub](https://aeprojects.org/)

---

## 🧠 Notes

> This plugin is ideal for non-technical site owners who need an automated and seamless checkout after a form submission.

> **Pro tip**: If you're running a competition, registration event, or educational challenge — this plugin saves time and reduces user error.

---

## 🛡 License

MIT License © 2025 Abdelrahman Ashraf
