const LocationModal = (() => {
    let currentCoords = null;
    let detectedInfo = null;

    function inject() {
        if (document.getElementById('locationModalOverlay')) return;
        const modalHtml = `
            <style>
                .loc-input-wrap { position:relative; margin-bottom:16px; }
                .loc-input-wrap i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--text-muted); width:20px; height:20px; transition:color 0.2s; }
                .loc-input-wrap input { width:100%; padding:16px 16px 16px 48px; border:1.5px solid var(--border); border-radius:14px; font-size:14px; font-weight:600; color:var(--text); background:var(--bg); transition:all 0.2s; box-sizing:border-box; }
                .loc-input-wrap input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 4px rgba(26,156,62,0.1); outline:none; }
                .loc-input-wrap input:focus + i, .loc-input-wrap input:focus ~ i { color:var(--primary); }
                
                .loc-detect-btn-new { display:flex; align-items:center; gap:16px; background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:16px; padding:18px 20px; cursor:pointer; transition:all 0.2s; margin-top:20px; margin-bottom:10px; }
                .loc-detect-btn-new:hover { background:#dcfce7; transform:translateY(-2px); box-shadow:0 8px 24px rgba(22,163,74,0.12); }
                .loc-detect-icon-new { width:48px; height:48px; border-radius:50%; background:white; color:var(--primary); display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.06); flex-shrink:0; }
                .loc-detect-text-new h4 { margin:0 0 4px 0; font-size:17px; font-weight:800; color:#166534; }
                .loc-detect-text-new p { margin:0; font-size:13px; color:#15803d; font-weight:600; }
                
                .loc-save-btn-new { width:100%; padding:18px; background:linear-gradient(135deg, var(--primary), #15803d); color:white; border:none; border-radius:16px; font-size:16px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px; box-shadow:0 10px 30px rgba(26,156,62,0.25); transition:all 0.2s; margin-top:10px; }
                .loc-save-btn-new:hover { transform:translateY(-2px); box-shadow:0 14px 35px rgba(26,156,62,0.35); }
                .loc-save-btn-new:disabled { opacity:0.7; transform:none; cursor:not-allowed; box-shadow:none; }
                
                .loc-landmark-hint { position:absolute; right:16px; top:50%; transform:translateY(-50%); background:#fffbeb; color:#d97706; font-size:10px; font-weight:800; padding:4px 8px; border-radius:6px; pointer-events:none; }
                .loc-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; }
                .loc-grid-2 .loc-input-wrap { margin-bottom:0; }
                .loc-modal-title-new { font-size:22px; font-weight:800; color:var(--text); margin:0 0 6px 0; font-family:'Sora', sans-serif; }
            </style>
            <div id="locationModalOverlay" class="loc-modal-overlay">
                <div class="loc-modal" style="padding:32px 28px; max-width:440px;">
                    <button class="loc-modal-close" onclick="LocationModal.close()" style="background:#f3f4f6; margin:-12px -12px 0 0;">
                        <i data-lucide="x" style="width:20px;height:20px;color:#4b5563"></i>
                    </button>
                    
                    <div id="locModalStep1">
                        <div class="loc-modal-header" style="text-align:left; margin-bottom:24px;">
                            <h2 class="loc-modal-title-new">Set Delivery Location</h2>
                            <p style="font-size:14px; color:var(--text-muted); margin:0; font-weight:500;">Please provide your address for accurate & fast delivery.</p>
                        </div>

                        <div class="loc-detect-btn-new" onclick="LocationModal.detectCurrent()" id="locDetectBtn">
                            <div class="loc-detect-icon-new" id="locDetectIcon">
                                <i data-lucide="navigation" style="width:22px;height:22px;fill:var(--primary-pale)"></i>
                            </div>
                            <div class="loc-detect-text-new">
                                <h4>Use Current Location</h4>
                                <p id="locDetectStatus">Using GPS for 100% accuracy</p>
                            </div>
                        </div>
                        
                        <div style="text-align:center; position:relative; margin:24px 0;">
                             <hr style="border:none; border-top:1px dashed var(--border); margin:0;">
                             <span style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:0 12px; font-size:12px; font-weight:700; color:var(--text-light)">OR ENTER MANUALLY</span>
                        </div>
                        
                        <button class="loc-save-btn-new" style="background:#f3f4f6; color:var(--text); box-shadow:none;" onclick="LocationModal.manualSelect('Tirupathur, TN')">
                            Search or Enter Address Manually
                        </button>
                    </div>

                    <div id="locModalStep2" style="display:none">
                        <div class="loc-modal-header" style="text-align:left; margin-bottom:16px">
                            <h2 class="loc-modal-title" style="margin:0">Confirm Address</h2>
                            <p id="locDetectedArea" style="font-size:13px; color:var(--primary); font-weight:700; margin-top:4px">📍 Detecting...</p>
                        </div>

                        <div class="loc-address-form">
                            <div class="loc-form-group">
                                <label>Door / House Number *</label>
                                <input type="text" id="locDoorNo" class="loc-form-input" placeholder="e.g. 42/A, Ground Floor">
                            </div>
                            <div class="loc-form-group">
                                <label>Street Name / Area *</label>
                                <input type="text" id="locStreet" class="loc-form-input" placeholder="e.g. Mariamman Koil St">
                                <div class="invalid-feedback" id="locStreetError" style="display:none"></div>
                            </div>
                            <div class="loc-form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px">
                                <div class="loc-form-group">
                                    <label>City *</label>
                                    <input type="text" id="locCity" class="loc-form-input" placeholder="City Name">
                                    <div class="invalid-feedback" id="locCityError" style="display:none"></div>
                                </div>
                                <div class="loc-form-group">
                                    <label>Pincode *</label>
                                    <input type="text" id="locPincode" class="loc-form-input" placeholder="635851" maxlength="6">
                                    <div class="invalid-feedback" id="locPincodeError" style="display:none"></div>
                                </div>
                            </div>
                            <div class="loc-form-group">
                                <label>Landmark (Optional)</label>
                                <input type="text" id="locLandmark" class="loc-form-input" placeholder="e.g. Near Govt Hospital">
                            </div>
                            <div class="loc-form-group">
                                <label>Contact Number *</label>
                                <input type="tel" id="locPhone" class="loc-form-input" placeholder="e.g. 9876543210" maxlength="10">
                                <div class="invalid-feedback" id="locPhoneError" style="display:none"></div>
                            </div>

                            <button class="loc-submit-btn" onclick="LocationModal.saveAddress()" id="locSaveBtn">
                                <span>Save & Proceed</span>
                                <i data-lucide="check-circle" style="width:18px;height:18px"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Add Real-time validation listeners
        document.getElementById('locStreet').addEventListener('input', e => Validation.validateInput(e.target, 'alpha', "Only letters and spaces are allowed", document.getElementById('locStreetError')));
        document.getElementById('locCity').addEventListener('input', e => Validation.validateInput(e.target, 'alpha', "Only letters and spaces are allowed", document.getElementById('locCityError')));
        document.getElementById('locPincode').addEventListener('input', e => Validation.validateInput(e.target, 'pincode', "Enter a valid 6-digit pincode", document.getElementById('locPincodeError')));
        document.getElementById('locPhone').addEventListener('input', e => Validation.validateInput(e.target, 'phone', "Enter a valid 10-digit phone number", document.getElementById('locPhoneError')));

        if (window.lucide) lucide.createIcons();
    }

    function open(addrToEdit = null) {
        inject();
        const overlay = document.getElementById('locationModalOverlay');
        overlay.classList.add('open');
        document.body.classList.add('body-no-scroll');
        
        let addr = addrToEdit;
        if (!addr) {
            const saved = localStorage.getItem('userAddress');
            if (saved) addr = JSON.parse(saved);
        }

        if (addr) {
            detectedInfo = addr; // Set as current detected info
            currentCoords = { lat: addr.latitude, lng: addr.longitude };
            
            // Store the ID being edited
            overlay.dataset.editingId = addr.id || '';

            // Fill fields
            document.getElementById('locDoorNo').value = addr.door_no || '';
            document.getElementById('locStreet').value = addr.street || '';
            document.getElementById('locCity').value = addr.city || addr.district || addr.area || '';
            document.getElementById('locPincode').value = addr.pincode || '';
            document.getElementById('locLandmark').value = addr.landmark || '';
            document.getElementById('locPhone').value = addr.contact_number || '';
            
            document.getElementById('locDetectedArea').textContent = addrToEdit ? `📍 Editing Address` : `📍 Editing Saved Address`;
            
            document.getElementById('locModalStep1').style.display = 'none';
            document.getElementById('locModalStep2').style.display = 'block';
        } else {
            // New user - Start at Step 1
            overlay.dataset.editingId = '';
            document.getElementById('locModalStep1').style.display = 'block';
            document.getElementById('locModalStep2').style.display = 'none';
            // Clear inputs
            document.getElementById('locDoorNo').value = '';
            document.getElementById('locStreet').value = '';
            document.getElementById('locCity').value = '';
            document.getElementById('locPincode').value = '';
            document.getElementById('locLandmark').value = '';
            document.getElementById('locPhone').value = '';
        }

        if (window.lucide) lucide.createIcons();
    }

    function close() {
        const overlay = document.getElementById('locationModalOverlay');
        if (overlay) {
            overlay.classList.remove('open');
            document.body.classList.remove('body-no-scroll');
        }
    }

    function detectCurrent() {
        const btn = document.getElementById('locDetectBtn');
        const status = document.getElementById('locDetectStatus');
        const icon = document.getElementById('locDetectIcon');
        const originalIcon = icon.innerHTML;

        btn.style.pointerEvents = 'none';
        status.textContent = "Requesting permission...";
        icon.innerHTML = '<div class="loc-spinner"></div>';

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(async function(position) {
                currentCoords = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                status.textContent = "Geocoding location...";
                
                try {
                    // Reverse geocoding with proper User-Agent header
                    const headers = new Headers({
                        'User-Agent': 'VillageFoodsApp/1.0 (Contact: hello@villagefoods.in)'
                    });

                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentCoords.lat}&lon=${currentCoords.lng}`, { headers });
                    const data = await res.json();
                    
                    detectedInfo = {
                        area: data.address.suburb || data.address.neighbourhood || data.address.village || data.address.hamlet || data.address.subdistrict || '',
                        city: data.address.city || data.address.town || data.address.municipality || data.address.village || data.address.district || data.address.county || '',
                        district: data.address.district || data.address.state_district || data.address.county || '',
                        state: data.address.state || '',
                        pincode: data.address.postcode || ''
                    };

                    showStep2();
                } catch (e) {
                    resetDetectBtn(btn, status, icon, originalIcon);
                    if (typeof Toast !== 'undefined') Toast.show('Reverse geocoding failed.', 'error');
                }
            }, function() {
                resetDetectBtn(btn, status, icon, originalIcon);
                if (typeof Toast !== 'undefined') Toast.show('Location access denied.', 'error');
            }, { enableHighAccuracy: true });
        } else {
            resetDetectBtn(btn, status, icon, originalIcon);
            if (typeof Toast !== 'undefined') Toast.show('Geolocation not supported.', 'error');
        }
    }

    function manualSelect(city) {
        // Fallback for manual city selection
        currentCoords = { lat: 12.5055, lng: 78.5776 }; // Default Tirupathur coordinates
        const cityName = city.split(',')[0];
        detectedInfo = {
            area: cityName,
            city: cityName,
            district: cityName,
            state: 'Tamil Nadu',
            pincode: ''
        };
        showStep2();
    }

    function showStep2() {
        document.getElementById('locModalStep1').style.display = 'none';
        document.getElementById('locModalStep2').style.display = 'block';
        
        document.getElementById('locDetectedArea').textContent = `📍 ${detectedInfo.area || detectedInfo.city}, ${detectedInfo.district}`;
        document.getElementById('locStreet').value = detectedInfo.area;
        document.getElementById('locCity').value = detectedInfo.city || detectedInfo.district || '';
        document.getElementById('locPincode').value = detectedInfo.pincode;
        
        if (window.lucide) lucide.createIcons();
    }

    async function saveAddress() {
        const doorNo = document.getElementById('locDoorNo').value.trim();
        const street = document.getElementById('locStreet').value.trim();
        const city = document.getElementById('locCity').value.trim();
        const pincode = document.getElementById('locPincode').value.trim();
        const landmark = document.getElementById('locLandmark').value.trim();
        const phone = document.getElementById('locPhone').value.trim();
        
        if (!doorNo || !street || !city || !pincode || !phone) {
            if (typeof Toast !== 'undefined') Toast.show('Please fill all required fields (*)', 'error');
            return;
        }

        const isStreetValid = Validation.validateInput(document.getElementById('locStreet'), 'alpha', "Only letters and spaces are allowed", document.getElementById('locStreetError'));
        const isCityValid = Validation.validateInput(document.getElementById('locCity'), 'alpha', "Only letters and spaces are allowed", document.getElementById('locCityError'));
        const isPincodeValid = Validation.validateInput(document.getElementById('locPincode'), 'pincode', "Enter a valid 6-digit pincode", document.getElementById('locPincodeError'));
        const isPhoneValid = Validation.validateInput(document.getElementById('locPhone'), 'phone', "Enter a valid 10-digit phone number", document.getElementById('locPhoneError'));

        if (!isStreetValid || !isCityValid || !isPincodeValid || !isPhoneValid) {
            if (typeof Toast !== 'undefined') Toast.show('Please fix the errors before saving', 'error');
            return;
        }

        const saveBtn = document.getElementById('locSaveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<div class="loc-spinner"></div> Saving...';

        const addressData = {
            latitude: currentCoords.lat,
            longitude: currentCoords.lng,
            door_no: doorNo,
            street: street,
            landmark: landmark,
            area: detectedInfo.area,
            city: city,
            district: detectedInfo.district || city,
            state: detectedInfo.state,
            pincode: pincode,
            contact_number: phone,
            is_default: 1,
            address_id: document.getElementById('locationModalOverlay').dataset.editingId || null
        };

        try {
            const res = await fetch('api/orders/save_address.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(addressData)
            });
            const result = await res.json();

            if (result.status === 'success') {
                // UI persistence
                addressData.id = result.address_id; // Store the database ID
                localStorage.setItem('userAddress', JSON.stringify(addressData));
                updateNavbar(addressData);
                if (window.updateAddressDisplay) window.updateAddressDisplay();
                close();
                if (typeof Toast !== 'undefined') Toast.show('Address saved!', 'success');
                
                // Refresh if on index to ensure service check/products
                if (window.location.pathname.includes('index.php') || window.location.pathname === '/' || window.location.pathname.endsWith('/')) {
                   // location.reload();
                }
            } else {
                if (typeof Toast !== 'undefined') Toast.show(result.message, 'error');
            }
        } catch (e) {
            if (typeof Toast !== 'undefined') Toast.show('Failed to save address. Try again.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<span>Save & Proceed</span><i data-lucide="check-circle" style="width:18px;height:18px"></i>';
            if (window.lucide) {
                const step2 = document.getElementById('locModalStep2');
                if (step2) {
                    lucide.createIcons({
                        attrs: { 'data-lucide': true },
                        scope: step2
                    });
                }
            }
        }
    }

    function updateNavbar(address) {
        const display = document.getElementById('userLocation');
        if (display) {
            display.innerHTML = `<div>${address.door_no}, ${address.street}</div><div style="font-size:11px; font-weight:500; color:var(--text-muted)">${address.area}, ${address.city}</div>`;
        }
    }

    function resetDetectBtn(btn, status, icon, originalIcon) {
        btn.style.pointerEvents = 'all';
        status.textContent = "Using GPS for 100% accuracy";
        icon.innerHTML = originalIcon;
        if (window.lucide) lucide.createIcons();
    }

    return { open, close, detectCurrent, manualSelect, saveAddress };
})();

async function initLocationDetection() {
    let savedAddress = localStorage.getItem('userAddress');
    const locationDisplay = document.getElementById('userLocation');
    
    // If not in localStorage, check if logged in and fetch from DB
    if (typeof Auth !== 'undefined') {
        const auth = await Auth.checkStatus();
        
        // Skip location logic for delivery partners
        if (auth.logged_in && auth.role === 'delivery') {
            return;
        }

        if (!savedAddress && auth.logged_in && auth.address) {
            savedAddress = JSON.stringify(auth.address);
            localStorage.setItem('userAddress', savedAddress);
        }
    }
    
    if (savedAddress) {
        const address = JSON.parse(savedAddress);
        if (locationDisplay) {
            locationDisplay.innerHTML = `<div>${address.door_no}, ${address.street}</div><div style="font-size:11px; font-weight:500; color:var(--text-muted)">${address.area}, ${address.city}</div>`;
        }
    } else {
        setTimeout(() => LocationModal.open(), 1000);
    }
}

// Global click listener for re-opening
document.addEventListener('click', function(e) {
    if (e.target && (e.target.id === 'userLocation' || e.target.closest('.nav-location'))) {
        LocationModal.open();
    }
});

// Init
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLocationDetection);
} else {
    initLocationDetection();
}
