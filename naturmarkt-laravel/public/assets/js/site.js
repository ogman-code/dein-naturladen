const toggle = document.querySelector('.menu-toggle');
const links = document.querySelector('.nav-links');

const shopIntro = document.querySelector('#shop-intro');
const shopIntroClose = document.querySelector('#shop-intro-close');
const shopIntroSkip = document.querySelector('#shop-intro-skip');

if (shopIntro) {
    const introKey = 'naturmarkt-intro-seen';
    let introTimer;
    const closeIntro = () => {
        window.clearTimeout(introTimer);
        shopIntro.classList.add('closing');
        sessionStorage.setItem(introKey, '1');
        window.setTimeout(() => {
            shopIntro.remove();
            document.body.classList.remove('intro-open');
        }, 550);
    };

    if (sessionStorage.getItem(introKey)) {
        shopIntro.remove();
    } else {
        document.body.classList.add('intro-open');
        shopIntroClose?.focus();
        introTimer = window.setTimeout(closeIntro, 7000);
        shopIntroClose?.addEventListener('click', closeIntro);
        shopIntroSkip?.addEventListener('click', closeIntro);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && document.body.classList.contains('intro-open')) closeIntro();
        }, { once: true });
    }
}

if (toggle && links) {
    toggle.addEventListener('click', () => {
        const open = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
    });

    links.querySelectorAll('a').forEach((anchor) => {
        anchor.addEventListener('click', () => {
            links.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
}

const search = document.querySelector('#product-search');
const sort = document.querySelector('#product-sort');
const products = [...document.querySelectorAll('.product-card')];
const productGrid = document.querySelector('.product-grid');
const bestsellerSlider = document.querySelector('[data-slider]');
const sliderPrev = document.querySelector('[data-slider-prev]');
const sliderNext = document.querySelector('[data-slider-next]');
const heroSlider = document.querySelector('[data-hero-slider]');
const heroSliderTrack = heroSlider?.querySelector('.hero-slider-track');
const heroPrev = document.querySelector('[data-hero-prev]');
const heroNext = document.querySelector('[data-hero-next]');
const heroDots = [...document.querySelectorAll('.hero-slider-dots span')];

if (search && products.length) {
    search.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();

        products.forEach((product) => {
            product.classList.toggle('hidden', !product.dataset.product.includes(query));
        });
    });
}

if (sort && products.length && productGrid) {
    sort.addEventListener('change', () => {
        const sorted = [...products].sort((a, b) => {
            if (sort.value === 'price-asc') return Number(a.dataset.priceValue || 0) - Number(b.dataset.priceValue || 0);
            if (sort.value === 'price-desc') return Number(b.dataset.priceValue || 0) - Number(a.dataset.priceValue || 0);
            if (sort.value === 'name-asc') return String(a.dataset.name || '').localeCompare(String(b.dataset.name || ''), 'de');
            return Number(a.dataset.index || 0) - Number(b.dataset.index || 0);
        });

        sorted.forEach((product) => productGrid.appendChild(product));
    });
}

function scrollBestsellers(direction) {
    if (!bestsellerSlider) return;

    const card = bestsellerSlider.querySelector('.product-card');
    const amount = card ? card.getBoundingClientRect().width + 14 : bestsellerSlider.clientWidth * 0.85;

    bestsellerSlider.scrollBy({
        left: direction * amount,
        behavior: 'smooth',
    });
}

sliderPrev?.addEventListener('click', () => scrollBestsellers(-1));
sliderNext?.addEventListener('click', () => scrollBestsellers(1));

function updateHeroDots() {
    if (!heroSliderTrack || !heroDots.length) return;

    const index = Math.round(heroSliderTrack.scrollLeft / heroSliderTrack.clientWidth);

    heroDots.forEach((dot, dotIndex) => {
        dot.classList.toggle('active', dotIndex === index);
    });
}

function scrollHeroProducts(direction) {
    if (!heroSliderTrack) return;

    const maxIndex = heroDots.length - 1;
    const currentIndex = Math.round(heroSliderTrack.scrollLeft / heroSliderTrack.clientWidth);
    let nextIndex = currentIndex + direction;

    if (nextIndex < 0) nextIndex = maxIndex;
    if (nextIndex > maxIndex) nextIndex = 0;

    heroSliderTrack.scrollTo({
        left: nextIndex * heroSliderTrack.clientWidth,
        behavior: 'smooth',
    });
}

heroPrev?.addEventListener('click', () => scrollHeroProducts(-1));
heroNext?.addEventListener('click', () => scrollHeroProducts(1));
heroSliderTrack?.addEventListener('scroll', () => window.requestAnimationFrame(updateHeroDots));

heroDots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        heroSliderTrack?.scrollTo({
            left: index * heroSliderTrack.clientWidth,
            behavior: 'smooth',
        });
    });
});

