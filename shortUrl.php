<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['longUrl'])) {
    
    $longUrl = $_POST['longUrl'];

    if (filter_var($longUrl, FILTER_VALIDATE_URL)) {
      require_once dirname(__FILE__) . "/vendor/autoload.php";
      $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
      $dotenv->load();

      $accessToken = $_ENV['UNELMA_ACCESS_TOKEN'];

      if (!$accessToken) {
        die('Access token not found in .env file');
      }

      $url = 'https://unelma.io/api/v1/link';

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

      $response = curl_exec($ch);

      if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
      } else {
        $responseDecoded = json_decode($response, true);

        if (isset($responseDecoded['link']) && isset($responseDecoded['link']['short_url'])) {
          $shortUrl = $responseDecoded['link']['short_url'];

          $sql = "INSERT INTO shortened_urls (long_url, short_url) VALUES (?, ?)";
          $stmt = $connection->prepare($sql);
          $stmt->bind_param("ss", $longUrl, $shortUrl);
          $stmt->execute();
          $stmt->close();
        } else {
          echo "Short URL not found in the response.";
        }
      }

      curl_close($ch);
    } else {
      echo "Invalid URL entered.";
    }
  } else {
    echo "URL not provided.";
  }
}

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
            <td><a id="a-long" target='_blank' href='<?php echo htmlspecialchars($row['long_url']); ?>'><?php echo htmlspecialchars($row['long_url']); ?></a></td>
            <td><a target='_blank' href='<?php echo htmlspecialchars($row['short_url']); ?>'><?php echo htmlspecialchars($row['short_url']); ?></a></td>
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