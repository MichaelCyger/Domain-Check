<?php

// Get the credentials to run this script
require_once 'config.php';

// Access setting
$accessKey = ACCESS_KEY;

// GoDaddy API credentials
$apiKey = API_KEY;
$apiSecret = API_SECRET;
$shopperId = SHOPPER_ID;

// Email settings
$recipientName = RECIPIENT_NAME;
$recipientEmail = RECIPIENT_EMAIL;
$senderEmail = SENDER_EMAIL;
$codeLocation = CODE_LOCATION;

// Only permit access to /check/domains.php?pwd=accessKey
if (isset($_GET['pwd']) && $_GET['pwd'] === $accessKey) {
    // Continue
} else {
    // Redirect the user
    header("Location: /");
    exit();
}

// Hook into Wordpress to use wp_mail()
if (file_exists('../wp-load.php')) {
    require_once '../wp-load.php';
    echo "Wordpress: wp-load.php file found and loaded.<br><br>";
} else {
    echo "Error: wp-load.php file not found.<br><br>";
    exit;
}

// Check if wp_mail() function exists
if (function_exists('wp_mail')) {
    echo "WP Mail exists.<br><br>";
} else {
    echo "WP Mail is not installed or activated.<br><br>";
    exit;
}

// GoDaddy API endpoint
$apiEndpoint = 'https://api.godaddy.com/v1/domains?statuses=ACTIVE&statusGroups=VISIBLE&limit=1000&includes=';

// Local file path
$localFilePath = 'domain_list.txt';

// Connect to GoDaddy API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'X-Shopper-Id: ' . $shopperId,
    'Authorization: sso-key ' . $apiKey . ':' . $apiSecret
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    $error = curl_error($ch);
    echo "cURL Error: " . $error . "<br><br>";
    $failureMessage = "cURL Error: " . $error;
    sendFailureEmail($recipientName, $recipientEmail, $senderEmail, $codeLocation, $failureMessage);
    exit;
}

// Check for HTTP errors
if ($httpCode != 200) {
    echo "HTTP Error: " . $httpCode . "<br><br>";
    echo "Response: " . $response . "<br><br>";
    $failureMessage = "HTTP Error: " . $httpCode . "\nResponse: " . $response;
    sendFailureEmail($recipientName, $recipientEmail, $senderEmail, $codeLocation, $failureMessage);
    exit;
}

curl_close($ch);

// Connected to GoDaddy API successfully
echo "Connected to GoDaddy API successfully.<br><br>";

// Retrieve domain names from API response
$domains = [];
if ($response) {
    $data = json_decode($response, true);
    foreach ($data as $domain) {
        $domains[] = $domain['domain'];
    }
}

// Check if domain names were retrieved successfully
if (!empty($domains)) {
    echo "Retrieved a list of domain names in your account.<br><br>";
} else {
    echo "Failed to retrieve domain names.<br><br>";
    $failureMessage = "Failed to retrieve domain names.";
    sendFailureEmail($recipientName, $recipientEmail, $senderEmail, $failureMessage);
    exit;
}

// Get total count of domain names
$totalCount = count($domains);
echo "Total number of active domain names in account: " . number_format($totalCount) . "<br><br>";