const storageKey = 'naturmarkt-cart';
const cartButton = document.querySelector('.cart-button');
const cartDrawer = document.querySelector('#cart-drawer');
const cartClose = document.querySelector('#cart-close');
const cartItems = document.querySelector('#cart-items');
const cartEmpty = document.querySelector('#cart-empty');
const cartCount = document.querySelector('#cart-count');
const cartPageItems = document.querySelector('#cart-page-items');
const cartPageEmpty = document.querySelector('#cart-page-empty');
const cartPageCount = document.querySelector('#cart-page-count');
const cartSubtotal = document.querySelector('#cart-subtotal');
const cartWeight = document.querySelector('#cart-weight');
const cartShipping = document.querySelector('#cart-shipping');
const cartDiscount = document.querySelector('#cart-discount');
const discountRow = document.querySelector('#discount-row');
const cartTotal = document.querySelector('#cart-total');
const placeOrder = document.querySelector('#place-order');
const checkoutForm = document.querySelector('#checkout-form');
const goToCheckout = document.querySelector('#go-to-checkout');
const checkoutMessage = document.querySelector('#checkout-message');
const shippingCountry = document.querySelector('#shipping-country');
const couponCode = document.querySelector('#coupon-code');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const shippingConfig = window.NATURMARKT_SHIPPING || {};

