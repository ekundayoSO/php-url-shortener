<?php
include 'db.php';

 #  Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  # URL to the Unelma.IO API
  $url = 'https://unelma.io/api/v1/link';

  # Load access token from .env
  require_once dirname(__FILE__) . "/vendor/autoload.php";
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  # Access token for the Unelma.IO API
  $accessToken = $_ENV['UNELMA_ACCESS_TOKEN'];

  # var_dump($accessToken);
  
  if (!$accessToken) {
    die('Access token not found in .env file');
  }

  # Collect the long URL from the form input
  $longUrl = $_POST['longUrl'];

  # Prepare the data to be sent in the POST request
  $data = [
    "type" => "direct",
    "password" => null,
    "active" => true,
    "expires_at" => "2024-05-06",
    "activates_at" => "2024-04-20",
    "utm" => "utm_source=google&utm_medium=banner",
    "domain_id" => null,
    "long_url" => $longUrl
  ];

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken,
  ]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

  # Execute the POST request
  $response = curl_exec($ch);

  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
  } else {
    # Decode the response
    $responseDecoded = json_decode($response, true);

    if (isset($responseDecoded['link']) && isset($responseDecoded['link']['short_url'])) {

      # Inserting data into table
      $sql = "INSERT INTO shortened_urls (long_url, short_url) VALUES (?, ?)";
      $stmt = $connection->prepare($sql);
      $stmt->bind_param("ss", $longUrl, $responseDecoded['link']['short_url']);
      $stmt->execute();
      $stmt->close();
    } else {
      echo 'The key "short_url" does not exist in the response.';

      # Check for error status in the response
      if (isset($responseDecoded['status']) && $responseDecoded['status'] == 'error') {
        $error_message = isset($responseDecoded['error']) ? $responseDecoded['error'] : 'Unknown error occurred';
        echo "<p>Error: $error_message</p>";
      }
    }
  }

  # Close cURL session
  curl_close($ch);
}

# Retrieve all URLs from the database
$query = "SELECT * FROM shortened_urls";
$result = mysqli_query($connection, $query);

if (!$result) {
  die("Query failed" . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="url.css">
  <title>URL Shortener</title>
</head>

<body>
  <form method="post">
    <label for="longUrl">Enter URL to shorten:</label>
    <input type="text" id="longUrl" name="longUrl" required>
    <button type="submit">Shorten URL</button>
  </form>
  <div class="tab">
    <?php
    # Check if $result contains any rows
    if (mysqli_num_rows($result) > 0) {
    ?>
      <table>
        <tr>
          <th>ID</th>
          <th>Long URL</th>
          <th>Short URL</th>
        </tr>
        <?php
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <!--<td><?php echo $row['long_url']; ?></td>-->
            <td><a id="a-long" target='_blank' href='<?php echo $row['long_url']; ?>'><?php echo $row['long_url']; ?></a></td>
            <td><a target='_blank' href='<?php echo $row['short_url']; ?>'><?php echo $row['short_url']; ?></a></td>
          </tr>
        <?php
        }
        ?>
      </table>
    <?php
    } else {
      echo "<p>No URLs found in the database.</p>";
    }
    ?>
  </div>
</body>

</html>