// Check if local file exists
if (file_exists($localFilePath)) {
    echo "Local file exists so updating it.<br><br>";

    // Read previous domain list and count from local file
	$previousData = file_get_contents($localFilePath);
	$previousData = explode("\n", $previousData);
	$previousDomains = array_slice($previousData, 0, -1);
	$previousCount = end($previousData);
	
	// Compare previous and current domain lists
	$addedDomains = array_diff($domains, $previousDomains);
	$removedDomains = array_diff($previousDomains, $domains);
	
	// Compare previous and current domain counts
	$totalCountDifference = $totalCount - $previousCount;
	echo "The difference in active domain names in your account since last run: " . number_format($totalCountDifference) . "<br><br>";
	
	// Write current domain list and count to local file
	file_put_contents($localFilePath, implode("\n", $domains) . "\n" . $totalCount);
	if (file_put_contents($localFilePath, implode("\n", $domains) . "\n" . $totalCount) !== false) {
		echo "File created and updated successfully.<br><br>";
	} else {
		echo "Failed to create or update the file.<br><br>";
	}

    // Send email if there is a discrepancy
    $subject = 'Daily Domain Check: ' . date('m/d/Y');
    $body = "Hi " . RECIPIENT_NAME . ",\n\nToday's domain checks for you.\n\n";
    $body .= "Registrar: GoDaddy\n";
    $body .= "Domain Count (Active): " . number_format($totalCount) . "\n\n";
    $body .= "The difference in active domain names in your account since last run: " . number_format($totalCountDifference) . "\n\n";

    if (empty($previousDomains)) {
        $body .= "We created a local file and will compare your domains to it the next time we run.\n\n";
    } else {
        if (!empty($addedDomains)) {
            $body .= "The following domains were added to your account:\n";
            $body .= implode("\n", $addedDomains) . "\n\n";
        } else {
            $body .= "No domains were added to your account.\n\n";
        }

        if (!empty($removedDomains)) {
            $body .= "The following domains were removed from your account:\n";
            $body .= implode("\n", $removedDomains) . "\n\n";
        } else {
            $body .= "No domains were removed from your account.\n\n";
        }
    }

    $body .= "Sincerely,\nYour Trusty Checker\n($codeLocation)";

    $headers = array(
        'From: ' . $senderEmail,
        'Content-Type: text/plain; charset=UTF-8'
    );

    // Attach domain_list.txt file to the email
    $attachments = array($localFilePath);

    if (function_exists('wp_mail')) {
        if (wp_mail($recipientEmail, $subject, $body, $headers, $attachments)) {
            echo "Email update sent successfully to " . $recipientEmail . ".<br><br>";
        } else {
            echo "Failed to send email update.<br><br>";
        }
    } else {
        echo "WP Mail SMTP plugin is not installed or activated.<br><br>";
    }
} else {
    echo "Local file does not exist so writing it.<br><br>";

    // Write current domain list and count to local file
    file_put_contents($localFilePath, implode("\n", $domains) . "\n" . $totalCount);
    if (file_put_contents($localFilePath, implode("\n", $domains) . "\n" . $totalCount) !== false) {
        echo "File created and updated successfully.<br><br>";
    } else {
        echo "Failed to create or update the file.<br><br>";
    }

    $subject = 'Daily Domain Check: ' . date('m/d/Y');
    $body = "Hi " . $recipientName . ",\n\nToday's domain checks for you.\n\n";
    $body .= "Registrar: GoDaddy\n";
    $body .= "Domain Count (Active): " . number_format($totalCount) . "\n\n";
    $body .= "We created a local file and will compare your domains to it the next time we run.\n\n";
    $body .= "Sincerely,\nYour Trusty Checker\n($codeLocation)";

    $headers = array(
        'From: ' . $senderEmail,
        'Content-Type: text/plain; charset=UTF-8'
    );

    // Attach domain_list.txt file to the email
    $attachments = array($localFilePath);

    if (function_exists('wp_mail')) {
        if (wp_mail($recipientEmail, $subject, $body, $headers, $attachments)) {
            echo "Email update sent successfully to " . $recipientEmail . ".<br><br>";
        } else {
            echo "Failed to send email update.<br><br>";
        }
    } else {
        echo "WP Mail plugin is not installed or activated.<br><br>";
    }
}

// Function to send failure email
function sendFailureEmail($recipientName, $recipientEmail, $senderEmail, $codeLocation, $failureMessage) {
    $subject = 'Domain Check Failed: ' . date('m/d/Y');
    $body = "Hi " . $recipientName . ",\n\nThere was a failure in the domain check script.\n\n";
    $body .= "Failure Details:\n";
    $body .= $failureMessage . "\n\n";
    $body .= "Please check the script and resolve the issue.\n\n";
    $body .= "Sincerely,\nYour Trusty Checker\n($codeLocation)";

    $headers = array(
        'From: ' . $senderEmail,
        'Content-Type: text/plain; charset=UTF-8'
    );

    if (function_exists('wp_mail')) {
        if (wp_mail($recipientEmail, $subject, $body, $headers)) {
            echo "Failure email sent successfully to " . $recipientEmail . ".<br><br>";
        } else {
            echo "Failed to send failure email.<br><br>";
        }
    }
}
