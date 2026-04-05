<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;

header('Content-Type: application/json');

$property_id = '347453878'; 
$credentials_path = __DIR__ . '/../../../analytics-credentials.json';

if (!file_exists($credentials_path)) {
    echo json_encode(['error' => 'Missing analytics-credentials.json file in root directory']);
    exit;
}

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials_path);

try {
    $client = new BetaAnalyticsDataClient();

    // 1. Overview metrics (last 30 days)
    $requestOverview = (new RunReportRequest())
        ->setProperty('properties/' . $property_id)
        ->setDateRanges([new DateRange(['start_date' => '30daysAgo', 'end_date' => 'today'])])
        ->setMetrics([
            new Metric(['name' => 'activeUsers']),
            new Metric(['name' => 'screenPageViews']),
            new Metric(['name' => 'bounceRate']),
        ]);
        
    $response = $client->runReport($requestOverview);

    $overview = [];
    foreach ($response->getRows() as $row) {
        $overview = [
            'users' => $row->getMetricValues()[0]->getValue(),
            'pageViews' => $row->getMetricValues()[1]->getValue(),
            'bounceRate' => round($row->getMetricValues()[2]->getValue() * 100, 2) . '%',
        ];
    }

    // 2. Trend (last 7 days by Date)
    $requestTrend = (new RunReportRequest())
        ->setProperty('properties/' . $property_id)
        ->setDateRanges([new DateRange(['start_date' => '6daysAgo', 'end_date' => 'today'])])
        ->setDimensions([new Dimension(['name' => 'date'])])
        ->setMetrics([new Metric(['name' => 'activeUsers'])]);

    $trendResponse = $client->runReport($requestTrend);

    $dates = [];
    $users = [];
    foreach ($trendResponse->getRows() as $row) {
        $dateStr = $row->getDimensionValues()[0]->getValue();
        $formattedDate = substr($dateStr, 6, 2) . '/' . substr($dateStr, 4, 2);
        $dates[] = $formattedDate;
        $users[] = $row->getMetricValues()[0]->getValue();
    }

    // 3. Devices (Last 30 Days)
    $requestDevices = (new RunReportRequest())
        ->setProperty('properties/' . $property_id)
        ->setDateRanges([new DateRange(['start_date' => '30daysAgo', 'end_date' => 'today'])])
        ->setDimensions([new Dimension(['name' => 'deviceCategory'])])
        ->setMetrics([new Metric(['name' => 'activeUsers'])]);

    $devicesResponse = $client->runReport($requestDevices);
    $devices = [];
    foreach ($devicesResponse->getRows() as $row) {
        $devices[] = [
            'device' => ucfirst($row->getDimensionValues()[0]->getValue()),
            'users' => (int)$row->getMetricValues()[0]->getValue()
        ];
    }

    // 4. Top Pages (Last 30 Days)
    $requestPages = (new RunReportRequest())
        ->setProperty('properties/' . $property_id)
        ->setDateRanges([new DateRange(['start_date' => '30daysAgo', 'end_date' => 'today'])])
        ->setDimensions([new Dimension(['name' => 'pagePath'])])
        ->setMetrics([new Metric(['name' => 'screenPageViews'])]);

    $pagesResponse = $client->runReport($requestPages);
    $topPages = [];
    foreach ($pagesResponse->getRows() as $row) {
        $topPages[] = [
            'path' => $row->getDimensionValues()[0]->getValue(),
            'views' => (int)$row->getMetricValues()[0]->getValue()
        ];
    }
    // Sort array descending by views
    usort($topPages, function($a, $b) { return $b['views'] <=> $a['views']; });
    $topPages = array_slice($topPages, 0, 5);

    $client->close();

    echo json_encode([
        'overview' => $overview,
        'chart' => ['dates' => array_reverse($dates), 'users' => array_reverse($users)],
        'devices' => $devices,
        'topPages' => $topPages
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'GA4 API Error: ' . $e->getMessage()]);
}
?>
