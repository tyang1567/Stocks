<?php
require 'vendor/autoload.php'; 

use MongoDB\Client as MongoClient;

$client = new MongoClient('mongodb://localhost:27017');
$collection = $client->stock_data->most_active_stocks;

function fetch_data() {
    global $collection;
    return $collection->find()->toArray();
}

function insert_or_update_stock($stock) {
    global $collection;

    $existing_stock = $collection->findOne(['Symbol' => $stock['Symbol']]);

    if ($existing_stock) {
        $collection->updateOne(['_id' => $existing_stock['_id']], ['$set' => $stock]);
    } else {
        $collection->insertOne($stock);
    }
}

$stocks_data = fetch_data();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Most Active Stocks</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            cursor: pointer; 
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Most Active Stocks</h2>

<table id="stock-table">
    <thead>
    <tr>
        <th onclick="sortTable(0)">Index</th>
        <th onclick="sortTable(1)">Symbol</th>
        <th onclick="sortTable(2)">Name</th>
        <th onclick="sortTable(3)">Price</th>
        <th onclick="sortTable(4)">Change</th>
        <th onclick="sortTable(5)">Volume</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($stocks_data as $index => $stock): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo $stock['Symbol']; ?></td>
            <td><?php echo $stock['Name']; ?></td>
            <td><?php echo $stock['Price']; ?></td>
            <td><?php echo $stock['Change']; ?></td>
            <td><?php echo $stock['Volume']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    function sortTable(colIndex) {
        var table = document.getElementById("stock-table");
        var rows = table.rows;
        var switching = true;

        while (switching) {
            switching = false;
            for (var i = 1; i < (rows.length - 1); i++) {
                var shouldSwitch = false;
                var x = rows[i].getElementsByTagName("TD")[colIndex];
                var y = rows[i + 1].getElementsByTagName("TD")[colIndex];
                if (colIndex === 0) { 
                    if (Number(x.innerHTML) > Number(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (colIndex === 3 || colIndex === 5) { 
                    if (parseFloat(x.innerHTML.replace(/[^0-9.-]+/g, '')) > parseFloat(y.innerHTML.replace(/[^0-9.-]+/g, ''))) {
                        shouldSwitch = true;
                        break;
                    }
                } else { 
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
            }
        }
    }
</script>

</body>
</html>
