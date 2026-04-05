<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailHelper {
    public static function sendOrderEmails($orderId) {
        global $pdo;
        
        try {
            // 1. Fetch Order Details
            $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email 
                                  FROM orders o 
                                  JOIN users u ON o.user_id = u.id 
                                  WHERE o.id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) return false;

            // 2. Fetch Order Items
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Prepare Email Content
            $config = require __DIR__ . '/mail_config.php';
            $adminEmail = $config['username']; // Admin email from config

            // Send to Customer
            self::sendEmail(
                $order['customer_email'], 
                "Order Confirmation - " . $order['order_number'], 
                self::getCustomerTemplate($order, $items)
            );

            // Send to Admin
            self::sendEmail(
                $adminEmail, 
                "New Order Received - " . $order['order_number'], 
                self::getAdminTemplate($order, $items)
            );

            return true;
        } catch (Exception $e) {
            error_log("MailHelper Error: " . $e->getMessage());
            return false;
        }
    }

    public static function sendVerificationEmail($userId, $status) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) return false;

            $subject = $status === 'Verified' ? "Congratulations! Your Account is Verified" : "Account Verification Status Update";
            return self::sendEmail(
                $user['email'], 
                $subject, 
                self::getVerificationTemplate($user, $status)
            );
        } catch (Exception $e) {
            error_log("Verification Email Error: " . $e->getMessage());
            return false;
        }
    }

    public static function sendStatusUpdateEmail($orderId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email 
                                  FROM orders o 
                                  JOIN users u ON o.user_id = u.id 
                                  WHERE o.id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) return false;

            return self::sendEmail(
                $order['customer_email'], 
                "Order Status Update - " . $order['order_number'], 
                self::getStatusTemplate($order)
            );
        } catch (Exception $e) {
            error_log("Status Update Email Error: " . $e->getMessage());
            return false;
        }
    }

    private static function sendEmail($to, $subject, $body) {
        $config = require __DIR__ . '/mail_config.php';
        
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = $config['auth'];
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['secure'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            // Recipients
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    private static function getCustomerTemplate($order, $items) {
        $baseUrl = "http://localhost/new_food/"; // Change this to your live domain when deploying
        $itemsHtml = '';
        foreach ($items as $item) {
            $imgUrl = !empty($item['image_url']) ? $baseUrl . str_replace('../', '', $item['image_url']) : $baseUrl . "assets/images/placeholder.png";
            $itemsHtml .= "
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #edf2f7;'>
                    <div style='display: flex; align-items: center;'>
                        <img src='{$imgUrl}' alt='{$item['product_name']}' style='width: 40px; height: 40px; border-radius: 6px; object-fit: cover; margin-right: 12px; border: 1px solid #e2e8f0;'>
                        <span style='color: #4a5568;'>{$item['product_name']}</span>
                    </div>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #edf2f7; color: #4a5568; text-align: center;'>{$item['quantity']}</td>
                <td style='padding: 12px; border-bottom: 1px solid #edf2f7; color: #2d3748; text-align: right; font-weight: 700;'>₹{$item['subtotal']}</td>
            </tr>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <body style='font-family: \"Inter\", -apple-system, blinkmacsystemfont, \"Segoe UI\", roboto, helvetica, arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;'>
                <div style='background: #10b981; padding: 32px; text-align: center; color: #ffffff;'>
                    <h1 style='margin: 0; font-size: 24px; font-weight: 800;'>Village Foods</h1>
                    <p style='margin-top: 8px; opacity: 0.9; font-size: 14px;'>Order Confirmed!</p>
                </div>
                <div style='padding: 32px;'>
                    <p style='color: #4a5568; font-size: 16px;'>Hi <strong>{$order['customer_name']}</strong>,</p>
                    <p style='color: #4a5568; font-size: 16px; line-height: 1.6;'>Thank you for choosing Village Foods! Your order has been successfully placed. We're getting it ready with love and care.</p>
                    
                    <div style='background: #f8fafc; border-radius: 8px; padding: 16px; margin: 24px 0; border: 1px solid #edf2f7;'>
                        <div style='display: flex; justify-content: space-between; margin-bottom: 8px;'>
                            <span style='color: #718096; font-size: 12px; text-transform: uppercase; font-weight: 700;'>Order ID</span>
                            <span style='color: #2d3748; font-weight: 800; font-size: 14px;'>#{$order['order_number']}</span>
                        </div>
                    </div>

                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 24px;'>
                        <thead>
                            <tr style='background: #edf2f7;'>
                                <th style='text-align: left; padding: 12px; color: #718096; font-size: 12px; text-transform: uppercase;'>Item</th>
                                <th style='text-align: center; padding: 12px; color: #718096; font-size: 12px; text-transform: uppercase;'>Qty</th>
                                <th style='text-align: right; padding: 12px; color: #718096; font-size: 12px; text-transform: uppercase;'>Price</th>
                            </tr>
                        </thead>
                        $itemsHtml
                        <tfoot>
                            <tr>
                                <td colspan='2' style='padding: 20px 12px; text-align: right; color: #4a5568;'>Subtotal</td>
                                <td style='padding: 20px 12px; text-align: right; color: #2d3748; font-weight: 700;'>₹{$order['total_amount']}</td>
                            </tr>
                            <tr>
                                <td colspan='2' style='padding: 12px; text-align: right; color: #4a5568;'>Delivery Charge</td>
                                <td style='padding: 12px; text-align: right; color: #4a5568;'>₹{$order['delivery_charge']}</td>
                            </tr>
                            <tr style='background: #f8fafc;'>
                                <td colspan='2' style='padding: 20px 12px; text-align: right; color: #2d3748; font-weight: 800; font-size: 18px;'>Grand Total</td>
                                <td style='padding: 20px 12px; text-align: right; color: #10b981; font-weight: 800; font-size: 20px;'>₹{$order['grand_total']}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div style='padding: 20px; background: #fffaf0; border: 1px solid #feebc8; border-radius: 8px;'>
                        <h4 style='margin: 0 0 8px 0; color: #c05621; font-size: 14px;'>Delivery Information</h4>
                        <p style='margin: 0; color: #7b341e; font-size: 13px; line-height: 1.5;'>{$order['delivery_address']}</p>
                    </div>
                </div>
                <div style='background: #f8fafc; padding: 24px; text-align: center; border-top: 1px solid #edf2f7;'>
                    <p style='margin: 0; color: #718096; font-size: 13px;'>If you have any questions, please reply to this email.</p>
                    <p style='margin-top: 8px; color: #2d3748; font-weight: 700; font-size: 14px;'>Team Village Foods</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private static function getAdminTemplate($order, $items) {
        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= "
            <div style='padding: 12px; border-bottom: 1px solid #edf2f7;'>
                <span style='font-weight:700; color:#2d3748;'>{$item['product_name']}</span> x {$item['quantity']}
                <span style='float:right; font-weight:700;'>₹{$item['subtotal']}</span>
            </div>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <body style='font-family: sans-serif; background: #f8fafc; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;'>
                <div style='background: #1d4ed8; padding: 24px; color: #ffffff; text-align: center;'>
                    <h2 style='margin: 0; font-size: 20px;'>New Order Received</h2>
                    <p style='margin-top: 4px; opacity: 0.9;'>Action required in Admin Panel</p>
                </div>
                <div style='padding: 24px;'>
                    <div style='display:flex; justify-content:space-between; margin-bottom: 20px;'>
                        <div>
                            <p style='color: #718096; font-size: 12px; text-transform: uppercase; margin: 0;'>Order #</p>
                            <p style='font-weight: 800; font-size: 18px; margin: 4px 0;'>{$order['order_number']}</p>
                        </div>
                        <div style='text-align: right;'>
                            <p style='color: #718096; font-size: 12px; text-transform: uppercase; margin: 0;'>Amount</p>
                            <p style='font-weight: 800; font-size: 18px; margin: 4px 0; color: #10b981;'>₹{$order['grand_total']}</p>
                        </div>
                    </div>
                    
                    <div style='background: #f1f5f9; padding: 16px; border-radius: 8px; margin-bottom: 24px;'>
                        <p style='margin: 0; color: #475569; font-size: 14px;'><strong>Customer:</strong> {$order['customer_name']}</p>
                        <p style='margin: 4px 0 0 0; color: #475569; font-size: 14px;'><strong>Contact:</strong> {$order['customer_email']}</p>
                    </div>

                    <h4 style='color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-top: 0;'>Order Items</h4>
                    $itemsHtml
                    
                    <div style='margin-top: 24px;'>
                        <h4 style='color: #1e293b; margin-bottom: 8px;'>Delivery Address</h4>
                        <p style='background: #fffbeb; border: 1px solid #fef3c7; padding: 12px; border-radius: 6px; font-size: 14px; color: #92400e; margin: 0;'>{$order['delivery_address']}</p>
                    </div>

                    <a href='http://localhost/new_food/admin/orders.php' style='display: block; margin-top: 32px; padding: 16px; background: #1d4ed8; color: #ffffff; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px;'>Process Order Now</a>
                </div>
            </div>
        </body>
        </html>";
    }

    private static function getStatusTemplate($order) {
        $statusColors = [
            'Pending' => ['bg' => '#f1f5f9', 'text' => '#475569'],
            'Placed' => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'Confirmed' => ['bg' => '#f0fdf4', 'text' => '#15803d'],
            'Processing' => ['bg' => '#f0f9ff', 'text' => '#0369a1'],
            'Preparing' => ['bg' => '#fdf2f7', 'text' => '#be185d'],
            'Ready' => ['bg' => '#fffbeb', 'text' => '#b45309'],
            'Picked Up' => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'On the Way' => ['bg' => '#fff7ed', 'text' => '#c2410c'],
            'Delivered' => ['bg' => '#f0fdf4', 'text' => '#15803d'],
            'Cancelled' => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
        ];
        $color = (isset($statusColors[$order['status']])) ? $statusColors[$order['status']] : ['bg' => '#f8fafc', 'text' => '#4a5568'];
        
        return "
        <!DOCTYPE html>
        <html>
        <body style='font-family: sans-serif; background: #f8fafc; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>
                <div style='background: #ffffff; padding: 32px; text-align: center; border-bottom: 1px solid #f1f5f9;'>
                    <h2 style='color: #111827; margin: 0; font-size: 22px;'>Order Status Update</h2>
                    <p style='color: #6b7280; font-size: 14px; margin-top: 8px;'>Good news! Your order status has changed.</p>
                </div>
                <div style='padding: 40px; text-align: center;'>
                    <p style='color: #4b5563; font-size: 16px;'>Hi {$order['customer_name']},</p>
                    <p style='color: #4b5563; font-size: 16px;'>The status of your order <strong>#{$order['order_number']}</strong> has been updated to:</p>
                    
                    <div style='display: inline-block; padding: 12px 32px; background: {$color['bg']}; color: {$color['text']}; border-radius: 999px; font-weight: 800; font-size: 20px; margin: 24px 0; text-transform: uppercase; letter-spacing: 1px;'>
                        {$order['status']}
                    </div>

                    <div style='margin-top: 32px; padding: 24px; border-top: 1px solid #f1f5f9;'>
                        <p style='color: #6b7280; font-size: 14px; margin: 0;'>You can track your order live on our website.</p>
                        <a href='http://localhost/new_food/track-order.php?order_id={$order['order_number']}' style='display: inline-block; margin-top: 16px; padding: 12px 24px; background: #10b981; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700;'>Track My Order</a>
                    </div>
                </div>
                <div style='background: #f9fafb; padding: 24px; text-align: center;'>
                    <p style='margin: 0; color: #9ca3af; font-size: 12px;'>Thank you for shopping with Village Foods!</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private static function getVerificationTemplate($user, $status) {
        $isVerified = $status === 'Verified';
        $title = $isVerified ? "Welcome to the Fleet!" : "Account Verification Update";
        $color = $isVerified ? "#10b981" : "#f59e0b";
        $icon = $isVerified ? "check-circle" : "alert-circle";
        
        $message = $isVerified 
            ? "We are thrilled to inform you that your profile has been verified. You can now log in to the Delivery Dashboard and start accepting orders!" 
            : "Thank you for your application. Unfortunately, your profile verification could not be completed at this time. Please log in to your dashboard to see the reason and update your documents.";

        return "
        <!DOCTYPE html>
        <html>
        <body style='font-family: sans-serif; background: #f8fafc; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;'>
                <div style='background: {$color}; padding: 32px; color: #ffffff; text-align: center;'>
                    <h2 style='margin: 0; font-size: 24px;'>{$title}</h2>
                </div>
                <div style='padding: 40px; text-align: center;'>
                    <p style='color: #4b5563; font-size: 16px;'>Hi {$user['name']},</p>
                    <p style='color: #4b5563; font-size: 16px; line-height: 1.6;'>{$message}</p>
                    
                    <a href='http://localhost/new_food/delivery/index.php' style='display: inline-block; margin-top: 32px; padding: 14px 28px; background: {$color}; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700;'>Go to Dashboard</a>
                </div>
                <div style='background: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #edf2f7;'>
                    <p style='margin: 0; color: #9ca3af; font-size: 12px;'>Village Foods Delivery Partner Program</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
?>