function getCart() {
    try {
        return JSON.parse(localStorage.getItem(storageKey)) || [];
    } catch {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(storageKey, JSON.stringify(cart));
}
function showShopToast(message) { let toast=document.querySelector('.shop-toast'); if(!toast){toast=document.createElement('div');toast.className='shop-toast';document.body.appendChild(toast);} toast.textContent=message;toast.classList.add('show');clearTimeout(window.naturmarktToastTimer);window.naturmarktToastTimer=setTimeout(()=>toast.classList.remove('show'),2200); }

function parsePrice(price) {
    return Number(String(price).replace('EUR', '').replace(/\./g, '').replace(',', '.').trim()) || 0;
}

function formatPrice(value) {
    return value.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' EUR';
}

function formatWeight(grams) {
    if (grams < 1000) return `${grams} g`;
    return `${(grams / 1000).toLocaleString('de-DE', { maximumFractionDigits: 2 })} kg`;
}

function shippingPriceForWeight(countryCode, weightGrams) {
    const rates = shippingConfig.countries?.[countryCode]?.rates || {};
    const rate = Object.entries(rates)
        .map(([maximumWeight, price]) => [Number(maximumWeight), Number(price)])
        .sort((a, b) => a[0] - b[0])
        .find(([maximumWeight]) => weightGrams <= maximumWeight);

    return rate ? rate[1] : null;
}

function cartTotals(cart) {
    const subtotal = cart.reduce((sum, item) => sum + parsePrice(item.price) * item.quantity, 0);
    const weight = cart.reduce((sum, item) => sum + Number(item.weight || shippingConfig.default_product_weight_grams || 500) * item.quantity, 0);
    const countryCode = shippingCountry?.value || 'DE';
    const weightShipping = shippingPriceForWeight(countryCode, weight);
    const freeFrom = Number(shippingConfig.free_from || 60);
    const exceedsMaximumWeight = subtotal > 0 && weightShipping === null;
    const shipping = subtotal === 0 || subtotal >= freeFrom ? 0 : (weightShipping ?? 0);
    const discount = 0;

    return {
        subtotal,
        weight,
        shipping,
        exceedsMaximumWeight,
        discount,
        total: subtotal + shipping - discount,
        count: cart.reduce((sum, item) => sum + item.quantity, 0),
    };
}

function setQuantity(name, quantity) {
    const cart = getCart()
        .map((item) => item.name === name ? { ...item, quantity: Math.min(20, Math.max(1, quantity)) } : item);

    saveCart(cart);
    renderCart();
}

function removeItem(name) {
    saveCart(getCart().filter((item) => item.name !== name));
    renderCart();
}

function renderCart() {
    const cart = getCart();
    const totals = cartTotals(cart);

    if (cartCount) cartCount.textContent = String(totals.count);

    if (cartItems && cartEmpty) {
        cartItems.innerHTML = cart.map((item) => `
            <li>
                <span>${item.quantity}x ${item.name}</span>
                <strong>${formatPrice(parsePrice(item.price) * item.quantity)}</strong>
            </li>
        `).join('');
        cartEmpty.style.display = cart.length ? 'none' : 'block';
    }

    if (cartPageItems && cartPageEmpty) {
        const compact = cartPageItems.classList.contains('checkout-summary-items');

        cartPageItems.innerHTML = cart.map((item) => compact ? `
            <article class="checkout-summary-line">
                <a class="checkout-summary-image" href="${item.url || '#'}">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
                </a>
                <div class="checkout-summary-copy">
                    <span>${item.category || 'Naturmarkt'}</span>
                    <strong>${item.url ? `<a href="${item.url}">${item.name}</a>` : item.name}</strong>
                    <small>${item.quantity} x ${item.price}</small>
                </div>
                <strong class="checkout-summary-price">${formatPrice(parsePrice(item.price) * item.quantity)}</strong>
            </article>
        ` : `
            <article class="cart-line">
                <a class="cart-line-image" href="${item.url || '#'}">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
                </a>
                <div>
                    <span>${item.category || 'Naturmarkt'}</span>
                    <strong>${item.url ? `<a href="${item.url}">${item.name}</a>` : item.name}</strong>
                    <small>${item.price} pro Stück</small>
                </div>
                <label>
                    Menge
                    <select data-cart-quantity="${item.name}">
                        ${Array.from({ length: 20 }, (_, index) => {
                            const amount = index + 1;
                            return `<option value="${amount}" ${amount === item.quantity ? 'selected' : ''}>${amount}</option>`;
                        }).join('')}
                    </select>
                </label>
                <strong>${formatPrice(parsePrice(item.price) * item.quantity)}</strong>
                <button type="button" data-cart-remove="${item.name}">Entfernen</button>
            </article>
        `).join('');

        cartPageEmpty.style.display = cart.length ? 'none' : 'grid';
        cartPageItems.style.display = cart.length ? 'grid' : 'none';
    }

    if (cartPageCount) cartPageCount.textContent = totals.count === 1 ? '1 Produkt' : `${totals.count} Produkte`;
    if (cartSubtotal) cartSubtotal.textContent = formatPrice(totals.subtotal);
    if (cartWeight) cartWeight.textContent = formatWeight(totals.weight);
    if (cartShipping) cartShipping.textContent = totals.exceedsMaximumWeight ? 'Auf Anfrage' : (totals.shipping ? formatPrice(totals.shipping) : 'Kostenlos');
    if (cartDiscount) cartDiscount.textContent = `−${formatPrice(totals.discount)}`;
    if (discountRow) discountRow.hidden = totals.discount <= 0;
    if (cartTotal) cartTotal.textContent = formatPrice(totals.total);
    if (goToCheckout) {
        goToCheckout.classList.toggle('disabled', totals.exceedsMaximumWeight);
        goToCheckout.setAttribute('aria-disabled', String(totals.exceedsMaximumWeight));
    }
}

document.querySelectorAll('.add-to-cart').forEach((button) => {
    button.addEventListener('click', () => {
        const cart = getCart();
        const existing = cart.find((item) => item.name === button.dataset.name);

        if (existing) {
            existing.quantity = Math.min(20, existing.quantity + 1);
            existing.weight = Number(button.dataset.weight || existing.weight || shippingConfig.default_product_weight_grams || 500);
        } else {
            cart.push({
                name: button.dataset.name,
                price: button.dataset.price,
                weight: Number(button.dataset.weight || shippingConfig.default_product_weight_grams || 500),
                category: button.dataset.category || button.closest('.product-card')?.querySelector('.product-category')?.textContent?.trim() || 'Naturmarkt',
                image: button.dataset.image || '',
                url: button.dataset.url || '',
                quantity: 1,
            });
        }

        saveCart(cart);
        renderCart();
        cartDrawer?.classList.add('open');
        showShopToast(`${button.dataset.name} wurde in den Warenkorb gelegt.`);
    });
});

document.addEventListener('change', (event) => {
    const quantityTarget = event.target.closest('[data-cart-quantity]');
    if (!quantityTarget) return;

    setQuantity(quantityTarget.dataset.cartQuantity, Number(quantityTarget.value));
});

document.addEventListener('click', (event) => {
    const removeTarget = event.target.closest('[data-cart-remove]');
    if (!removeTarget) return;

    removeItem(removeTarget.dataset.cartRemove);
});

cartClose?.addEventListener('click', () => cartDrawer?.classList.remove('open'));

goToCheckout?.addEventListener('click', (event) => {
    if (cartTotals(getCart()).exceedsMaximumWeight) {
        event.preventDefault();
        checkoutMessage.textContent = 'Das Gesamtgewicht ist zu hoch. Bitte teile die Bestellung auf oder kontaktiere uns.';
        return;
    }
    if (getCart().length) return;

    event.preventDefault();
    checkoutMessage.textContent = 'Bitte lege zuerst Produkte in den Warenkorb.';
});

checkoutForm?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const cart = getCart();
    const formData = new FormData(checkoutForm);
    const totals = cartTotals(cart);

    if (!cart.length) {
        checkoutMessage.textContent = 'Bitte lege zuerst Produkte in den Warenkorb.';
        return;
    }

    if (totals.exceedsMaximumWeight) {
        checkoutMessage.textContent = 'Das Gesamtgewicht ist zu hoch. Bitte teile die Bestellung auf oder kontaktiere uns.';
        return;
    }

    checkoutMessage.textContent = 'Deine Anfrage wird gespeichert...';
    placeOrder.disabled = true;

    try {
        const response = await fetch('/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                street: formData.get('street'),
                postal_code: formData.get('postal_code'),
                city: formData.get('city'),
                country_code: formData.get('country_code'),
                notes: formData.get('notes'),
                payment_method: formData.get('payment_method') || 'Ueberweisung',
                coupon_code: formData.get('coupon_code'),
                terms_accepted: formData.get('terms_accepted'),
                company_website: formData.get('company_website'),
                cart,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Die Anfrage konnte nicht gespeichert werden.');
        }

        checkoutMessage.textContent = data.message;
        window.location.href = data.redirect || '/danke';
    } catch (error) {
        checkoutMessage.textContent = error.message || 'Die Anfrage konnte nicht gespeichert werden.';
    } finally {
        placeOrder.disabled = false;
    }
});

