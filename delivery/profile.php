<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
require_once '../includes/auth_helper.php';
checkPersistentLogin($pdo);

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    header('Location: ../index');
    exit;
}

$pageTitle = 'My Profile — Village Foods';
$bodyClass = 'db-body';
include 'layouts/header.php';

$navTitle = 'Partner Profile';
include 'layouts/top_nav.php';
?>
<style>
    .profile-container {
        max-width: 860px;
        margin: 0 auto;
        padding: 20px;
        position: relative;
    }

    /* Modern Progress Bar - Impressive Revamp */
    .onboarding-progress-container {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 30px;
        padding: 40px;
        margin-bottom: 32px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
    }

    .onboarding-status-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        max-width: 600px;
        margin: 0 auto;
    }

    .status-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        z-index: 2;
        flex: 1;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .step-circle {
        width: 45px;
        height: 45px;
        background: white;
        border: 2px solid #f1f5f9;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .status-step.active .step-circle {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-color: transparent;
        transform: scale(1.1);
        box-shadow: 0 12px 24px rgba(16, 185, 129, 0.3), 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    /* Active Step Pulse Glow */
    .status-step.active:last-of-type .step-circle::after,
    .status-step.active.current-step .step-circle::after {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 24px;
        background: var(--primary);
        opacity: 0.2;
        animation: pulse-glow 2s infinite;
        z-index: -1;
    }

    @keyframes pulse-glow {
        0% { transform: scale(0.95); opacity: 0.4; }
        50% { transform: scale(1.1); opacity: 0.2; }
        100% { transform: scale(0.95); opacity: 0.4; }
    }

    .status-step span {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #64748b;
        transition: all 0.3s;
    }

    .status-step.active span { color: #065f46; letter-spacing: 2px; }

    .status-line {
        position: absolute;
        top: 25px;
        height: 4px;
        background: #f1f5f9;
        width: 73%;
        left: 13%;
        z-index: 1;
        border-radius: 2px;
        overflow: hidden;
    }

    .status-line-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        width: 0%;
        transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
    }

    /* Profile Header Card */
    .profile-header-card {
        background: var(--white);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        padding: 40px;
        margin-bottom: 32px;
        text-align: center;
        box-shadow: var(--shadow);
    }

    .profile-photo-wrapper {
        width: 110px; height: 110px;
        margin: 0 auto 24px;
        position: relative;
        cursor: pointer;
    }
    .profile-photo {
        width: 100%; height: 100%;
        background: var(--primary-pale);
        border: 1px solid rgba(26, 156, 62, 0.1);
        border-radius: 36px;
        display: flex; align-items: center; justify-content: center;
        font-size: 40px; font-weight: 900; color: var(--primary);
        font-family: 'Sora', sans-serif;
        overflow: hidden;
    }
    .photo-overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,0.4);
        border-radius: 36px;
        display: flex; align-items: center; justify-content: center;
        color: white; opacity: 0;
        transition: all 0.3s ease;
        backdrop-filter: blur(2px);
    }
    .profile-photo-wrapper:hover .photo-overlay { opacity: 1; transform: scale(1.05); }

    .profile-name { font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -1px; }
    .profile-email { color: var(--text-muted); font-size: 16px; margin: 8px 0 24px; }

    .meta-tags { display: flex; align-items: center; justify-content: center; gap: 12px; }
    /* Toast */
    #toast-container { position: fixed; top: 110px; right: 40px; z-index: 1000; }
    .toast {
        background: #1e293b; color: white; padding: 16px 28px; border-radius: 20px;
    }
    .meta-tag {
        background: var(--bg);
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
        border: 1px solid var(--border);
    }

    /* Section Cards */
    .profile-section {
        background: var(--white);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        margin-bottom: 24px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .section-header {
        padding: 24px 32px;
        border-bottom: 1px solid var(--border-light);
        display: flex; align-items: center; justify-content: space-between;
    }

    .section-title { font-size: 18px; font-weight: 800; color: var(--text); }
    
    .edit-btn {
        padding: 8px 20px;
        background: var(--primary-pale);
        color: var(--primary);
        border: 1px solid rgba(26, 156, 62, 0.2);
        border-radius: 12px;
        font-weight: 800;
        font-size: 12px;
        cursor: pointer;
    }

    .section-content { padding: 32px; }

    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; }
    .info-label { font-size: 10px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 4px; }
    .info-value { font-size: 15px; font-weight: 700; color: var(--text); }

    /* Forms */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .premium-input {
        width: 100%; border-radius: 12px; padding: 14px 18px;
        background: var(--bg); border: 1px solid var(--border);
        color: var(--text); font-family: inherit; font-size: 14px;
    }
    .premium-input:focus { border-color: var(--primary); background: var(--white); box-shadow: 0 0 0 4px var(--primary-glow); }
    
    .form-label::after { content: " *"; color: #ef4444; font-weight: 800; }
    .form-label.optional::after { content: ""; }

    .btn-save { padding: 14px; background: var(--primary); color: white; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; }
    .btn-cancel { padding: 14px 20px; background: var(--border-light); color: var(--text-muted); border: none; border-radius: 14px; font-weight: 800; cursor: pointer; }

    /* Documents */
    .doc-card {
        background: var(--bg);
        border: 1px solid var(--border-light);
        border-radius: 20px;
        padding: 20px;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 12px;
    }
    .doc-meta h4 { margin: 0; font-size: 14px; font-weight: 800; }
    .doc-meta p { margin: 2px 0 0; font-size: 12px; color: var(--text-dim); }

    .upload-trigger {
        background: var(--white); color: var(--primary);
        padding: 10px 18px; border-radius: 10px; border: 1.5px dashed var(--primary);
        font-weight: 800; font-size: 12px; cursor: pointer;
    }

    .btn-view {
        width: 44px; height: 44px;
        display: flex; align-items: center; justify-content: center;
        background: var(--primary-pale); color: var(--primary);
        border: 1.5px solid rgba(26, 156, 62, 0.1); border-radius: 50%;
        cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(26, 156, 62, 0.1);
    }
    .btn-view:hover {
        background: var(--primary); color: white;
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 8px 20px var(--primary-glow);
    }
    .btn-view i { width: 22px; height: 22px; stroke-width: 2.5px; }

    .hidden { display: none !important; }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .onboarding-status-bar span { display: none; }
    }
        /* Skeleton Loading */
        .skeleton-text {
            display: inline-block;
            height: 1em;
            background: linear-gradient(90deg, var(--bg) 25%, var(--border-light) 50%, var(--bg) 75%);
            background-size: 200% 100%;
            animation: skeleton-pulse 1.5s infinite;
            border-radius: 4px;
            vertical-align: middle;
        }
        @keyframes skeleton-pulse {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Custom Premium Modal */
        .premium-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .premium-modal-overlay.open {
            opacity: 1;
            pointer-events: all;
        }

        .premium-modal-card {
            background: white;
            width: 90%;
            max-width: 420px;
            border-radius: 32px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 30px 60px -12px rgba(0,0,0,0.25);
            transform: scale(0.9) translateY(20px);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .premium-modal-overlay.open .premium-modal-card {
            transform: scale(1) translateY(0);
        }

        .modal-warning-icon {
            width: 72px;
            height: 72px;
            background: #fff7ed;
            color: #ea580c;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            animation: bounce-slow 2s infinite;
        }

        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .modal-title-alt {
            font-size: 22px;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .modal-text {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .modal-btn {
            padding: 16px;
            border-radius: 18px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-secondary-alt {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-secondary-alt:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .btn-confirm-alt {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        .btn-confirm-alt:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3);
            filter: brightness(1.1);
        }
    </style>
</head>
<body>
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>
    
    <div id="toast-container"></div>

    <!-- Premium Security Modal -->
    <div class="premium-modal-overlay" id="securityModal">
        <div class="premium-modal-card">
            <div class="modal-warning-icon">
                <i data-lucide="alert-triangle" style="width:36px; height:36px"></i>
            </div>
            <h2 class="modal-title-alt">Security Warning</h2>
            <p class="modal-text">
                Your profile is currently <strong>VERIFIED</strong>. Editing these details will suspend your status and require Admin re-approval.
            </p>
            <div class="modal-actions">
                <button class="modal-btn btn-secondary-alt" onclick="closeSecurityModal()">Abort</button>
                <button class="modal-btn btn-confirm-alt" id="confirmSecurityBtn">I Understand</button>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <!-- Top Nav -->
        <div class="nav-header">
            <a href="dashboard.php" class="back-btn"><i data-lucide="arrow-left"></i></a>
            <div class="nav-title">Partner Onboarding</div>
            <div style="width:44px"></div>
        </div>

        <!-- Onboarding Status -->
        <div class="onboarding-progress-container">
            <div class="onboarding-status-bar">
                <div class="status-line"><div class="status-line-fill" id="status-line-fill"></div></div>
                
                <div class="status-step" id="step-incomplete">
                    <div class="step-circle"><i data-lucide="user-plus"></i></div>
                    <span>Signup</span>
                </div>
                
                <div class="status-step" id="step-pending">
                    <div class="step-circle"><i data-lucide="shield-check"></i></div>
                    <span>Review</span>
                </div>
                
                <div class="status-step" id="step-verified">
                    <div class="step-circle"><i data-lucide="award"></i></div>
                    <span>Verified</span>
                </div>
            </div>
        </div>

        <!-- User Profile Card -->
        <div class="profile-header-card">
            <div class="profile-photo-wrapper" onclick="document.getElementById('profileUpload').click()">
                <div class="profile-photo" id="profileInitial"><?= strtoupper(substr($_SESSION['user_name'] ?? 'P', 0, 1)) ?></div>
                <div class="photo-overlay">
                    <div style="text-align:center">
                        <i data-lucide="camera" style="width:28px; height:28px; margin-bottom:4px"></i>
                        <div style="font-size:10px; font-weight:800; color:#fbbf24">REQUIRED</div>
                    </div>
                </div>
                <input type="file" id="profileUpload" hidden accept="image/*" onchange="uploadProfilePhoto(this)">
            </div>
            <h1 class="profile-name" id="pName"><span class="skeleton-text" style="width:160px"></span></h1>
            <div class="profile-email" id="pEmail"><span class="skeleton-text" style="width:200px"></span></div>
            <div class="meta-tags">
                <div class="meta-tag"><i data-lucide="calendar"></i> Joined <span id="pJoined"><span class="skeleton-text" style="width:60px"></span></span></div>
                <div class="meta-tag" id="pStatusTag"><i data-lucide="shield-check"></i> <span id="pStatusText">Active Partner</span></div>
            </div>
        </div>

        <!-- Personal Info -->
        <div class="profile-section">
            <div class="section-header">
                <div class="section-title"><i data-lucide="user"></i> Personal Info</div>
                <button class="edit-btn" onclick="toggleEdit('personal')">Update</button>
            </div>
            <div class="section-content" id="view-personal">
                <div class="info-grid">
                    <div class="info-item"><div class="info-label">Full Name</div><div class="info-value" id="val-full_name">Not Set</div></div>
                    <div class="info-item"><div class="info-label">Phone Number</div><div class="info-value" id="val-phone">Not Set</div></div>
                    <div class="info-item"><div class="info-label">Email</div><div class="info-value" id="val-email">...</div></div>
                    <div class="info-item"><div class="info-label">City</div><div class="info-value" id="val-city">Not Set</div></div>
                    <div class="info-item"><div class="info-label">Area</div><div class="info-value" id="val-area">Not Set</div></div>
                </div>
            </div>
            <form class="section-content hidden" id="edit-personal">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="full_name" id="input-full_name" class="premium-input" required pattern="[a-zA-Z\s]{3,50}" title="Only letters and spaces (3-50 chars)" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"></div>
                    <div class="form-group"><label class="form-label">Phone Number</label><input type="tel" name="phone" id="input-phone" class="premium-input" required pattern="[6-9][0-9]{9}" title="Valid 10-digit Indian mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)"></div>
                    <div class="form-group"><label class="form-label">City</label><input type="text" name="city" id="input-city" class="premium-input" required placeholder="Enter your city"></div>
                    <div class="form-group"><label class="form-label">Area</label><input type="text" name="area" id="input-area" class="premium-input" required placeholder="Enter your area"></div>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn-cancel" onclick="toggleEdit('personal')">Cancel</button>
                    <button type="button" class="btn-save" onclick="saveProfile('personal')">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Vehicle Details -->
        <div class="profile-section">
            <div class="section-header">
                <div class="section-title"><i data-lucide="bike"></i> Vehicle Details</div>
                <button class="edit-btn" onclick="toggleEdit('vehicle')">Update</button>
            </div>
            <div class="section-content" id="view-vehicle">
                <div class="info-grid">
                    <div class="info-item"><div class="info-label">Vehicle Type</div><div class="info-value" id="val-vehicle_type">Not Set</div></div>
                    <div class="info-item"><div class="info-label">Vehicle Number</div><div class="info-value" id="val-vehicle_number">Not Set</div></div>
                    <div class="info-item"><div class="info-label">License Number</div><div class="info-value" id="val-license_number">Not Set</div></div>
                </div>
            </div>
            <form class="section-content hidden" id="edit-vehicle">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" id="input-vehicle_type" class="premium-input" required>
                            <option value="">Select Type</option>
                            <option value="Bike">Bike</option>
                            <option value="Cycle">Cycle</option>
                            <option value="Scooter">Scooter</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Vehicle Number</label><input type="text" name="vehicle_number" id="input-vehicle_number" class="premium-input" required></div>
                    <div class="form-group full"><label class="form-label">Driving License Number</label><input type="text" name="license_number" id="input-license_number" class="premium-input" required></div>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn-cancel" onclick="toggleEdit('vehicle')">Cancel</button>
                    <button type="button" class="btn-save" onclick="saveProfile('vehicle')">Save Details</button>
                </div>
            </form>
        </div>

        <!-- Bank Details -->
        <div class="profile-section">
            <div class="section-header">
                <div class="section-title"><i data-lucide="banknote"></i> Bank Account</div>
                <button class="edit-btn" onclick="toggleEdit('bank')">Update</button>
            </div>
            <div class="section-content" id="view-bank">
                <div class="info-grid">
                    <div class="info-item"><div class="info-label">Bank Name</div><div class="info-value" id="val-bank_name">Not Set</div></div>
                    <div class="info-item"><div class="info-label">Account Number</div><div class="info-value" id="val-account_number">Not Set</div></div>
                    <div class="info-item"><div class="info-label">IFSC Code</div><div class="info-value" id="val-ifsc_code">Not Set</div></div>
                </div>
            </div>
            <form class="section-content hidden" id="edit-bank">
                <div class="form-grid">
                    <div class="form-group full"><label class="form-label">Bank Name</label><input type="text" name="bank_name" id="input-bank_name" class="premium-input" required pattern="[a-zA-Z\s]{3,100}" title="Enter your official bank name" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"></div>
                    <div class="form-group"><label class="form-label">Account Holder Name</label><input type="text" name="holder_name" id="input-holder_name" class="premium-input" required pattern="[a-zA-Z\s]{3,50}" title="Only letters and spaces" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"></div>
                    <div class="form-group"><label class="form-label">Account Number</label><input type="text" name="account_number" id="input-account_number" class="premium-input" required pattern="[0-9]{9,18}" title="9 to 18 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
                    <div class="form-group"><label class="form-label">IFSC Code</label><input type="text" name="ifsc_code" id="input-ifsc_code" class="premium-input" required pattern="[A-Z]{4}0[A-Z0-9]{6}" title="Standard 11-char IFSC code (e.g. SBIN0123456)" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0,11)"></div>
                    <div class="form-group"><label class="form-label">UPI ID</label><input type="text" name="upi_id" id="input-upi_id" class="premium-input" required placeholder="example@upi" pattern="[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}" title="Enter a valid UPI ID (e.g. name@bank)"></div>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn-cancel" onclick="toggleEdit('bank')">Cancel</button>
                    <button type="button" class="btn-save" onclick="saveProfile('bank')">Save Bank Info</button>
                </div>
            </form>
        </div>

        <!-- Documents -->
        <div class="profile-section">
            <div class="section-header">
                <div class="section-title"><i data-lucide="file-check"></i> Document Center</div>
            </div>
            <div class="section-content">
                <div class="doc-list">
                    <div class="doc-card">
                        <div class="doc-meta"><h4>Driving License</h4><p id="status-license_doc">...</p></div>
                        <div style="display:flex;gap:12px">
                            <label class="upload-trigger"><i data-lucide="upload"></i> Upload<input type="file" hidden onchange="uploadDoc('license_doc', this)"></label>
                            <button class="btn-view hidden" id="view-license_doc"><i data-lucide="eye"></i></button>
                        </div>
                    </div>
                    <div class="doc-card">
                        <div class="doc-meta"><h4>Aadhaar Card</h4><p id="status-aadhaar_doc">...</p></div>
                        <div style="display:flex;gap:12px">
                            <label class="upload-trigger"><i data-lucide="upload"></i> Upload<input type="file" hidden onchange="uploadDoc('aadhaar_doc', this)"></label>
                            <button class="btn-view hidden" id="view-aadhaar_doc"><i data-lucide="eye"></i></button>
                        </div>
                    </div>
                    <div class="doc-card">
                        <div class="doc-meta"><h4>Vehicle RC</h4><p id="status-rc_doc">...</p></div>
                        <div style="display:flex;gap:12px">
                            <label class="upload-trigger"><i data-lucide="upload"></i> Upload<input type="file" hidden onchange="uploadDoc('rc_doc', this)"></label>
                            <button class="btn-view hidden" id="view-rc_doc"><i data-lucide="eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Logout -->
        <div style="text-align:center; padding-top:20px">
            <button onclick="window.location.href='logout.php'" style="background:none; border:none; color:#ef4444; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px">
                <i data-lucide="log-out"></i> Logout Account
            </button>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        let profileData = null;

        document.addEventListener('DOMContentLoaded', () => {
            fetchProfile();
            if (window.lucide) lucide.createIcons();
        });

        async function fetchProfile() {
            try {
                const res = await fetch('../api/delivery/get_profile.php');
                const result = await res.json();
                if (result.success) {
                    profileData = result.data;
                    renderProfile();
                }
            } catch (err) { showToast('Failed to load profile','error'); }
        }

        function updateStatusBar(status) {
            const steps = ['incomplete', 'pending', 'verified'];
            const statusMap = { 'Profile Incomplete': 0, 'Verification Pending': 1, 'Verified': 2, 'Rejected': 1 };
            const activeIndex = statusMap[status] ?? 0;
            const fillWidth = activeIndex === 0 ? 0 : activeIndex === 1 ? 50 : 100;
            
            const fillEl = document.getElementById('status-line-fill');
            if (fillEl) {
                fillEl.style.width = fillWidth + '%';
                if (status === 'Rejected') {
                    fillEl.style.background = 'linear-gradient(90deg, #ef4444, #b91c1c)';
                } else {
                    fillEl.style.background = 'linear-gradient(90deg, #10b981, #059669)';
                }
            }
            
            steps.forEach((s, idx) => {
                const el = document.getElementById(`step-${s}`);
                if (!el) return;

                el.classList.remove('active', 'current-step');
                
                if (idx <= activeIndex) {
                    el.classList.add('active');
                }
                
                // Add current-step to only the exact current step for the pulse effect
                if (idx === activeIndex) {
                    el.classList.add('current-step');
                }

                if (status === 'Rejected' && s === 'pending') {
                    const circle = el.querySelector('.step-circle');
                    const label = el.querySelector('span');
                    if (circle) {
                        circle.style.background = 'linear-gradient(135deg, #ef4444, #b91c1c)';
                        circle.style.borderColor = 'transparent';
                        circle.innerHTML = '<i data-lucide="x-circle"></i>';
                    }
                    if (label) {
                        label.textContent = 'Rejected';
                        label.style.color = '#ef4444';
                    }
                }
            });
            if (window.lucide) lucide.createIcons();
        }

        function renderProfile() {
            const d = profileData;
            const name = d.full_name || 'Village Partner';
            const initial = name.charAt(0).toUpperCase();
            const photoEl = document.getElementById('profileInitial');
            
            if (d.image) {
                photoEl.innerHTML = `<img src="../${d.image}" style="width:100%;height:100%;border-radius:50px;object-fit:cover;">`;
            } else { photoEl.textContent = initial; }

            document.getElementById('pName').textContent = name;
            document.getElementById('pEmail').textContent = d.email;
            document.getElementById('pJoined').textContent = new Date(d.created_at).toLocaleDateString(undefined, { month: 'short', year: 'numeric' });
            
            updateStatusBar(d.partner_status);

            // Update Status Tag
            const statusText = document.getElementById('pStatusText');
            const statusTag = document.getElementById('pStatusTag');
            if (statusText && statusTag) {
                statusText.textContent = d.partner_status || 'Unknown Status';
                statusTag.style.background = d.partner_status === 'Verified' ? 'var(--primary-pale)' : 
                                            d.partner_status === 'Rejected' ? '#fef2f2' : 'var(--bg)';
                statusTag.style.color = d.partner_status === 'Verified' ? 'var(--primary)' : 
                                       d.partner_status === 'Rejected' ? '#ef4444' : 'var(--text-muted)';
                
                const icon = statusTag.querySelector('i, svg');
                if (icon) {
                    icon.setAttribute('data-lucide', 
                        d.partner_status === 'Verified' ? 'check-circle' : 
                        d.partner_status === 'Rejected' ? 'x-circle' : 'clock'
                    );
                }
                if (window.lucide) lucide.createIcons();
            }

            // Update Update/Edit Buttons text
            const isVerified = d.partner_status === 'Verified';
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.textContent = isVerified ? 'Request Change' : 'Update';
                btn.style.background = isVerified ? '#fff7ed' : 'var(--primary-pale)';
                btn.style.color = isVerified ? '#c2410c' : 'var(--primary)';
                btn.style.borderColor = isVerified ? '#ffedd5' : 'rgba(26, 156, 62, 0.2)';
            });

            // Redraw mapping
            const mapField = (id, val) => {
                const view = document.getElementById(`val-${id}`);
                const input = document.getElementById(`input-${id}`);
                if(view) view.textContent = val || 'Not Set';
                if(input) input.value = val || '';
            }

            mapField('full_name', d.full_name); mapField('phone', d.phone); mapField('email', d.email);
            mapField('city', d.city); mapField('area', d.area);
            mapField('vehicle_type', d.vehicle_type); mapField('vehicle_number', d.vehicle_number); mapField('license_number', d.license_number);
            ['bank_name', 'holder_name', 'account_number', 'ifsc_code', 'upi_id'].forEach(f => mapField(f, d[f]));

            const docs = ['license_doc', 'aadhaar_doc', 'rc_doc'];
            docs.forEach(doc => {
                const statusEl = document.getElementById(`status-${doc}`);
                const viewBtn = document.getElementById(`view-${doc}`);
                if (d[doc]) {
                    const isVerified = d.partner_status === 'Verified';
                    const isRejected = d.partner_status === 'Rejected';
                    
                    if (isVerified) {
                        statusEl.textContent = 'Verified';
                        statusEl.style.color = '#10b981';
                    } else if (isRejected) {
                        statusEl.textContent = 'Rejected - Please Re-upload';
                        statusEl.style.color = '#ef4444';
                    } else {
                        statusEl.textContent = 'Pending Review';
                        statusEl.style.color = '#f59e0b';
                    }
                    
                    viewBtn.classList.remove('hidden');
                    viewBtn.onclick = () => window.open('../' + d[doc], '_blank');
                } else {
                    statusEl.textContent = 'Required';
                    statusEl.style.color = '#64748b';
                    viewBtn.classList.add('hidden');
                }
            });

            if (window.lucide) lucide.createIcons();
        }

        function openSecurityModal(onConfirm) {
            const modal = document.getElementById('securityModal');
            const confirmBtn = document.getElementById('confirmSecurityBtn');
            
            modal.classList.add('open');
            if (window.lucide) lucide.createIcons();
            
            confirmBtn.onclick = () => {
                modal.classList.remove('open');
                onConfirm();
            };
        }

        function closeSecurityModal() {
            document.getElementById('securityModal').classList.remove('open');
        }

        function toggleEdit(section) {
            const isVerified = profileData && profileData.partner_status === 'Verified';
            const editForm = document.getElementById(`edit-${section}`);
            
            const performToggle = () => {
                document.getElementById(`view-${section}`).classList.toggle('hidden');
                editForm.classList.toggle('hidden');
            };

            // Show warning only when opening the form for a verified user
            if (isVerified && editForm.classList.contains('hidden')) {
                openSecurityModal(performToggle);
            } else {
                performToggle();
            }
        }

        async function saveProfile(section) {
            const form = document.getElementById(`edit-${section}`);
            
            // Client-side validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            showToast('Saving changes...');
            try {
                const res = await fetch('../api/delivery/save_profile.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    showToast('Profile updated!');
                    toggleEdit(section); fetchProfile();
                } else showToast(result.error || 'Update failed', 'error');
            } catch (err) { showToast('Network error', 'error'); }
        }

        async function uploadDoc(docType, input) {
            if (!input.files[0]) return;
            const formData = new FormData();
            formData.append(docType, input.files[0]);
            showToast('Uploading...');
            try {
                const res = await fetch('../api/delivery/save_profile.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    showToast('File uploaded!'); fetchProfile();
                } else showToast(result.error || 'Upload failed', 'error');
            } catch (err) { showToast('Network error', 'error'); }
        }

        async function uploadProfilePhoto(input) {
            if (!input.files[0]) return;
            const formData = new FormData();
            formData.append('profile_image', input.files[0]);
            showToast('Uploading photo...');
            try {
                const res = await fetch('../api/delivery/save_profile.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    showToast('Profile photo updated!');
                    fetchProfile(); // Refresh the display
                } else showToast(result.error || 'Upload failed', 'error');
            } catch (err) { showToast('Network error', 'error'); }
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            container.innerHTML = ''; // Clear previous toasts to avoid clutter
            const toast = document.createElement('div');
            toast.className = 'toast';
            if (type === 'error') toast.style.borderLeftColor = '#ef4444';
            toast.innerHTML = `<i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" style="color:${type === 'success' ? '#10b981' : '#ef4444'}"></i><span>${message}</span>`;
            container.appendChild(toast);
            if (window.lucide) lucide.createIcons();
            setTimeout(() => { if(toast.parentNode) toast.remove(); }, 4000);
        }
    </script>
<?php 
echo '<script src="../assets/js/delivery.js?v=' . time() . '"></script>';
include 'layouts/footer.php'; 
?>
</body>
</html>

