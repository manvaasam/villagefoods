<?php
require_once 'includes/db.php';
$pageTitle = 'Track Order — Village Foods';
$extraStyles = <<<CSS
  <link rel="stylesheet" href="assets/css/customer.css">
  <style>
    .tracking-page { max-width: 800px; margin: 0 auto; padding: 40px 24px 100px; font-family: 'Sora', sans-serif; }
    .page-header { margin-bottom: 40px; text-align: center; }
    .page-title { font-size: 32px; font-weight: 800; color: var(--primary-dark); margin-bottom: 8px; letter-spacing: -0.5px; }
    .page-sub { font-size: 15px; color: var(--text-muted); font-weight: 500; }

    /* ETA CARD: PREMIUM GLASSMORPHISM */
    /* ETA CARD: PREMIUM ANIMATED GLASSMORPHISM */
    .live-eta {
      background: linear-gradient(-45deg, #1a1a1a, #1a9c3e, #22c55e, #14532d);
      background-size: 400% 400%;
      animation: gradient-bg 15s ease infinite;
      border-radius: 24px;
      padding: 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 32px;
      color: white;
      box-shadow: 0 20px 40px rgba(26, 156, 62, 0.2);
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,0.1);
    }
    
    @keyframes gradient-bg {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .live-eta::after {
        content: "";
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        animation: shine 4s infinite;
    }
    @keyframes shine {
        0% { left: -100%; }
        20% { left: 100%; }
        100% { left: 100%; }
    }

    .eta-label { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.9; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .eta-label i { animation: pulse-zap 1.5s infinite; color: #fbbf24; }
    @keyframes pulse-zap {
        0% { transform: scale(1); filter: drop-shadow(0 0 0px #fbbf24); }
        50% { transform: scale(1.2); filter: drop-shadow(0 0 8px #fbbf24); }
        100% { transform: scale(1); filter: drop-shadow(0 0 0px #fbbf24); }
    }

    .eta-time { font-size: 48px; font-weight: 800; letter-spacing: -1px; line-height: 1; text-shadow: 0 4px 12px rgba(0,0,0,0.2); }
    .eta-sub { font-size: 14px; opacity: 0.85; margin-top: 12px; font-weight: 500; }
    
    .eta-icon-wrap { 
        width: 80px; height: 80px; 
        background: rgba(255,255,255,0.15); 
        backdrop-filter: blur(12px); 
        border-radius: 22px; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        animation: bike-float 3s infinite ease-in-out;
    }
    .eta-icon-wrap i { animation: bike-ride 0.5s infinite linear; }

    @keyframes bike-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    @keyframes bike-ride {
        0% { transform: rotate(0deg) translateY(0); }
        25% { transform: rotate(2deg) translateY(-1px); }
        75% { transform: rotate(-2deg) translateY(1px); }
        100% { transform: rotate(0deg) translateY(0); }
    }

    /* TRACKING TIMELINE */
    .tracking-card {
      background: white;
      border-radius: 28px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.04);
      border: 1px solid var(--border-light);
    }
    .tracking-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 40px; 
        padding-bottom: 24px; 
        border-bottom: 1px dashed var(--border);
    }
    .tracking-order-id { font-size: 14px; color: var(--text-muted); font-weight: 600; }
    .tracking-status-badge {
      background: #f0fdf4;
      color: #166534;
      padding: 8px 18px;
      border-radius: 50px;
      font-size: 13px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .timeline-container {
        position: relative;
        padding: 20px 0;
        display: flex;
        justify-content: space-between;
    }
    .timeline-line {
        position: absolute;
        top: 38px;
        left: 50px;
        right: 50px;
        height: 4px;
        background: #f3f4f6;
        border-radius: 10px;
        z-index: 1;
    }
    .timeline-progress {
        position: absolute;
        top: 38px;
        left: 50px;
        right: 50px;
        height: 4px;
        background: var(--primary);
        border-radius: 10px;
        z-index: 2;
        transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 0 15px rgba(26, 156, 62, 0.4);
        clip-path: inset(0 calc(100% - var(--track-progress, 0%)) 0 0);
    }

    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100px;
        position: relative;
        z-index: 3;
    }
    .step-node {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: white;
        border: 3px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        transition: all 0.4s ease;
        color: #9ca3af;
    }
    .timeline-step.done .step-node {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(26, 156, 62, 0.3);
    }
    .timeline-step.active .step-node {
        border-color: var(--primary);
        color: var(--primary);
        background: white;
        box-shadow: 0 0 0 6px rgba(26, 156, 62, 0.1);
        animation: pulse-node 2s infinite;
    }
    @keyframes pulse-node {
        0% { box-shadow: 0 0 0 0px rgba(26, 156, 62, 0.2); }
        70% { box-shadow: 0 0 0 15px rgba(26, 156, 62, 0); }
        100% { box-shadow: 0 0 0 0px rgba(26, 156, 62, 0); }
    }
    .step-info { text-align: center; }
    .step-name { font-size: 13px; font-weight: 800; color: #4b5563; margin-bottom: 4px; }
    .timeline-step.done .step-name, .timeline-step.active .step-name { color: var(--primary-dark); }
    .step-desc { font-size: 11px; color: #9ca3af; font-weight: 500; }

    /* DELIVERY INFO CARD */
    .delivery-partner-card {
        margin-top: 40px;
        background: #f9fafb;
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .partner-avatar {
        width: 60px; height: 60px;
        background: var(--primary);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }
    .partner-name { font-size: 16px; font-weight: 800; color: var(--primary-dark); }
    .partner-meta { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
    .partner-actions { display: flex; gap: 10px; }
    .action-btn {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: white;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text);
        transition: all 0.2s;
    }
    .action-btn:hover { background: var(--primary); color: white; border-color: var(--primary); transform: translateY(-2px); }

    /* MOBILE VERTICAL TIMELINE */
    @media (max-width: 600px) {
        .timeline-container { 
            flex-direction: column; 
            gap: 40px; 
            padding: 20px 0 20px 20px; 
            align-items: flex-start;
        }
        .timeline-line { 
            left: 42px; 
            width: 4px; 
            top: 40px; 
            bottom: 40px; 
            right: auto;
            height: auto; 
        }
        .timeline-progress { 
            left: 42px; 
            right: auto;
            width: 4px; 
            top: 40px; 
            height: var(--track-progress, 0%);
            max-height: calc(100% - 80px);
            clip-path: none;
        }
        .timeline-step { 
            flex-direction: row; 
            width: 100%; 
            gap: 24px; 
            align-items: center;
            justify-content: flex-start;
        }
        .step-node { margin-bottom: 0; flex-shrink: 0; }
        .step-info { text-align: left; }
        .live-eta { flex-direction: column; text-align: center; gap: 24px; }
        .eta-time { font-size: 40px; }
    }

    .order-search {
        display: flex;
        gap: 0;
        margin-bottom: 40px;
        background: #f3f4f6;
        padding: 6px;
        border-radius: 18px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
    .order-search:focus-within {
        background: white;
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(26, 156, 62, 0.1);
    }
    .order-search input {
        flex: 1;
        padding: 12px 20px;
        border: none;
        background: transparent;
        font-size: 15px;
        font-weight: 600;
        outline: none;
        color: var(--text);
    }
    .order-search input::placeholder { color: #9ca3af; }
    .order-search button {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0 24px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .order-search button:hover { background: var(--primary-dark); transform: scale(0.98); }

    /* ORDER ITEMS LIST */
    .order-items-list { margin-top: 32px; border-top: 1px dashed var(--border); padding-top: 24px; }
    .item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 14px;
        margin-bottom: 10px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .item-row:hover { background: white; border-color: var(--border); transform: translateX(5px); }
    .item-info { display: flex; align-items: center; gap: 12px; }
    .item-pic { width: 44px; height: 44px; border-radius: 10px; background: #eee; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-light); }
    .item-emoji { font-size: 24px; }
    .item-name { font-size: 14px; font-weight: 700; color: var(--primary-dark); }
    .item-qty { font-size: 12px; color: var(--text-muted); font-weight: 600; }
    .item-price { font-size: 15px; font-weight: 800; color: var(--text); }

    /* HISTORY CARDS */
    .history-title { font-size: 20px; font-weight: 800; color: var(--primary-dark); margin: 60px 0 24px; display: flex; align-items: center; gap: 10px; }
    .history-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
    .history-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        transition: all 0.3s;
    }
    .history-card:hover {
        border-color: var(--primary);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        transform: translateY(-3px);
    }
    .history-icon {
        width: 50px; height: 50px;
        background: #f0fdf4;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }
    .history-info { flex: 1; }
    .history-id { font-size: 14px; font-weight: 800; color: var(--primary-dark); }
    .history-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .history-amount { font-size: 15px; font-weight: 800; color: var(--text); text-align: right; }

    /* MOBILE ADJUSTMENTS */
    @media (max-width: 600px) {
        .order-search { border-radius: 14px; }
        .order-search input { padding: 10px 15px; }
        .history-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .page-title { font-size: 24px; }
        .live-eta { padding: 24px; }
        .eta-time { font-size: 32px; }
        .delivery-partner-card { flex-direction: column; text-align: center; gap: 16px; padding: 20px; }
        .delivery-partner { flex-direction: column; text-align: center; }
        .partner-actions { justify-content: center; width: 100%; }
        .history-card { flex-direction: column; text-align: center; }
        .history-amount { text-align: center; width: 100%; margin-top: 10px; border-top: 1px dashed var(--border); padding-top: 10px; }
    }
  </style>
CSS;
include 'includes/header.php';
include 'includes/navbar.php';

$order_id = $_GET['order_id'] ?? $_GET['id'] ?? '';
$clean_id = strtoupper(trim($order_id));

$order = null;
$orderItems = [];

if ($clean_id) {
    // 1. Try exact match, then match with # prefix, then numeric ID
    $stmt = $pdo->prepare("SELECT o.*, s.shop_name 
                           FROM orders o 
                           LEFT JOIN shops s ON o.shop_id = s.id 
                           WHERE o.order_number = ? OR o.order_number = ? OR o.id = ?");
    $stmt->execute([$clean_id, '#' . $clean_id, is_numeric($clean_id) ? $clean_id : 0]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.image_url 
                               FROM order_items oi 
                               LEFT JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
        $stmt->execute([$order['id']]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch user history
$user_id = $_SESSION['user_id'] ?? 0;
$history = [];
if ($user_id) {
    $historyStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $historyStmt->execute([$user_id]);
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="tracking-page">
  <div class="page-header">
    <div class="page-title"><i data-lucide="package" style="vertical-align:middle;margin-right:8px;color:var(--primary)"></i> Track Your Order</div>
    <div class="page-sub">Get real-time updates on your delivery</div>
  </div>

  <div class="order-search">
    <input type="text" id="orderIdInput" placeholder="Enter Order ID (e.g. VF-1001)" value="<?= htmlspecialchars($order_id) ?>">
    <button onclick="trackOrder()"><i data-lucide="search" style="width:16px;height:16px;vertical-align:middle;margin-right:6px"></i> Track</button>
  </div>

  <?php if ($order_id && !$order): ?>
    <div class="checkout-card" style="background:white; border-radius:var(--radius); padding:48px; text-align:center; color:var(--text-muted)">
        <i data-lucide="search-x" style="width:48px; height:48px; margin-bottom:16px; opacity:0.5"></i>
        <h3 style="font-size:18px; font-weight:800; color:var(--primary-dark)">Order Not Found</h3>
        <p>We couldn't find any order with ID: <?= htmlspecialchars($order_id) ?></p>
    </div>
  <?php elseif ($order): ?>
    <!-- LIVE ETA -->
    <div class="live-eta">
        <?php if ($order['status'] === 'Delivered'): ?>
            <div>
                <div class="eta-label"><i data-lucide="check-circle" style="width:16px;height:16px"></i> Order Delivered</div>
                <div class="eta-time">Success!</div>
                <div class="eta-sub">Your meal has been delivered. Enjoy!</div>
                <?php if ($order['picked_up_at'] && $order['delivered_at']): 
                    $pTime = strtotime($order['picked_up_at']);
                    $dTime = strtotime($order['delivered_at']);
                    $diff = $dTime - $pTime;
                    $mins = floor($diff / 60);
                    $secs = $diff % 60;
                ?>
                    <div class="duration-badge" style="background:rgba(255,255,255,0.2); backdrop-filter:blur(5px); padding:6px 12px; border-radius:50px; font-size:11px; font-weight:800; display:inline-flex; align-items:center; gap:6px; margin-top:12px;">
                        <i data-lucide="timer" style="width:12px;height:12px"></i> Total Delivery: <?= $mins ?>m <?= $secs ?>s
                    </div>
                <?php endif; ?>
            </div>
            <div class="eta-icon-wrap"><i data-lucide="home" style="width:40px;height:40px"></i></div>
        <?php else: ?>
            <div>
                <div class="eta-label"><i data-lucide="zap" style="width:16px;height:16px"></i> Estimated Arrival</div>
                <div class="eta-time" id="etaTime">25 mins</div>
                <div class="eta-sub">Our partner is moving at top speed for you</div>
                
                <?php if ($order['picked_up_at'] && $order['status'] === 'On the Way'): ?>
                    <div class="duration-badge" style="background:white; color:var(--primary); padding:6px 12px; border-radius:50px; font-size:11px; font-weight:800; display:inline-flex; align-items:center; gap:6px; margin-top:12px;">
                        <i data-lucide="timer" style="width:12px;height:12px"></i> On Way: <span class="live-duration-timer" data-start="<?= $order['picked_up_at'] ?>">0m 0s</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="eta-icon-wrap"><i data-lucide="bike" style="width:40px;height:40px"></i></div>
        <?php endif; ?>
    </div>

    <!-- TRACKING CARD -->
    <div class="tracking-card">
      <div class="tracking-header">
        <div>
          <div class="tracking-order-id">Order <?= $order['order_number'] ?: $order['id'] ?> · <?= date('h:i A', strtotime($order['created_at'])) ?></div>
          <div style="font-size:16px;font-weight:800;margin-top:4px"><?= count($orderItems) ?> items · ₹<?= number_format($order['grand_total'], 2) ?></div>
        </div>
        <div class="tracking-status-badge" style="<?= $order['status'] === 'Cancelled' ? 'background:#fef2f2; color:#b91c1c;' : '' ?>">
            <i data-lucide="<?= $order['status'] === 'Cancelled' ? 'x-circle' : 'shield-check' ?>" style="width:16px;height:16px"></i>
            <?= ($order['status'] === 'Cancelled' && $order['payment_status'] === 'Refund Pending') ? 'Refund Pending' : $order['status'] ?>
        </div>
      </div>

      <?php
        // Calculate steps FIRST so --track-progress CSS variable is set correctly
        $allSteps = [
            ['id' => 'Confirmed', 'icon' => 'check-circle', 'desc' => 'Order verified'],
            ['id' => 'Ready', 'icon' => 'bell', 'desc' => 'Ready for pickup'],
            ['id' => 'Picked Up', 'icon' => 'package', 'desc' => 'Picked from shop'],
            ['id' => 'On the Way', 'icon' => 'bike', 'desc' => 'En route to you'],
            ['id' => 'Delivered', 'icon' => 'award', 'desc' => 'Enjoy your meal']
        ];
        
        $currentStatus = $order['status'];
        
        // Map actual DB status -> which timeline step is "Active" (In Progress)
        if ($currentStatus === 'Placed') {
            $mappedStatus = 'Confirmed';
        } elseif (in_array($currentStatus, ['Confirmed', 'Accepted', 'Accepted by Shop', 'Processing', 'Preparing'])) {
            // Once confirmed/accepted, the vendor is PREPARING the order.
            // So the active visual step should be "Ready" (Preparing & Packing).
            $mappedStatus = 'Ready';
        } elseif (in_array($currentStatus, ['Ready', 'Ready for Pickup'])) {
            // Once ready, the order is waiting for pickup.
            // Visually, we are now working towards "Picked Up".
            $mappedStatus = 'Picked Up';
        } elseif (in_array($currentStatus, ['Picked Up', 'Picked'])) {
            $mappedStatus = 'On the Way';
        } elseif (in_array($currentStatus, ['On the Way', 'Delivering', 'Arrived'])) {
            $mappedStatus = 'On the Way';
        } elseif ($currentStatus === 'Delivered') {
            $mappedStatus = 'Delivered';
        } else {
            $mappedStatus = 'Confirmed';
        }

        $currentIndex = array_search($mappedStatus, array_column($allSteps, 'id'));
        if ($currentIndex === false) $currentIndex = 0;
        
        // Progress percentage for the line
        $lineWidth = ($currentIndex / (count($allSteps) - 1)) * 100;
      ?>

      <?php if ($order['status'] === 'Cancelled' && $order['payment_status'] === 'Refund Pending'): ?>
      <div style="padding: 16px; background: #fff7ed; border-radius: 16px; margin-bottom: 32px; border: 1px solid #ffedd5; display: flex; align-items: center; gap: 12px; animation: slide-in 0.5s ease-out;">
        <i data-lucide="refresh-ccw" style="color: #c2410c; animation: spin 4s linear infinite;"></i>
        <div style="font-size: 13px; color: #9a3412; font-weight: 600;">Your refund is being processed. The amount will be credited back to your account within 5-7 working days.</div>
      </div>
      <?php elseif (in_array($order['status'], ['Pending', 'Placed'])): ?>
      <div style="padding: 16px; background: #fff1f2; border-radius: 16px; margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #fee2e2;">
        <div style="font-size: 13px; color: #991b1b; font-weight: 600;">Need to change something?</div>
        <button onclick="confirmCancel(<?= $order['id'] ?>)" class="delivery-chat-btn" style="background: #991b1b; color: white; border: none; padding: 10px 20px; font-size: 12px; border-radius: 12px; font-weight: 800; cursor: pointer;">Cancel Order</button>
      </div>
      <?php elseif ($order['status'] !== 'Cancelled' && $order['status'] !== 'Delivered'): ?>
      <div style="padding: 12px 16px; background: #f9fafb; border-radius: 12px; margin-bottom: 32px; display: flex; align-items: center; gap: 8px; border: 1px solid var(--border-light);">
        <i data-lucide="info" style="width:14px; height:14px; color:var(--text-muted)"></i>
        <div style="font-size: 11px; color: var(--text-muted); font-weight: 500;">This order is already being prepared and cannot be cancelled online. Please contact support for any changes.</div>
      </div>
      <?php endif; ?>

      <div class="timeline-container" style="--track-progress: <?= $lineWidth ?>%">
        
        <div class="timeline-line"></div>
        <div class="timeline-progress"></div>
        
        <?php foreach ($allSteps as $index => $step):
            $isDone = $index < $currentIndex;
            $isActive = $index === $currentIndex;
            $isPending = $index > $currentIndex;
        ?>
            <div class="timeline-step <?= $isDone ? 'done' : '' ?> <?= $isActive ? 'active' : '' ?>">
                <div class="step-node">
                    <i data-lucide="<?= $step['icon'] ?>" style="width:20px;height:20px"></i>
                </div>
                <div class="step-info">
                    <div class="step-name"><?= $step['id'] ?></div>
                    <div class="step-desc">
                        <?php 
                        if ($isDone) echo 'Completed';
                        elseif ($isActive) {
                            if ($currentStatus === 'Delivered') echo 'Success!';
                            else echo 'In Progress';
                        } else {
                            echo $step['desc'];
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
      </div>

      <!-- DELIVERY PARTNER CARD -->
      <div class="delivery-partner-card">
        <div class="delivery-partner">
          <div class="partner-avatar"><i data-lucide="user" style="width:30px;height:30px"></i></div>
          <?php if($order['delivery_boy_id']): 
             $pst = $pdo->prepare("SELECT name, phone FROM users WHERE id = ?");
             $pst->execute([$order['delivery_boy_id']]);
             $dboy = $pst->fetch();
             $dbPhone = $dboy['phone'] ?? '';
          ?>
            <div>
                <div class="partner-name"><?= $dboy['name'] ?></div>
                <div class="partner-meta">Professional Delivery Partner · 4.9 <i data-lucide="star" style="width:12px;height:12px;display:inline;fill:#fbbf24;color:#fbbf24"></i></div>
            </div>
          <?php else: ?>
            <div>
                <div class="partner-name">Finding Captain...</div>
                <div class="partner-meta">Assigning the best partner for you</div>
            </div>
          <?php endif; ?>
        </div>
        <div class="partner-actions">
          <?php if(!empty($dbPhone)): ?>
            <a href="tel:<?= $dbPhone ?>" class="action-btn"><i data-lucide="phone" style="width:18px;height:18px"></i></a>
            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $dbPhone) ?>" class="action-btn"><i data-lucide="message-circle" style="width:18px;height:18px"></i></a>
          <?php else: ?>
            <button class="action-btn" disabled><i data-lucide="phone" style="width:18px;height:18px;opacity:0.3"></i></button>
          <?php endif; ?>
        </div>
      </div>

      <!-- ORDER ITEMS -->
      <div class="order-items-list">
        <div style="font-size:14px; font-weight:800; margin-bottom:16px; color:var(--primary-dark); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px">
            <div style="display:flex; align-items:center; gap:8px">
                <i data-lucide="shopping-cart" style="width:16px;height:16px"></i> Your Items
            </div>
            <?php if (!empty($order['shop_name'])): ?>
                <div style="font-size:12px; color:var(--primary); background:#f0fdf4; padding:6px 14px; border-radius:50px; font-weight:800; border:1px solid #dcfce7; display:flex; align-items:center; gap:6px">
                    <i data-lucide="store" style="width:14px;height:14px"></i>
                    From: <?= htmlspecialchars($order['shop_name']) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php foreach ($orderItems as $item): ?>
            <div class="item-row">
                <div class="item-info">
                    <div class="item-pic">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Product" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span class="item-emoji">🍱</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div class="item-qty">Quantity: <?= $item['quantity'] ?></div>
                    </div>
                </div>
                <div class="item-price">₹<?= number_format($item['subtotal'], 2) ?></div>
            </div>
        <?php endforeach; ?>

        <div class="payment-summary" style="background:#f9fafb; border-radius:16px; padding:20px; margin-top:20px;">
          <div class="cart-row" style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px"><span>Items Total</span><span style="font-weight:700">₹<?= number_format($order['total_amount'], 2) ?></span></div>
          <div class="cart-row" style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px"><span>Delivery Fee</span><span style="font-weight:700">₹<?= number_format($order['delivery_charge'], 2) ?></span></div>
          <div class="cart-row" style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px"><span>Platform Fee</span><span style="font-weight:700">₹<?= number_format($order['platform_fee'], 2) ?></span></div>
          <div class="cart-row" style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:8px"><span>Handling Fee</span><span style="font-weight:700">₹<?= number_format($order['handling_fee'], 2) ?></span></div>
          <div class="cart-row total" style="display:flex; justify-content:space-between; font-size:16px; font-weight:800; color:var(--primary-dark); margin-top:12px; padding-top:12px; border-top:1px dashed var(--border)"><span>Grand Total</span><span>₹<?= number_format($order['grand_total'], 2) ?></span></div>
          <div style="font-size:11px; color:#6b7280; text-align:right; margin-top:10px; font-weight:600">Paid via <?= strtoupper($order['payment_method']) ?> · <i data-lucide="shield-check" style="width:12px;height:12px;display:inline"></i> Verified</div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <!-- Initial State if no ID -->
    <div class="checkout-card" style="background:white; border-radius:var(--radius); padding:48px; text-align:center; color:var(--text-muted)">
        <i data-lucide="package" style="width:48px; height:48px; margin-bottom:16px; opacity:0.3"></i>
        <h3 style="font-size:18px; font-weight:800; color:var(--primary-dark)">Enter Order ID</h3>
        <p>Please enter your Order ID above to see real-time status.</p>
    </div>
  <?php endif; ?>

  <!-- ORDER HISTORY -->
  <?php if (!empty($history)): ?>
  <div class="order-history">
    <div class="history-title"><i data-lucide="clock" style="width:24px;height:24px;color:var(--primary)"></i> Recent Orders</div>
    <div class="history-grid">
        <?php foreach ($history as $h): ?>
            <a href="track-order.php?order_id=<?= urlencode($h['order_number'] ?: $h['id']) ?>" class="history-card">
              <div class="history-icon"><i data-lucide="package"></i></div>
              <div class="history-info">
                <div class="history-id"><?= $h['order_number'] ?: '#'.$h['id'] ?></div>
                <div class="history-meta"><?= date('d M Y', strtotime($h['created_at'])) ?> · <?= $h['status'] ?></div>
              </div>
              <div class="history-amount">₹<?= number_format($h['grand_total'], 2) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</main>

<script>
  function trackOrder() {
    const id = document.getElementById('orderIdInput').value.trim();
    if (!id) { Toast.show('Please enter an order ID', 'error'); return; }
    window.location.href = 'track-order.php?order_id=' + encodeURIComponent(id);
  }

  // Countdown timer for dummy ETA
  const etaEl = document.getElementById('etaTime');
  if (etaEl) {
    let eta = parseInt(etaEl.textContent) || 28;
    setInterval(() => {
        if (eta > 1) {
        eta--;
        etaEl.textContent = `${eta} min`;
        }
    }, 60000);
  }
  function confirmCancel(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = 'Cancelling...';

        fetch('api/orders/cancel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Toast.show('Order cancelled successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                Toast.show(data.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Cancel Order';
            }
        })
        .catch(err => {
            Toast.show('Failed to cancel order', 'error');
            btn.disabled = false;
            btn.textContent = 'Cancel Order';
        });
    }
  }
  // Live Duration Timer
  function updateLiveDuration() {
    const timers = document.querySelectorAll('.live-duration-timer');
    timers.forEach(el => {
        const startStr = el.getAttribute('data-start');
        if (!startStr) return;
        
        const start = new Date(startStr).getTime();
        const now = new Date().getTime();
        const diff = Math.floor((now - start) / 1000);
        
        if (diff < 0) return;
        
        const m = Math.floor(diff / 60);
        const s = diff % 60;
        el.textContent = `${m}m ${s}s`;
    });
  }
  setInterval(updateLiveDuration, 1000);
  updateLiveDuration();

<?php if ($order && $order['status'] !== 'Delivered' && $order['status'] !== 'Cancelled'): ?>
  // Live Status Polling — auto-refresh if delivery boy updates status
  const currentOrderStatus = "<?= addslashes($order['status']) ?>";
  const trackOrderId = "<?= addslashes($order['order_number'] ?: $order['id']) ?>";
  
  setInterval(async () => {
    try {
      const res = await fetch(`api/orders/get_status.php?order_id=${encodeURIComponent(trackOrderId)}`);
      const data = await res.json();
      if (data.success && data.status !== currentOrderStatus) {
        // Status changed! Reload the page to show updated timeline
        location.reload();
      }
    } catch (e) { /* silent fail */ }
  }, 15000); // Poll every 15 seconds
<?php endif; ?>
</script>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
