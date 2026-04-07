<?php
$pageTitle = 'Pickup & Drop — Village Foods';
$extraStyles = "
  <link rel=\"stylesheet\" href=\"assets/css/customer.css\">
  <style>
    .pickup-page-hero {
      background: linear-gradient(135deg, #1e3a5f, #1d4ed8, #3b82f6);
      padding: 60px 24px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .pickup-page-hero::before {
      content: \"\";
      position: absolute;
      inset: 0;
      background: url(\"data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='20' cy='20' r='2'/%3E%3C/g%3E%3C/svg%3E\");
    }
    .pickup-page-hero h1 { font-size: clamp(28px,5vw,48px); color: white; font-weight: 800; margin-bottom: 12px; position: relative; z-index: 1; }
    .pickup-page-hero p { font-size: 16px; color: rgba(255,255,255,0.8); max-width: 480px; margin: 0 auto 28px; position: relative; z-index: 1; }
    .pickup-page-hero .hero-emoji { font-size: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; position: relative; z-index: 1; animation: float 3s ease-in-out infinite; color: white; opacity: 0.9; }
    .pickup-page-hero .hero-emoji i { width: 100px; height: 100px; stroke-width: 1.5; }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }

    .booking-card {
      background: white;
      border-radius: var(--radius-lg);
      padding: 32px;
      box-shadow: var(--shadow-lg);
      max-width: 560px;
      margin: -40px auto 0;
      position: relative;
      z-index: 10;
    }

    .route-input-group { position: relative; }
    .route-dot {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      width: 12px;
      height: 12px;
      border-radius: 50%;
    }
    .route-dot.pickup { background: var(--primary); }
    .route-dot.drop { background: var(--accent); }
    .route-input {
      width: 100%;
      padding: 14px 16px 14px 42px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      font-size: 14px;
      font-weight: 500;
      color: var(--text);
      outline: none;
      transition: all 0.2s;
      background: var(--bg);
    }
    .route-input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(26,156,62,0.08); }
    .route-divider {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 6px 0;
      color: var(--text-muted);
      font-size: 20px;
    }

    .vehicle-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; }
    .vehicle-option {
      padding: 14px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
    }
    .vehicle-option.selected { border-color: var(--primary); background: var(--primary-pale); }
    .vehicle-option:hover { border-color: var(--primary); }
    .vehicle-icon { font-size: 28px; margin-bottom: 6px; display: flex; align-items: center; justify-content: center; }
    .vehicle-icon i { width: 32px; height: 32px; stroke-width: 2; }
    .vehicle-name { font-size: 13px; font-weight: 700; }
    .vehicle-price { font-size: 11px; color: var(--text-muted); font-weight: 500; }

    .price-estimate {
      background: var(--primary-pale);
      border: 1px solid #d1fae5;
      border-radius: var(--radius-sm);
      padding: 14px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
    }
    .price-label { font-size: 13px; font-weight: 600; color: var(--primary-dark); }
    .price-value { font-size: 20px; font-weight: 800; color: var(--primary); }

    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 48px; }
    .feature-card {
      background: white;
      border-radius: var(--radius);
      padding: 24px;
      text-align: center;
      box-shadow: var(--shadow-sm);
    }
    .feature-icon { font-size: 36px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); }
    .feature-icon i { width: 44px; height: 44px; stroke-width: 1.5; }
    .feature-title { font-size: 15px; font-weight: 800; margin-bottom: 6px; }
    .feature-desc { font-size: 13px; color: var(--text-muted); font-weight: 500; line-height: 1.5; }

    .how-it-works { padding: 48px 0; }
    .steps-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; position: relative; margin-top: 32px; }
    .step-card { text-align: center; }
    .step-num {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      font-size: 18px;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      box-shadow: var(--shadow-green);
    }
    .step-title { font-size: 14px; font-weight: 800; margin-bottom: 6px; }
    .step-desc { font-size: 12px; color: var(--text-muted); font-weight: 500; line-height: 1.5; }

    @media(max-width: 768px) {
      .vehicle-options { grid-template-columns: 1fr 1fr; }
      .features-grid { grid-template-columns: 1fr; }
      .steps-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
    }

    /* ===== LOCATION AUTOCOMPLETE ===== */
    .loc-autocomplete {
      position: relative;
    }
    .loc-suggestions {
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      background: white;
      border: 1.5px solid var(--border);
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      z-index: 200;
      max-height: 260px;
      overflow-y: auto;
      display: none;
    }
    .loc-suggestions.open {
      display: block;
    }
    .loc-sugg-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      cursor: pointer;
      border-bottom: 1px solid #f1f5f9;
      transition: background 0.15s;
    }
    .loc-sugg-item:last-child { border-bottom: none; }
    .loc-sugg-item:hover { background: #f0fdf4; }
    .loc-sugg-icon {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      background: var(--primary-pale);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      color: var(--primary);
    }
    .loc-sugg-name {
      font-size: 13px;
      font-weight: 700;
      color: var(--text);
    }
    .loc-sugg-meta {
      font-size: 11px;
      color: var(--text-muted);
      font-weight: 500;
    }
    .loc-sugg-pin {
      margin-left: auto;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      background: var(--bg);
      padding: 2px 6px;
      border-radius: 4px;
    }
    .loc-no-results {
      text-align: center;
      padding: 20px;
      font-size: 13px;
      color: var(--text-muted);
    }
  </style>";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero -->
<script>
  window.RAPID_SETTINGS = {
    bike: <?= (int)Settings::get('rapid_price_bike', 30) ?>,
    heavy: <?= (int)Settings::get('rapid_price_heavy', 50) ?>,
    express: <?= (int)Settings::get('rapid_price_express', 70) ?>,
    km_rate: <?= (int)Settings::get('rapid_price_per_km', 10) ?>
  };
</script>

<!-- BOOKING CARD -->
<div class="container">
  <div class="booking-card">
    <h3 style="font-size:18px;font-weight:800;margin-bottom:20px"><i data-lucide="package" style="vertical-align:middle;margin-right:8px;color:var(--primary)"></i> Book a Pickup</h3>

    <div class="form-group">
      <label class="form-label">Sender Name &amp; Phone</label>
      <div class="form-grid-2">
        <div class="form-group">
            <input class="form-input" type="text" id="senderName" placeholder="Your Name" value="<?= $_SESSION['user_name'] ?? '' ?>">
            <div class="invalid-feedback" id="senderNameError" style="display:none"></div>
        </div>
        <div class="form-group">
            <input class="form-input" type="tel" id="senderPhone" placeholder="Phone Number" value="<?= $_SESSION['user_phone'] ?? '' ?>" maxlength="10">
            <div class="invalid-feedback" id="senderPhoneError" style="display:none"></div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label"><i data-lucide="map" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;color:var(--primary)"></i> Route — Thirupathur District</label>

      <!-- Pickup -->
      <div class="loc-autocomplete" style="margin-bottom:8px">
        <div class="route-input-group">
          <div class="route-dot pickup"></div>
          <input class="route-input" type="text" id="pickupInput"
            placeholder="Pickup location (type to search...)"
            oninput="searchLocation(this, 'pickupSugg')"
            autocomplete="off">
          <button onclick="getPickupLocation()" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:var(--primary-pale);border:none;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:700;color:var(--primary);cursor:pointer">
            <i data-lucide="map-pin" style="width:12px;height:12px;vertical-align:middle"></i> Now
          </button>
        </div>
        <ul class="loc-suggestions" id="pickupSugg"></ul>
      </div>

      <div class="route-divider">↕</div>

      <!-- Drop -->
      <div class="loc-autocomplete">
        <div class="route-input-group">
          <div class="route-dot drop"></div>
          <input class="route-input" type="text" id="dropInput"
            placeholder="Drop location (type to search...)"
            oninput="searchLocation(this, 'dropSugg')"
            autocomplete="off">
          <button onclick="getDropLocation()" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:var(--primary-pale);border:none;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:700;color:var(--primary);cursor:pointer">
            <i data-lucide="map-pin" style="width:12px;height:12px;vertical-align:middle"></i> Now
          </button>
        </div>
        <ul class="loc-suggestions" id="dropSugg"></ul>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Package Type</label>
      <div class="vehicle-options">
        <div class="vehicle-option selected" onclick="selectVehicle(this, 'bike', <?= Settings::get('rapid_price_bike', 30) ?>)">
          <div class="vehicle-icon"><i data-lucide="bike"></i></div>
          <div class="vehicle-name">Bike</div>
          <div class="vehicle-price">From ₹<?= Settings::get('rapid_price_bike', 30) ?></div>
        </div>
        <div class="vehicle-option" onclick="selectVehicle(this, 'eco', <?= Settings::get('rapid_price_heavy', 50) ?>)">
          <div class="vehicle-icon"><i data-lucide="package"></i></div>
          <div class="vehicle-name">Heavy Parcel</div>
          <div class="vehicle-price">From ₹<?= Settings::get('rapid_price_heavy', 50) ?></div>
        </div>
        <div class="vehicle-option" onclick="selectVehicle(this, 'express', <?= Settings::get('rapid_price_express', 70) ?>)">
          <div class="vehicle-icon"><i data-lucide="zap"></i></div>
          <div class="vehicle-name">Express</div>
          <div class="vehicle-price">From ₹<?= Settings::get('rapid_price_express', 70) ?></div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Package Description</label>
      <input class="form-input" type="text" id="packageDesc" placeholder="e.g. Documents, food parcel, clothes...">
    </div>

    <div class="price-estimate">
      <div>
        <div class="price-label">Estimated Price</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Based on distance · Final price may vary</div>
      </div>
      <div class="price-value" id="estimatedPrice">₹30 – ₹70</div>
    </div>

    <button class="form-btn" onclick="bookPickup()" style="margin-top:0"><i data-lucide="bike" style="margin-right:8px;vertical-align:middle"></i> Book Pickup Now</button>
    <div style="text-align:center;font-size:12px;color:var(--text-muted);margin-top:12px">Payment collected on delivery · No advance needed</div>
  </div>

  <!-- FEATURES -->
  <div class="section">
    <div class="section-header">
      <h2 class="section-title">Why Choose <span>Village Foods</span> Pickup?</h2>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon"><i data-lucide="zap"></i></div>
        <div class="feature-title">Lightning Fast</div>
        <div class="feature-desc">Average pickup in under 5 minutes. Your package reaches its destination in record time.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i data-lucide="lock"></i></div>
        <div class="feature-title">100% Secure</div>
        <div class="feature-desc">Every delivery is tracked in real-time. Your package is safe with our verified partners.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><i data-lucide="indian-rupee"></i></div>
        <div class="feature-title">Best Rates</div>
        <div class="feature-desc">Starting from just ₹40. Transparent pricing with no hidden charges — ever.</div>
      </div>
    </div>
  </div>

  <!-- HOW IT WORKS -->
  <div class="how-it-works">
    <div class="section-header">
      <h2 class="section-title">How It <span>Works</span></h2>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-num">1</div>
        <div class="step-title">Enter Locations</div>
        <div class="step-desc">Add your pickup and drop addresses with package details.</div>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <div class="step-title">Choose Package</div>
        <div class="step-desc">Select package type based on size and urgency.</div>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <div class="step-title">We Pick Up</div>
        <div class="step-desc">Our partner arrives at your door within minutes.</div>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <div class="step-title">Delivered!</div>
        <div class="step-desc">Package delivered safely. Pay only on delivery.</div>
      </div>
    </div>
  </div>
</div>

<script>
  let selectedType = 'bike';
  let selectedPrice = { 
    min: window.RAPID_SETTINGS?.bike || 30, 
    max: (window.RAPID_SETTINGS?.bike || 30) * 2 
  };

  function selectVehicle(el, type, basePrice) {
    document.querySelectorAll('.vehicle-option').forEach(v => v.classList.remove('selected'));
    el.classList.add('selected');
    selectedType = type;
    
    // Calculate dynamic min/max based on settings
    const bikePrice = window.RAPID_SETTINGS?.bike || 30;
    const heavyPrice = window.RAPID_SETTINGS?.heavy || 50;
    const expressPrice = window.RAPID_SETTINGS?.express || 70;
    
    const prices = { 
      bike: [bikePrice, bikePrice * 2 + 10], 
      eco: [heavyPrice, heavyPrice * 2 + 10], 
      express: [expressPrice, expressPrice * 2 + 10] 
    };
    
    const [min, max] = prices[type] || [30, 70];
    selectedPrice = { min, max };
    updateEstimatedPrice();
  }

  function getPickupLocation() {
    if (typeof Toast !== 'undefined') Toast.show('Detecting your location...', '');
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('pickupInput').value = `Current Location (${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)})`;
        if (typeof Toast !== 'undefined') Toast.show('Location detected!', 'success');
      }, () => {
        if (typeof Toast !== 'undefined') Toast.show('Could not detect location', 'error');
      });
    }
  }

  let pickupCoords = null;
  let dropCoords = null;

  function getLocation(type) {
    if (!navigator.geolocation) {
      Toast.show('Geolocation not supported', 'error');
      return;
    }
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:12px;height:12px"></i>';
    if (window.lucide) lucide.createIcons();

    navigator.geolocation.getCurrentPosition(async (pos) => {
      const { latitude, longitude } = pos.coords;
      const coords = { lat: latitude, lng: longitude };
      if (type === 'pickup') pickupCoords = coords;
      else dropCoords = coords;

      try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
        const data = await res.json();
        const addr = data.display_name;
        document.getElementById(type + 'Input').value = addr;
        Toast.show(type.charAt(0).toUpperCase() + type.slice(1) + ' location detected!', 'success');
      } catch (e) {
        document.getElementById(type + 'Input').value = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
      }
      btn.innerHTML = originalHtml;
      if (window.lucide) lucide.createIcons();
    }, (err) => {
      Toast.show('Location error: ' + err.message, 'error');
      btn.innerHTML = originalHtml;
      if (window.lucide) lucide.createIcons();
    }, { enableHighAccuracy: true });
  }

  function getPickupLocation() { getLocation('pickup'); }
  function getDropLocation() { getLocation('drop'); }

  // Haversine formula to calculate distance between coordinates
  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  function updateEstimatedPrice() {
    if (!pickupCoords || !dropCoords) {
        document.getElementById('estimatedPrice').textContent = `₹${selectedPrice.min} – ₹${selectedPrice.max}`;
        return;
    }

    const distance = calculateDistance(pickupCoords.lat, pickupCoords.lng, dropCoords.lat, dropCoords.lng);
    
    // Pricing Logic:
    // Base: ₹40 (covers first 2km)
    // Extra: ₹10 per km after 2km
    // Vehicle Multiplier: Bike(1.0), Heavy(1.5), Express(2.0)
    
    const settingsMap = { bike: 'bike', eco: 'heavy', express: 'express' };
    let basePrice = window.RAPID_SETTINGS?.[settingsMap[selectedType]] || 30;
    const kmRate = window.RAPID_SETTINGS?.km_rate || 10;

    if (distance > 2) {
        basePrice += (distance - 2) * kmRate;
    }
    
    const multipliers = { bike: 1.0, eco: 1.5, express: 2.0 };
    const multiplier = multipliers[selectedType] || 1.0;
    const finalPrice = Math.round(basePrice * multiplier);
    
    document.getElementById('estimatedPrice').textContent = `₹${finalPrice} (${distance.toFixed(1)} km)`;
    return finalPrice;
  }

  async function bookPickup() {
    const name = document.getElementById('senderName').value.trim();
    const phone = document.getElementById('senderPhone').value.trim();
    const pickup = document.getElementById('pickupInput').value;
    const drop = document.getElementById('dropInput').value;
    const desc = document.getElementById('packageDesc').value;
    
    // Validate
    const isNameValid = Validation.validateInput(document.getElementById('senderName'), 'alpha', "Only letters and spaces are allowed", document.getElementById('senderNameError'));
    const isPhoneValid = Validation.validateInput(document.getElementById('senderPhone'), 'phone', "Enter a valid 10-digit phone number", document.getElementById('senderPhoneError'));

    if (!isNameValid || !isPhoneValid) {
        Toast.show('Please fix the errors before booking', 'error');
        return;
    }

    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<div class="loading-spinner"></div> Processing...';

    const price = updateEstimatedPrice() || 30;

    try {
        const resp = await fetch('api/rapid/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                sender_name: name,
                sender_phone: phone,
                pickup_address: pickup,
                pickup_lat: pickupCoords ? pickupCoords.lat : null,
                pickup_lng: pickupCoords ? pickupCoords.lng : null,
                drop_address: drop,
                drop_lat: dropCoords ? dropCoords.lat : null,
                drop_lng: dropCoords ? dropCoords.lng : null,
                item_description: desc,
                package_type: selectedType,
                price: price
            })
        });
        
        const result = await resp.json();
        if (result.status === 'success') {
            Toast.show(result.message, 'success');
            // Redirect to orders page with rapid tab active
            setTimeout(() => window.location.href = 'orders.php?tab=rapid', 1500);
        } else {
            Toast.show(result.message || 'Booking failed', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error(e);
        Toast.show('An error occurred during booking', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
  }

  // ===== DYNAMIC LOCATION SEARCH (Photon API) =====
  const _suggTimers = {};

  async function searchLocation(inputEl, suggId) {
    const q = inputEl.value.trim();
    const sugg = document.getElementById(suggId);
    clearTimeout(_suggTimers[suggId]);
    if (q.length < 3) {
      sugg.classList.remove('open');
      sugg.innerHTML = '';
      return;
    }
    _suggTimers[suggId] = setTimeout(async () => {
      try {
        // Photon API - focused around Thirupathur area if possible
        // Thirupathur coords: lat=12.4930, lon=78.5670
        const res = await fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&lat=12.4930&lon=78.5670&limit=5`);
        const data = await res.json();
        
        sugg.innerHTML = '';
        if (!data.features || !data.features.length) {
          sugg.innerHTML = '<li class="loc-no-results">No locations found</li>';
          sugg.classList.add('open');
          return;
        }

        data.features.forEach(feature => {
          const props = feature.properties;
          const coords = feature.geometry.coordinates; // [lng, lat]
          const name = props.name || props.street || "Location";
          const meta = [props.city, props.state, props.postcode].filter(Boolean).join(', ');

          const li = document.createElement('li');
          li.className = 'loc-sugg-item';
          
          li.innerHTML = `
            <div class="loc-sugg-icon"><i data-lucide="map-pin" style="width:14px;height:14px"></i></div>
            <div>
                <div class="loc-sugg-name">${name}</div>
                <div class="loc-sugg-meta">${meta}</div>
            </div>
          `;

          li.addEventListener('mousedown', function(e) {
            e.preventDefault();
            inputEl.value = name + (meta ? ', ' + meta : '');
            
            // Store coordinates
            const type = suggId.replace('Sugg', '');
            if (type === 'pickup') pickupCoords = { lat: coords[1], lng: coords[0] };
            else dropCoords = { lat: coords[1], lng: coords[0] };

            sugg.classList.remove('open');
            sugg.innerHTML = '';
            if (window.lucide) lucide.createIcons();
            updateEstimatedPrice();
          });
          sugg.appendChild(li);
        });
        sugg.classList.add('open');
        if (window.lucide) lucide.createIcons();
      } catch (err) {
        console.error("Photon API Error:", err);
      }
    }, 400);
  }

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.loc-autocomplete')) {
      document.querySelectorAll('.loc-suggestions').forEach(function(s) {
        s.classList.remove('open');
        s.innerHTML = '';
      });
    }
  });

  // Real-time validation listeners
  document.getElementById('senderName').addEventListener('input', e => Validation.validateInput(e.target, 'alpha', "Only letters and spaces are allowed", document.getElementById('senderNameError')));
  document.getElementById('senderPhone').addEventListener('input', e => Validation.validateInput(e.target, 'phone', "Enter a valid 10-digit phone number", document.getElementById('senderPhoneError')));

  // Pre-fill from URL params passed by homepage Book Now button
  (function() {
    var params = new URLSearchParams(window.location.search);
    var pickup = params.get('pickup');
    var drop = params.get('drop');
    if (pickup) { var pe = document.getElementById('pickupInput'); if (pe) pe.value = pickup; }
    if (drop)   { var de = document.getElementById('dropInput');   if (de) de.value = drop; }
  })();
</script>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
