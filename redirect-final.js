jQuery(document).ready(function ($) {
    console.log("Plugin JS Loaded");

    // Wait for the Forminator form container to appear
    const waitForForm = setInterval(() => {
        const formContainer = document.querySelector('#forminator-module-1482');
        if (formContainer) {
            clearInterval(waitForForm);
            console.log("Form container found");

            let hasProcessed = false;

            // Observe changes inside the form container to detect submission success
            const observer = new MutationObserver(() => {
                if (
                    !hasProcessed &&
                    formContainer.innerText.includes("Thank you! The form has been successfully completed. Only one step remains to finish your registration...")
                ) {
                    hasProcessed = true;
                    console.log("Success message detected");

                    // Read selected kit value (dropdown/select field)
                    const kitField = document.querySelector('select[name="select-2"]');
                    const kitValue = kitField ? parseInt(kitField.value, 10) : null;

                    // Check if user selected "Yes" for requesting a kit (radio field)
                    const kitRequestField = document.querySelector('input[name="radio-1"]:checked');
                    const wantsKit = kitRequestField && kitRequestField.value === "Yes";

                    console.log("Wants Kit?", wantsKit);
                    console.log("Kit Value:", kitValue);

                    // Show temporary loading message inside the form
                    const loadingDiv = document.createElement('div');
                    loadingDiv.textContent = 'We are processing your registration. Please wait...';
                    loadingDiv.style.cssText = 'padding: 20px; font-size: 18px; text-align: center; background: #f0f8ff; color: #000; border: 1px solid #ccc; margin: 20px 0; border-radius: 6px;';
                    formContainer.appendChild(loadingDiv);

                    // Save selected kit ID to session storage (for future use)
                    sessionStorage.setItem('seaperch_kit_id', wantsKit && kitValue ? kitValue : "");

                    // Step 1: Clear WooCommerce cart
                    fetch('/?empty-cart=yes', {
                        method: 'GET',
                        credentials: 'same-origin'
                    }).then(() => {
                        console.log("Cart cleared");

                        // Step 2: Add registration product to cart (product_id = 1384)
                        return fetch('/?wc-ajax=add_to_cart', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'product_id=1384&quantity=1',
                            credentials: 'same-origin'
                        });
                    }).then(() => {
                        // Step 3: If user requested a kit and selected a valid one, add it and shipping
                        if (wantsKit && kitValue > 0) {
                            // Add kit product
                            return fetch('/?wc-ajax=add_to_cart', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `product_id=${kitValue}&quantity=1`,
                                credentials: 'same-origin'
                            }).then(() => {
                                // // Add shipping product (product_id = 1511)
                                return fetch('/?wc-ajax=add_to_cart', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'product_id=1511&quantity=1',
                                    credentials: 'same-origin'
                                });
                            });
                        }
                    }).then(() => {
                        // All products added, redirect to checkout
                        console.log("Products added to cart, redirecting...");
                        window.location.href = '/checkout';
                    }).catch((err) => {
                        console.error("Error during cart operation:", err);
                    });

                    // Stop observing once handled
                    observer.disconnect();
                }
            });

            // Start watching for DOM changes in the form
            observer.observe(formContainer, { childList: true, subtree: true });
        }
    }, 300);
});

// Utility function to read a cookie by name
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}
