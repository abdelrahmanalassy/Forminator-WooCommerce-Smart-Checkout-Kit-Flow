jQuery(document).ready(function ($) {
    console.log("✅ Plugin JS Loaded");

    const waitForForm = setInterval(() => {
        const formContainer = document.querySelector('#forminator-module-1433');
        if (formContainer) {
            clearInterval(waitForForm);
            console.log("✅ Form container found");

            let hasProcessed = false;

            const observer = new MutationObserver(() => {
                if (
                    !hasProcessed &&
                    formContainer.innerText.includes("Thank you! The form has been successfully completed. Only one step remains to finish your registration...")
                ) {
                    hasProcessed = true;
                    console.log("🎯 Success message detected");

                    const kitField = document.querySelector('select[name="select-3"]');
                    const kitValue = kitField ? kitField.value : null;
                    console.log("📦 Kit Value:", kitValue);

                    const kitProductId = parseInt(kitValue, 10);
                    if (isNaN(kitProductId) || kitProductId <= 0) {
                        console.error("❌ Invalid product ID:", kitValue);
                        return;
                    }

                    // Show loading message
                    const loadingDiv = document.createElement('div');
                        loadingDiv.textContent = 'We are processing your registration. Please wait...';
                        loadingDiv.style.cssText = 'padding: 20px; font-size: 18px; text-align: center; background: #f0f8ff; color: #000; border: 1px solid #ccc; margin: 20px 0; border-radius: 6px;';
                        formContainer.appendChild(loadingDiv);

                    // Save kit to session
                    sessionStorage.setItem('seaperch_kit_id', kitProductId);

                    // 🧹 Step 1: Clear cart from backend
                    fetch('/?empty-cart=yes', {
                            method: 'GET',
                            credentials: 'same-origin'
                        }).then(() => {
                            console.log("🧼 Cart cleared");

                        // ➕ Step 2: Add registration product
                        return fetch('/?wc-ajax=add_to_cart', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'product_id=1384&quantity=1',
                            credentials: 'same-origin'
                        });
                    }).then(() => {
                        // ➕ Step 3: Add kit
                        return fetch('/?wc-ajax=add_to_cart', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `product_id=${kitProductId}&quantity=1`,
                            credentials: 'same-origin'
                        });
                    }).then(() => {
                        console.log("✅ Products added to cart, redirecting...");
                        window.location.href = '/checkout';
                    }).catch((err) => {
                        console.error("❌ Error during cart operation:", err);
                    });

                    observer.disconnect();
                }
            });

            observer.observe(formContainer, { childList: true, subtree: true });
        }
    }, 300);
});
