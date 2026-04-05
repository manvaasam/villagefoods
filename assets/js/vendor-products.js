const VendorProducts = (() => {
    let products = [];

    async function init() {
        await loadProducts();
        
        document.getElementById('productSearch')?.addEventListener('input', (e) => {
            renderProducts(e.target.value.toLowerCase());
        });
    }

    async function loadProducts() {
        try {
            const resp = await fetch('../api/vendor/products/list.php').then(r => r.json());
            if (resp.success) {
                products = resp.products;
                renderProducts();
            }
        } catch (err) {
            console.error('Failed to load products:', err);
        }
    }

    function renderProducts(searchTerm = '') {
        const tbody = document.getElementById('vendorProductsList');
        if (!tbody) return;

        const filtered = products.filter(p => {
            return (p.name || '').toLowerCase().includes(searchTerm) || 
                   (p.category_name || '').toLowerCase().includes(searchTerm);
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted)">No products found</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(p => {
            const isAvail = parseInt(p.is_available) === 1;
            return `
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px">
                            <div style="width:48px; height:48px; border-radius:10px; background:#f1f5f9; overflow:hidden;">
                                <img src="../${p.image_url || 'assets/images/village_quick-1.png'}" 
                                     style="width:100%; height:100%; object-fit:cover" 
                                     onerror="this.src='../assets/images/village_quick-1.png'; this.onerror=null;">
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--text); font-size:15px">${p.name}</div>
                                <div style="font-size:11px; color:var(--text-muted)">ID: #${p.id}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge" style="background:var(--bg); border:1px solid var(--border); color:var(--text-muted); font-size:11px">${p.category_name || 'Uncategorized'}</span></td>
                    <td><strong style="color:var(--primary); font-size:16px">₹${parseFloat(p.price).toFixed(2)}</strong></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px">
                            <input type="number" class="form-input" value="${p.stock}" 
                                   style="width:80px; padding:6px 10px; font-size:14px; text-align:center; font-weight:600" 
                                   onchange="VendorProducts.updateStock(${p.id}, this.value)">
                            <span style="font-size:12px; color:var(--text-muted); font-weight:600">UNIT</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px">
                            <label class="switch">
                                <input type="checkbox" ${isAvail ? 'checked' : ''} onchange="VendorProducts.toggleAvailability(${p.id}, this.checked)">
                                <span class="slider round"></span>
                            </label>
                            <span class="badge ${isAvail ? 'st-active' : 'st-inactive'}" style="min-width:80px; text-align:center">
                                ${isAvail ? 'ACTIVE' : 'HIDDEN'}
                            </span>
                        </div>
                    </td>
                    <td style="text-align:right">
                        <div style="display:flex; justify-content:flex-end; gap:6px">
                            <button class="view-all-btn" style="padding:6px 12px; font-size:12px; background:var(--bg); color:var(--text)" onclick="VendorProducts.editProduct(${p.id})">
                                <i data-lucide="edit-2" style="width:14px; height:14px"></i>
                            </button>
                            <button class="view-all-btn" style="padding:6px 12px; font-size:12px; background:#fff1f2; color:#dc2626; border-color:#fee2e2" onclick="VendorProducts.deleteProduct(${p.id})">
                                <i data-lucide="trash-2" style="width:14px; height:14px"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        
        if (window.lucide) lucide.createIcons();
    }

    function resetModal() {
        document.getElementById('prodModalTitle').innerHTML = '<i data-lucide="plus-circle"></i> Add New Product';
        document.getElementById('prodId').value = '';
        document.getElementById('prodName').value = '';
        document.getElementById('prodCat').value = '1';
        document.getElementById('prodPrice').value = '';
        document.getElementById('prodOldPrice').value = '';
        document.getElementById('prodUnit').value = '';
        document.getElementById('prodStock').value = '100';
        document.getElementById('prodRating').value = '4.5';
        document.getElementById('prodImageFile').value = '';
        document.getElementById('prodImagePreview').style.display = 'none';
        if (window.lucide) lucide.createIcons();
    }

    function handleImagePreview(input) {
        const preview = document.getElementById('prodImagePreview');
        const img = preview.querySelector('img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function editProduct(id) {
        const p = products.find(prod => prod.id == id);
        if (!p) return;

        document.getElementById('prodModalTitle').innerHTML = '<i data-lucide="edit-3"></i> Edit Product';
        document.getElementById('prodId').value = p.id;
        document.getElementById('prodName').value = p.name;
        document.getElementById('prodCat').value = p.category_id;
        document.getElementById('prodPrice').value = p.price;
        document.getElementById('prodOldPrice').value = p.old_price || '';
        document.getElementById('prodUnit').value = p.unit || '';
        document.getElementById('prodStock').value = p.stock;
        document.getElementById('prodRating').value = p.rating || '4.5';
        
        const preview = document.getElementById('prodImagePreview');
        if (p.image_url) {
            const previewImg = preview.querySelector('img');
            previewImg.src = '../' + p.image_url;
            previewImg.onerror = function() {
                this.src = '../assets/images/village_quick-1.png';
                this.onerror = null;
            };
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
        
        if (window.lucide) lucide.createIcons();
        if (typeof Modal !== 'undefined') Modal.open('addProductModal');
    }

    async function toggleAvailability(productId, isAvailable) {
        try {
            const resp = await fetch('../api/vendor/products/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, is_available: isAvailable ? 1 : 0 })
            }).then(r => r.json());

            if (resp.success) {
                const p = products.find(p => p.id == productId);
                if (p) p.is_available = isAvailable ? 1 : 0;
                if (typeof Toast !== 'undefined') Toast.show(isAvailable ? 'Product is now active' : 'Product is now hidden', 'success');
                renderProducts(document.getElementById('productSearch')?.value.toLowerCase() || '');
            } else {
                alert(resp.error || 'Failed to update availability');
            }
        } catch (err) {
            console.error('Error updating availability:', err);
        }
    }

    async function updateStock(productId, newQty) {
        try {
            const resp = await fetch('../api/vendor/products/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, stock: parseInt(newQty) })
            }).then(r => r.json());

            if (resp.success) {
                const p = products.find(p => p.id == productId);
                if (p) p.stock = parseInt(newQty);
                if (typeof Toast !== 'undefined') Toast.show('Stock updated successfully', 'success');
            } else {
                alert(resp.error || 'Failed to update stock');
            }
        } catch (err) {
            console.error('Error updating stock:', err);
        }
    }

    async function save() {
        const formData = new FormData();
        formData.append('id', document.getElementById('prodId').value);
        formData.append('name', document.getElementById('prodName').value);
        formData.append('category_id', document.getElementById('prodCat').value);
        formData.append('price', document.getElementById('prodPrice').value);
        formData.append('old_price', document.getElementById('prodOldPrice').value);
        formData.append('unit', document.getElementById('prodUnit').value);
        formData.append('stock', document.getElementById('prodStock').value);
        formData.append('rating', document.getElementById('prodRating').value);
        
        const imgFile = document.getElementById('prodImageFile').files[0];
        if (imgFile) {
            formData.append('image', imgFile);
        }

        try {
            const response = await fetch('../api/vendor/products/save.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let resp;
            try {
                resp = JSON.parse(text);
            } catch (e) {
                console.error('Non-JSON response:', text);
                alert('Server Error: Received invalid response from server.');
                return;
            }

            if (resp.success) {
                if (typeof Toast !== 'undefined') Toast.show(resp.message, 'success');
                if (typeof Modal !== 'undefined') Modal.close('addProductModal');
                resetModal();
                loadProducts();
            } else {
                alert(resp.error || 'Failed to save product');
            }
        } catch (err) {
            console.error('Network or Execution Error:', err);
            alert('Error: Could not connect to the server.');
        }
    }

    function deleteProduct(id) {
        document.getElementById('deleteProdId').value = id;
        if (typeof Modal !== 'undefined') Modal.open('deleteConfirmModal');
    }

    async function confirmDelete() {
        const id = document.getElementById('deleteProdId').value;
        if (!id) return;

        try {
            const resp = await fetch('../api/vendor/products/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            }).then(r => r.json());

            if (resp.success) {
                if (typeof Toast !== 'undefined') Toast.show('Product deleted successfully', 'success');
                if (typeof Modal !== 'undefined') Modal.close('deleteConfirmModal');
                loadProducts(); // Reload the list
            } else {
                alert(resp.error || 'Failed to delete product');
            }
        } catch (err) {
            console.error('Error deleting product:', err);
            alert('Error: Could not connect to the server.');
        }
    }

    return { init, loadProducts, toggleAvailability, updateStock, resetModal, handleImagePreview, editProduct, save, deleteProduct, confirmDelete };
})();

// Export to global scope for HTML onclick handlers
window.VendorProducts = VendorProducts;

document.addEventListener('DOMContentLoaded', VendorProducts.init);
