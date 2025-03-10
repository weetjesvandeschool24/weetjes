<?php
date_default_timezone_set('Europe/Amsterdam');

// Haal de huidige tijd op
$now = new DateTime();
$currentTime = $now->format('H:i:s');

// Laad de YAML-gegevens
$yamlFile = 'duurzaamheid.yaml'; // YAML-bestand moet op de server staan
$yamlData = file_exists($yamlFile) ? yaml_parse_file($yamlFile) : null;

// Controleer of de YAML-data correct is geladen
$weetjeTekst = "Kon de weetjes niet laden.";
if ($yamlData && isset($yamlData['duurzaamheid']['sets'])) {
    $sets = $yamlData['duurzaamheid']['sets'];
    
    // Bepaal de dag van de week en weeknummer
    $dayOfWeek = date('N'); // Maandag = 1, Zondag = 7
    $weekNumber = date('W');

    if ($dayOfWeek >= 6) {
        $weetjeTekst = "Geen weetje in het weekend!";
    } else {
        $currentSet = $sets[$weekNumber % count($sets)];
        
        // Bepaal of het een fout weetje moet zijn
        session_start();
        if (!isset($_SESSION['weetjeWeek']) || $_SESSION['weetjeWeek'] != $weekNumber) {
            $_SESSION['foutDag'] = rand(1, 5); // Willekeurige doordeweekse dag
            $_SESSION['weetjeWeek'] = $weekNumber;
        }

        // Selecteer een weetje
        $weetjes = array_filter($currentSet['weetjes'], fn($w) => empty($w['foute']));
        $fouteWeetje = array_values(array_filter($currentSet['weetjes'], fn($w) => !empty($w['foute'])))[0] ?? null;

        if ($dayOfWeek == $_SESSION['foutDag'] && $fouteWeetje) {
            $weetjeTekst = $fouteWeetje['tekst'];
        } elseif (!empty($weetjes)) {
            $weetjeTekst = $weetjes[array_rand($weetjes)]['tekst'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duurzaamheidsweetjes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #7ccbff;
            color: #333;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            position: relative;
        }
        .weetje-container {
            text-align: center;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 1300px;
        }
        h1 {
            font-size: 40px;
            margin-bottom: 20px;
            color: #4caf50;
        }
        p {
            font-size: 30px;
            margin: 0;
            line-height: 1.5;
        }
        footer {
            margin-top: 20px;
            font-size: 25px;
            color: #777;
        }
        #clock {
            font-size: 72px;
            font-weight: bold;
            font-family: 'Arial Black', sans-serif;
            color: #222;
            position: absolute;
            bottom: 25px;
            left: 35px;
        }
    </style>
</head>
<body>
    <div class="weetje-container">
        <h1>Weetje van de dag</h1>
        <p id="weetje"><?php echo htmlspecialchars($weetjeTekst); ?></p>
        <footer>Elke dag een stapje dichter bij duurzaamheid!</footer>
    </div>

    <div id="clock"><?php echo $currentTime; ?></div>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
    </script>
</body>
</html>
