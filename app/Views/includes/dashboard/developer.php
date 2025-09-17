<?php
$CIinstalledVersion = \CodeIgniter\CodeIgniter::CI_VERSION;

// GitHub API endpoint for CodeIgniter repository releases
$api_url = 'https://api.github.com/repos/codeigniter4/framework/releases/latest';

// Initialize cURL session
$curl = curl_init();

// Set cURL options
curl_setopt_array($curl, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
]);

// Execute cURL request
$response = curl_exec($curl);

// Close cURL session
curl_close($curl);

// Decode JSON response
$data = json_decode($response, true);

// Extract latest version
if(isset($data['message']) || !isset($data['tag_name'])) {
    $CIlatestVersion = $CIinstalledVersion;
}
else {
    $CIlatestVersion = preg_replace('/[^0-9.]/', '',$data['tag_name']);
}

// Output the latest version
// echo "Latest CodeIgniter version is: $latest_version";

$result = version_compare($CIinstalledVersion, $CIlatestVersion);

if ($result < 0) {
    // New version available
    $alert = 'danger';
    $updateCI = 'href="' . base_url('update-codeigniter') . '"';
    $updateText = 'update to ';
} else {
    // Installed version is same as current version
    $alert = 'secondary';
    $updateCI = '';
    $updateText = '';
}
$codeigniterText = '<span class="text-bg-warning">&nbsp;CodeIgniter&nbsp;</span>';

$codeigniterVersion = '<a class="text-bg-' . $alert . '" ' . $updateCI . '>&nbsp; ' . $updateText . $CIlatestVersion . ' &nbsp;</a>';

$codeigniterButton = $codeigniterText . $codeigniterVersion;

echo '<small class="text-muted fw-medium ms-1">';
echo $codeigniterButton;
echo '</small>';
?>