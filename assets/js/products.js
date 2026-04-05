/* =============================================
   VILLAGE FOODS — DYNAMIC PRODUCT RENDERER (AJAX)
   ============================================= */

"use strict";

const ProductRenderer = (() => {
  let productsData = [];

  async function fetchProducts(category = "all", search = "", bestseller = false, shopId = null) {
    console.log(`Fetching products for category: ${category}, search: ${search}, bestseller: ${bestseller}, shopId: ${shopId}`);
    
    // Show skeleton loaders before fetching
    if (typeof showSkeletons === "function") {
      showSkeletons("productsGrid", 8);
    }
    
    try {
      let url = `api/products/list.php?category=${category}&search=${search}${bestseller ? '&bestseller=1' : ''}`;
      if (shopId) url += `&shop_id=${shopId}`;
      
      const resp = await fetch(url);
      if (!resp.ok) throw new Error(`HTTP Error: ${resp.status}`);
      productsData = await resp.json();
      console.log("Products received:", productsData);
      Cart.setProducts(productsData);
      renderGrid(productsData);
      Cart.init();

      const countEl = document.getElementById("productCountHeader");
      if (countEl) countEl.textContent = `${productsData.length} products`;
    } catch (err) {
      console.error("Failed to fetch products:", err);
      if (typeof Toast !== "undefined")
        Toast.show("Error loading products.", "error");
    }
  }

  async function fetchCategories() {
    console.log("Fetching categories...");
    
    // Show skeletons for categories if they exist
    const pills = document.getElementById("catPills");
    const grid = document.getElementById("categoriesGrid");
    if (pills) pills.innerHTML = '<div class="skeleton" style="height:40px;width:100px;border-radius:20px;margin-right:12px;flex-shrink:0;"></div>'.repeat(6);
    if (grid) grid.innerHTML = '<div class="skeleton-card" style="height:120px;border-radius:16px;"></div>'.repeat(6);

    try {
      const resp = await fetch("api/products/get_categories.php");
      if (!resp.ok) throw new Error(`HTTP Error: ${resp.status}`);
      const cats = await resp.json();
      console.log("Categories received:", cats);
      renderCategoryPills(cats);
      renderCategoryGrid(cats);
    } catch (err) {
      console.error("Failed to fetch categories:", err);
    }
  }

  function renderCategoryPills(cats) {
    const container = document.getElementById("catPills");
    if (!container) return;
    const allPill =
      '<div class="cat-pill active" onclick="filterCat(this,\'all\')" style="padding: 10px 24px;">All</div>';
    container.innerHTML =
      allPill +
      cats
        .map(
          (c) => `
      <div class="cat-pill" onclick="filterCat(this,'${c.slug}')" style="padding: 6px 18px 6px 8px;">
        ${c.image_url 
          ? `<div style="width:32px;height:32px;border-radius:50%;overflow:hidden;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,0.1);display:flex;align-items:center;justify-content:center;"><img src="${c.image_url}" style="width:100%;height:100%;object-fit:cover;"></div>`
          : `<div style="width:32px;height:32px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;"><i data-lucide="${c.icon_name || "package"}" style="width:16px;height:16px;"></i></div>`
        }
        <span>${Utils.escapeHTML(c.name)}</span>
      </div>
    `,
        )
        .join("");
    if (window.lucide) {
      lucide.createIcons({
        attrs: { "data-lucide": true },
        scope: container
      });
    }
  }

  function renderCategoryGrid(cats) {
    const container = document.getElementById("categoriesGrid");
    if (!container) return;

    // Add Village Quick as a special category
    const quickItem = `
      <div class="category-item cat-quick" onclick="window.location.href='pickup-drop.php'">
        <div class="category-circle" style="background: white; border-color: var(--primary);">
          <img src="assets/images/village_quick-1.png" alt="Village Quick" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
        </div>
        <div class="category-item-name">Village Quick</div>
      </div>`;

    container.innerHTML = cats
      .map(
        (c, i) => `
      <div class="category-item cat-${(i % 7) + 1}" onclick="window.location.href='category_shops?category_id=${c.id}&name=${encodeURIComponent(c.name)}'">
        <div class="category-circle">
          ${c.image_url 
            ? `<img src="${c.image_url}" alt="${c.name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`
            : `<i data-lucide="package"></i>`
          }
        </div>
        <div class="category-item-name">${Utils.escapeHTML(c.name)}</div>
      </div>
    `,
      )
      .join("") + quickItem;
    
    if (window.lucide) {
      lucide.createIcons({
        attrs: { "data-lucide": true },
        scope: container
      });
    }
  }

  function renderCard(product) {
    const {
      id,
      name,
      unit,
      price,
      old_price,
      rating,
      reviews,
      icon_name,
      image_url,
      badge,
      badge_type,
    } = product;

    const imageHtml = image_url 
      ? `<img src="${image_url}" alt="${Utils.escapeHTML(name)}" style="width:100%;height:100%;object-fit:cover;">`
      : `<i data-lucide="${icon_name || "package"}" class="p-icon"></i>`;

    const isOutOfStock = (product.stock !== undefined && product.stock <= 0);

    return `
      <div class="product-card fade-in-up ${isOutOfStock ? 'out-of-stock' : ''}" data-id="${id}" data-cat="${product.category_slug}">
        <div class="product-img-wrap">
          ${imageHtml}
          <button class="wishlist-btn ${product.in_wishlist > 0 ? 'active' : ''}" onclick="Wishlist.toggle(event, ${id})">
            <i data-lucide="heart" class="${product.in_wishlist > 0 ? 'heart-filled' : ''}"></i>
          </button>
          ${isOutOfStock ? `<div class="product-badge danger">Out of Stock</div>` : (badge ? `<div class="product-badge ${badge_type || ""}">${badge}</div>` : "")}
        </div>
        <div style="font-size:11px; text-transform:uppercase; font-weight:800; color:var(--primary); margin-bottom:4px; margin-top:12px;">
          ${Utils.escapeHTML(product.shop_name || 'Vendor')}
        </div>
        <div class="product-name" style="margin-top:0">${Utils.escapeHTML(name)}</div>
        <div class="product-unit">${Utils.escapeHTML(unit)}</div>
        <div class="product-rating">
          <i data-lucide="star" class="star-icon"></i><span>${rating || '4.5'}</span>
          <span class="fs-12">(${reviews || '0'})</span>
        </div>
        <div class="product-footer">
          <div class="product-price">
            ${old_price ? `<span class="old-price">₹${old_price}</span>` : ""}
            ₹${price}
          </div>
          <div id="btn-${id}">
            <button class="add-btn" onclick="Cart.add(${id})" ${isOutOfStock ? 'disabled' : ''}>
              ${isOutOfStock ? '<i data-lucide="slash"></i>' : '<i data-lucide="plus"></i>'}
            </button>
          </div>
        </div>
      </div>`;
  }

  function renderGrid(products, containerId = "productsGrid") {
    const grid = document.getElementById(containerId);
    if (!grid) return;

    if (!products || products.length === 0) {
      grid.innerHTML = `
        <div style="text-align:center;padding:48px;color:var(--text-muted);grid-column:1/-1">
          <div style="font-size:48px;margin-bottom:12px"><i data-lucide="search-x" style="width:64px;height:64px;margin:0 auto"></i></div>
          <div style="font-weight:700;font-size:15px">No products found</div>
          <div style="font-size:13px;margin-top:6px">Try a different search or category</div>
        </div>`;
    } else {
      grid.innerHTML = products.map(renderCard).join("");
    }

    // Initialize icons for newly rendered content ONLY
    if (window.lucide) {
      lucide.createIcons({
        attrs: { 'data-lucide': true },
        scope: grid
      });
    }
  }

  return { fetchProducts, fetchCategories, renderGrid, renderCard };
})();

function filterCat(el, cat) {
  document
    .querySelectorAll(".cat-pill")
    .forEach((p) => p.classList.remove("active"));
  el.classList.add("active");
  const isHomePage = document.getElementById('heroSearchInput') !== null;
  ProductRenderer.fetchProducts(cat, "", isHomePage);
}

// ======= SEARCH INPUT HANDLER =======
let searchTimeout;
function filterProducts(query) {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    const activeCat =
      document
        .querySelector(".cat-pill.active")
        ?.getAttribute("onclick")
        ?.match(/'([^']+)'/)?.[1] || "all";
    const isHomePage = document.getElementById('heroSearchInput') !== null;
    ProductRenderer.fetchProducts(activeCat, query, isHomePage);
  }, 300);
}

// Initial Load
document.addEventListener("DOMContentLoaded", () => {
  const isHomePage = document.getElementById('heroSearchInput') !== null;
  
  // Skip auto-fetch if we are on a shop details page (which handles its own fetching)
  if (!window.currentShopId) {
    ProductRenderer.fetchProducts("all", "", isHomePage);
  }
  ProductRenderer.fetchCategories();
});
