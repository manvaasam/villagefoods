<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    header('Location: index.php');
    exit;
}
require_once '../includes/db.php';
$pageTitle = 'Earnings & Wallet — Village Foods';
$bodyClass = 'db-body';
include 'layouts/header.php';

$navTitle = 'My Earnings';
include 'layouts/top_nav.php';
?>

<div class="db-content" style="padding: 20px 16px 100px;">
    
    <!-- MAIN BALANCE CARD -->
    <div class="premium-card" style="padding:32px; background:linear-gradient(135deg, #065f46 0%, #064e3b 100%); color:white; border-radius:32px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); margin-bottom: 32px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; font-size: 120px; opacity: 0.1; color: white; transform: rotate(15deg); pointer-events: none;">
            <i data-lucide="indian-rupee"></i>
        </div>
        <div style="font-size:14px; opacity:0.8; margin-bottom:8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Available Balance</div>
        <div id="walletBalance" style="font-size:48px; font-weight:900; margin-bottom:24px; letter-spacing: -1px;">₹0.00</div>
        
        <div style="display:flex; gap:12px; position: relative; z-index: 1;">
            <button class="wallet-btn" style="flex:1; background:white; color:#064e3b; padding: 16px; border-radius: 16px; font-weight: 800; border: none; cursor: pointer;" onclick="DeliveryConsole.requestWithdrawal()">Withdraw Now</button>
            <button class="wallet-btn" style="flex:1; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color: white; padding: 16px; border-radius: 16px; font-weight: 800; cursor: pointer;" onclick="window.location.href='history.php'">View Logs</button>
        </div>
    </div>

    <!-- PAYMENT METHODS -->
    <div style="margin-bottom: 32px;">
        <div style="font-weight: 800; font-size: 15px; color: var(--text-main); margin-bottom: 16px; padding-left: 4px;">Connected Accounts</div>
        <div class="db-order-card" style="display:flex; align-items:center; gap:20px; padding: 20px; margin: 0;">
            <div style="width:48px; height:48px; border-radius:14px; background:rgba(16, 185, 129, 0.1); display:flex; align-items:center; justify-content:center; color:var(--primary);"><i data-lucide="building-2"></i></div>
            <div style="flex:1">
                <div style="font-weight:800; font-size:15px; color: var(--text-main);">Bank Account</div>
                <div style="font-size:13px; color:var(--text-dim)" id="bankMeta">State Bank of India ·· 1234</div>
            </div>
            <button class="tbl-btn-edit" style="background:var(--glass-light); border: 1px solid var(--border); color: black; padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700;" onclick="window.location.href='profile.php'">Edit</button>
        </div>
    </div>

    <!-- RECENT TRANSACTIONS -->
    <div>
        <div style="font-weight: 800; font-size: 15px; color: var(--text-main); margin-bottom: 16px; padding-left: 4px;">Recent Earning Activity</div>
        <div id="transactionLogs">
            <div style="text-align:center; padding:40px; color:var(--text-dim); background: var(--glass); border-radius: 20px; border: 1px solid var(--border);">
                 <i data-lucide="info" style="width:32px; height:32px; opacity:0.2; margin-bottom:12px"></i>
                 <div style="font-size:14px; font-weight: 600;">Daily breakdown will update automatically</div>
            </div>
        </div>
    </div>

    <!-- WITHDRAWAL MODAL -->
    <div id="withdrawalModal" class="modal-overlay">
        <div class="modal" style="max-width: 400px; padding: 24px; border-radius: 32px;">
            <button class="modal-close" onclick="DeliveryConsole.closeWithdrawalModal()" style="position: absolute; top: 16px; right: 16px; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b;"><i data-lucide="x" style="width: 18px; height: 18px;"></i></button>
            
            <div style="background: rgba(6, 95, 70, 0.1); width: 64px; height: 64px; border-radius: 22px; margin: 0 auto 20px; color: #065f46; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="wallet" style="width: 32px; height: 32px;"></i>
            </div>
            
            <div style="text-align: center; font-size: 20px; font-weight: 900; color: #1e293b; margin-bottom: 8px;">Request Payout</div>
            <div style="text-align: center; font-size: 13px; color: #64748b; margin-bottom: 24px; line-height: 1.5; font-weight: 500;">Enter the amount you wish to withdraw to your linked bank account.</div>

            <form id="withdrawalForm" onsubmit="event.preventDefault(); DeliveryConsole.submitWithdrawal();">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="font-size: 12px; font-weight: 800; color: #475569; margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">Withdrawal Amount</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-weight: 900; color: #94a3b8; font-size: 18px;">₹</span>
                        <input type="number" id="withdrawalAmountInput" style="padding: 0 20px 0 45px; width: 100%; height: 60px; border-radius: 20px; border: 2px solid #e2e8f0; font-size: 20px; font-weight: 900; color: #1e293b; background: #f8fafc; transition: all 0.2s;" placeholder="0.00" step="0.01" required>
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 12px; font-weight: 700; display: flex; justify-content: space-between; padding: 0 4px;">
                        <span>Available Balance:</span>
                        <span id="maxAvailableDisplay" style="color: #059669; font-weight: 900;">₹0.00</span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="button" style="flex: 1; background: #f1f5f9; color: #64748b; border: none; border-radius: 18px; height: 56px; font-weight: 800; cursor: pointer; font-size: 14px; transition: all 0.2s;" onclick="DeliveryConsole.closeWithdrawalModal()">Cancel</button>
                    <button type="submit" style="flex: 1.8; background: #059669; color: white; border: none; border-radius: 18px; height: 56px; font-weight: 800; cursor: pointer; font-size: 14px; box-shadow: 0 10px 20px rgba(5, 150, 105, 0.2); transition: all 0.2s;">Request Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
include 'layouts/bottom_nav.php';
$extraScripts = '<script src="../assets/js/delivery.js?v=' . time() . '"></script>';
include 'layouts/footer.php'; 
?>
