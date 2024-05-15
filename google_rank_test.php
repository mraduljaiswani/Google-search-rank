<?php

function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$websiteUrl = $keyword = '';
$search_result_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $websiteUrl = sanitize_input($_POST['websiteUrl']);
    $keyword = sanitize_input($_POST['keyword']);

    $apiUrl = "https://www.googleapis.com/customsearch/v1";
    
    $cseId = '462ac824a7d514baa';
    $apiKey = 'AIzaSyDHa2sSG99m-MkyS78aoDXY-oEnWSD4NTk';

    $allResults = [];
    $start = 1;
    $resultsPerPage = 10;

    while (true) {
        $queryParams = http_build_query([
            'q' => $keyword,
            'cx' => $cseId,
            'key' => $apiKey,
            'start' => $start,
            'num' => $resultsPerPage
        ]);

        $finalUrl = "$apiUrl?$queryParams";

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $finalUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response, true);

        if (isset($data['items'])) {
            $allResults = array_merge($allResults, $data['items']);
            $totalResults = isset($data['searchInformation']['totalResults']) ? intval($data['searchInformation']['totalResults']) : 0;

            if ($start + $resultsPerPage > $totalResults) {
                break;
            }

            $start += $resultsPerPage;
        } else {
            break;
        }
    }

    if (!empty($allResults)) {
        $rank = 1;
        foreach ($allResults as $item) {
            echo "Title: " . $item['title'] . "<br>";
            echo "Link: " . $item['link'] . "<br>";
            echo "Snippet: " . $item['snippet'] . "<br>";
            echo "<hr>";

            if (strpos($item['link'], $websiteUrl) !== false) {
                $search_result_msg = "Your website is ranked at position $rank in Google search results for keyword \"$keyword\".";
                break;
            }
            $rank++;
        }

        if (!$search_result_msg) {
            $search_result_msg = "Your website is not found in the Google search results for keyword \"$keyword\".";
        }
    } else {
        $search_result_msg = "No search results found for keyword \"$keyword\".";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Search</title>
</head>
<body>

    <h2>Google Search</h2>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        Website URL: <input type="text" name="websiteUrl" value="<?php echo htmlspecialchars($websiteUrl); ?>"><br><br>
        Keyword: <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>"><br><br>
        <input type="submit" name="submit" value="Search">
    </form>

    <?php
    echo $search_result_msg;
    ?>

</body>
</html>