shippingCountry?.addEventListener('change', renderCart);
couponCode?.addEventListener('input', renderCart);

renderCart();

const consentKey = 'naturmarkt-cookie-consent';
if (!document.body.classList.contains('admin-body') && !localStorage.getItem(consentKey)) {
    const banner = document.createElement('aside');
    banner.className = 'cookie-banner';
    banner.setAttribute('aria-label', 'Cookie-Einstellungen');
    banner.innerHTML = `
        <div>
            <strong>Datenschutz-Einstellungen</strong>
            <p>Der Shop verwendet technisch notwendige Speicherung für Warenkorb und Sitzung. Optionale Dienste bleiben deaktiviert, bis du zustimmst.</p>
        </div>
        <div>
            <button type="button" data-consent="necessary">Nur notwendige</button>
            <button type="button" class="primary" data-consent="all">Alle akzeptieren</button>
        </div>
    `;
    document.body.appendChild(banner);
    banner.addEventListener('click', (event) => {
        const choice = event.target.closest('[data-consent]')?.dataset.consent;
        if (!choice) return;
        localStorage.setItem(consentKey, choice);
        banner.remove();
    });
}

const productImage = document.querySelector('.product-detail-media img');
if(productImage){const viewedKey='naturmarkt-recent-products';let viewed=[];try{viewed=JSON.parse(localStorage.getItem(viewedKey))||[]}catch{}const current={name:document.querySelector('.product-detail-info h1')?.textContent?.trim(),url:location.pathname,image:productImage.src};if(current.name){viewed=[current,...viewed.filter(item=>item.url!==current.url)].slice(0,6);localStorage.setItem(viewedKey,JSON.stringify(viewed));}}
productImage?.addEventListener('click', () => {
    const lightbox = document.createElement('dialog');
    lightbox.className = 'product-lightbox';
    lightbox.innerHTML = `<button type="button" aria-label="Großansicht schließen">×</button><img src="${productImage.src}" alt="${productImage.alt}">`;
    document.body.appendChild(lightbox);
    lightbox.showModal();
    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox || event.target.closest('button')) {
            lightbox.close();
            lightbox.remove();
        }
    });
});

