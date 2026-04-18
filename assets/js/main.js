
function openModal(overlayId) {
    const overlay = document.getElementById(overlayId);
    if (overlay) {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden'; // prevent scrolling
    }
}

function closeModal(overlayId) {
    const overlay = document.getElementById(overlayId);
    if (overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
});

function showError(inputId, message) {
    const input = document.getElementById(inputId);
    if (!input) return;

    clearError(inputId);

    const error = document.createElement('div');
    error.className = 'field-error';
    error.style.cssText = 'color:#C0392B; font-size:12px; margin-top:4px;';
    error.textContent = message;

    input.parentNode.appendChild(error);
    input.style.borderColor = '#C0392B';
}

function clearError(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const existingError = input.parentNode.querySelector('.field-error');
    if (existingError) existingError.remove();
    input.style.borderColor = '';
}

function validateLoginForm() {
    let valid = true;

    const email = document.getElementById('email');
    const password = document.getElementById('password');

    clearError('email');
    clearError('password');

    if (!email || email.value.trim() === '') {
        showError('email', 'Email is required');
        valid = false;
    } else if (!/\S+@\S+\.\S+/.test(email.value)) {
        showError('email', 'Please enter a valid email address');
        valid = false;
    }

    if (!password || password.value === '') {
        showError('password', 'Password is required');
        valid = false;
    }

    return valid;
}

function validateRegisterForm() {
    let valid = true;

    clearError('name');
    clearError('email');
    clearError('password');
    clearError('confirm_password');

    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');

    if (!name || name.value.trim() === '') {
        showError('name', 'Full name is required');
        valid = false;
    }

    if (!email || email.value.trim() === '') {
        showError('email', 'Email is required');
        valid = false;
    } else if (!/\S+@\S+\.\S+/.test(email.value)) {
        showError('email', 'Please enter a valid email');
        valid = false;
    }

    if (!password || password.value.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        valid = false;
    }

    if (!confirm || confirm.value !== password.value) {
        showError('confirm_password', 'Passwords do not match');
        valid = false;
    }

    return valid;
}

function validateProductForm() {
    let valid = true;

    const fields = [
        { id: 'name',          msg: 'Product name is required' },
        { id: 'sku',           msg: 'SKU is required' },
        { id: 'category_id',   msg: 'Category is required' },
        { id: 'selling_price', msg: 'Selling price is required' },
    ];

    fields.forEach(function(f) {
        clearError(f.id);
        const el = document.getElementById(f.id);
        if (el && el.value.trim() === '') {
            showError(f.id, f.msg);
            valid = false;
        }
    });

    const price = document.getElementById('selling_price');
    if (price && price.value && isNaN(parseFloat(price.value))) {
        showError('selling_price', 'Enter a valid price');
        valid = false;
    }

    return valid;
}

function debounce(fn, delay) {
    let timer;
    return function() {
        clearTimeout(timer);
        timer = setTimeout(fn, delay);
    };
}

function initProductSearch() {
    const searchInput = document.getElementById('product-search');
    if (!searchInput) return;

    const doSearch = debounce(function() {
        const query = searchInput.value.trim();
        const category = document.getElementById('category-filter')
            ? document.getElementById('category-filter').value
            : '';

        const url = '/storehub/client/search_products.php?q=' 
            + encodeURIComponent(query) 
            + '&cat=' + encodeURIComponent(category);

        fetch(url)
            .then(function(res) { return res.text(); })
            .then(function(html) {
                const container = document.getElementById('products-container');
                if (container) container.innerHTML = html;
            })
            .catch(function(err) {
                console.error('Search failed:', err);
            });
    }, 350);

    searchInput.addEventListener('input', doSearch);

    const catFilter = document.getElementById('category-filter');
    if (catFilter) {
        catFilter.addEventListener('change', doSearch);
    }
}

function initStockAdjustment() {
    const qtyInput   = document.getElementById('quantity');
    const typeInputs = document.querySelectorAll('input[name="adj_type"]');
    const beforeEl   = document.getElementById('stock-before');
    const changeEl   = document.getElementById('stock-change');
    const afterEl    = document.getElementById('stock-after');

    if (!qtyInput || !beforeEl || !afterEl) return;

    function updatePreview() {
        const before    = parseInt(beforeEl.dataset.value) || 0;
        const qty       = parseInt(qtyInput.value) || 0;
        const isAdd     = document.querySelector('input[name="adj_type"]:checked')?.value === 'add';
        const change    = isAdd ? qty : -qty;
        const after     = Math.max(0, before + change);

        changeEl.textContent = (isAdd ? '+' : '-') + qty;
        changeEl.style.color = isAdd ? '#2D7A4F' : '#C0392B';
        afterEl.textContent  = after + ' units';
    }

    qtyInput.addEventListener('input', updatePreview);
    typeInputs.forEach(function(r) { r.addEventListener('change', updatePreview); });
}

function initReasonChips() {
    const chips   = document.querySelectorAll('.reason-chip');
    const hidden  = document.getElementById('reason-value');

    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('selected'); });
            chip.classList.add('selected');
            if (hidden) hidden.value = chip.dataset.value;
        });
    });
}

function confirmDelete(msg) {
    return confirm(msg || 'Are you sure you want to delete this item?');
}

function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 4000);
    });
}

function editProduct(data) {

    Object.keys(data).forEach(function(key) {
        const el = document.getElementById('edit_' + key);
        if (el) el.value = data[key];
    });
    openModal('edit-product-modal');
}

function editCategory(id, name, description, status) {
    document.getElementById('edit_cat_id').value          = id;
    document.getElementById('edit_cat_name').value        = name;
    document.getElementById('edit_cat_description').value = description;
    document.getElementById('edit_cat_status').value      = status;

    const heading = document.querySelector('#edit-category-form h3');
    if (heading) heading.textContent = 'Edit Category';
}

function editSupplier(data) {
    Object.keys(data).forEach(function(key) {
        const el = document.getElementById('edit_sup_' + key);
        if (el) el.value = data[key];
    });
    openModal('edit-supplier-modal');
}

document.addEventListener('DOMContentLoaded', function() {
    initProductSearch();
    initStockAdjustment();
    initReasonChips();
    initAlerts();
});