const adminProductSearch = document.querySelector('#admin-product-search');
const adminCategoryTabs = document.querySelectorAll('[data-admin-category-tab]');
const filterAdminProducts = () => {
    const query = adminProductSearch.value.trim().toLowerCase();
    const activeCategory = document.querySelector('[data-admin-category]:not([hidden])');
    activeCategory?.querySelectorAll('[data-admin-product]').forEach((product) => {
        product.hidden = !product.dataset.adminProduct.includes(query);
    });
    const visibleProducts = activeCategory?.querySelectorAll('[data-admin-product]:not([hidden])').length ?? 0;
    const count = activeCategory?.querySelector('[data-visible-product-count]');
    if (count) count.textContent = visibleProducts;
};

adminCategoryTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
        adminCategoryTabs.forEach((item) => {
            const isActive = item === tab;
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        document.querySelectorAll('[data-admin-category]').forEach((category) => {
            category.hidden = category.id !== tab.dataset.adminCategoryTab;
            category.querySelectorAll('[data-admin-product]').forEach((product) => product.hidden = false);
        });
        adminProductSearch.value = '';
        filterAdminProducts();
    });
});
adminProductSearch?.addEventListener('input', filterAdminProducts);

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-description-name]');
    if (!trigger) return;

    const dialog = document.createElement('dialog');
    dialog.className = 'description-dialog';
    const incompleteNotice = trigger.dataset.descriptionIncomplete === '1'
        ? '<p class="description-source-note">Hinweis: Der derzeit hinterlegte Lieferantentext ist noch unvollständig. Der Besitzer kann die vollständigen Herstellerangaben ergänzen.</p>'
        : '';

    dialog.innerHTML = `
        <div class="description-dialog-head">
            <div>
                <span>Produktinformation</span>
                <h2></h2>
            </div>
            <button type="button" data-description-close aria-label="Beschreibung schließen">×</button>
        </div>
        <div class="description-dialog-body">
            <section>
                <h3>Beschreibung</h3>
                <p data-description-content></p>
                ${incompleteNotice}
            </section>
            <section class="description-ingredients">
                <h3>Zutaten & wichtige Angaben</h3>
                <p data-ingredients-content></p>
                <small>Bei Allergien oder Unverträglichkeiten bitte immer die verbindlichen Angaben auf der Verpackung prüfen.</small>
            </section>
        </div>
    `;
    dialog.querySelector('h2').textContent = trigger.dataset.descriptionName;
    dialog.querySelector('[data-description-content]').textContent = trigger.dataset.descriptionText;
    dialog.querySelector('[data-ingredients-content]').textContent = trigger.dataset.descriptionIngredients;
    document.body.appendChild(dialog);
    dialog.showModal();

    const close = () => {
        dialog.close();
        dialog.remove();
    };
    dialog.querySelector('[data-description-close]').addEventListener('click', close);
    dialog.addEventListener('click', (dialogEvent) => {
        if (dialogEvent.target === dialog) close();
    });
});
