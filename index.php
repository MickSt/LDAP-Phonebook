<!DOCTYPE html>
<html>
<head>
    <title>Telefonbuch</title>
    <style>
        a { color: #000; text-transform: lowercase; }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dark-mode {
            background-color: #222;
            color: #fff;
        }

        .dark-mode .container {
            background-color: #333;
        }

        .dark-mode table {
            background-color: #333;
            color: #fff;
        }

        .dark-mode a { color: #fff; }

        .dark-mode tr:nth-child(even) {
            background-color: #555;
        }

        .dark-mode input[type="text"],
        .dark-mode select,
        .dark-mode input[type="submit"] {
            background-color: #444;
            color: #fff;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        input[type="text"],
        select {
            padding: 8px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        input[type="submit"] {
            padding: 8px 16px;
            font-size: 16px;
            border-radius: 4px;
            background-color: #4caf50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background-color: #fff;
        }

        th, td {
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .dark-mode-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px;
            font-size: 16px;
            border-radius: 4px;
            background-color: #4caf50;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .dark-mode-toggle:hover {
            background-color: #45a049;
        }

        .dark-mode .dark-mode-toggle {
            background-color: #333;
            color: #fff;
        }

        .dark-mode .dark-mode-toggle:hover {
            background-color: #444;
        }
    </style>

</head>
<body>
    <div class="container">
        <h2>Telefonbuch</h2>
        <form method="post" action="">
            <input type="text" name="search" placeholder="Suchbegriff eingeben">
            <select name="searchField">
                <option value="*">Alles</option>
                <option value="sn">Nachname</option>
                <option value="givenname">Vorname</option>
                <option value="roomnumber">Raumnummer</option>
                <option value="telephonenumber">Telefonnummer</option>
                <option value="mobile">Mobiltelefonnummer</option>
                <option value="mail">E-Mail</option>
                <option value="department">Abteilung</option>
                <option value="title">Aufgabe</option>
            </select>
            <input type="submit" value="Suchen">
        </form>

        <table id="phonebook">
            <thead>
                <tr>
                    <th data-column="name">Nachname</th>
                    <th data-column="givenname">Vorname</th>
                    <th data-column="room">Raumnummer</th>
                    <th data-column="phone">Telefonnummer</th>
                    <th data-column="mobile">Mobiltelefonnummer</th>
                    <th data-column="email">Email</th>
                    <th data-column="department">Abteilung</th>
                    <th data-column="title">Aufgabe</th>
                </tr>
            </thead>
            <tbody>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // LDAP-Server-Konfiguration
        $ldapHost = "ldap.example.com";
        $ldapPort = 389;
        $ldapUser = "example\ldap";
        $ldapPass = "password";
        $ldapBaseDN = "ou=example,dc=example,dc=com";

        // Verbindung zum LDAP-Server herstellen
        $ldapConn = ldap_connect($ldapHost, $ldapPort);
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        // Anmeldung am LDAP-Server
        $ldapBind = ldap_bind($ldapConn, $ldapUser, $ldapPass);

        if ($ldapBind) {
            // Suchparameter vorbereiten
            $searchTerm = $_POST["search"];
            $searchField = $_POST["searchField"];

            if ($searchTerm == "") {
                // Zeige alle Einträge an, wenn das Suchfeld leer ist
                $ldapFilter = "(&(objectClass=person)(objectCategory=person)(|(company=example)(company=example2)))";
            } elseif ($searchField == "*") {
                // Suche in allen Feldern
                $ldapFilter = "(&(objectClass=person)(objectCategory=person)(|(company=example)(company=example2))(|(cn=*{$searchTerm}*)(physicaldeliveryofficename=*{$searchTerm}*)(telephonenumber=*{$searchTerm}*)(mobile=*{$searchTerm}*)(mail=*{$searchTerm}*)(department=*{$searchTerm}*)(title=*>
            } else {
                // Suche in einem bestimmten Feld
                $ldapFilter = "(&(objectClass=person)(objectCategory=person)(|(company=example)(company=example2))(|({$searchField}=*{$searchTerm}*)))";
            }

            $ldapFields = array("sn", "givenname", "physicaldeliveryofficename", "telephonenumber", "mobile", "mail", "department", "title");

            // Suche im LDAP-Verzeichnis durchführen
            $ldapSearch = ldap_search($ldapConn, $ldapBaseDN, $ldapFilter, $ldapFields);

            // Gefundene Einträge auslesen
            $ldapEntries = ldap_get_entries($ldapConn, $ldapSearch);

            if ($ldapEntries["count"] > 0) {
                // Tabelle zur Anzeige der Ergebnisse erstellen

                for ($i = 0; $i < $ldapEntries["count"]; $i++) {
                    $name = isset($ldapEntries[$i]["sn"][0]) ? $ldapEntries[$i]["sn"][0] : "-";
                    $givenname = isset($ldapEntries[$i]["givenname"][0]) ? $ldapEntries[$i]["givenname"][0] : "-";

                    // Überspringe Einträge ohne Vorname und Nachname
                    if ($name === "-" && $givenname === "-") {
                       continue;
                    }

                    $roomNumber = isset($ldapEntries[$i]["physicaldeliveryofficename"][0]) ? $ldapEntries[$i]["physicaldeliveryofficename"][0] : "-";
                    $phoneNumber = isset($ldapEntries[$i]["telephonenumber"][0]) ? $ldapEntries[$i]["telephonenumber"][0] : "-";
                    $mobileNumber = isset($ldapEntries[$i]["mobile"][0]) ? $ldapEntries[$i]["mobile"][0] : "-";
                    $email = isset($ldapEntries[$i]["mail"][0]) ? $ldapEntries[$i]["mail"][0] : "-";
                    $department = isset($ldapEntries[$i]["department"][0]) ? $ldapEntries[$i]["department"][0] : "-";
                    $title = isset($ldapEntries[$i]["title"][0]) ? $ldapEntries[$i]["title"][0] : "-";

                    echo "<tr><td>{$name}</td><td>{$givenname}</td><td>{$roomNumber}</td><td>{$phoneNumber}</td><td>{$mobileNumber}</td><td><a href=mailto:$email>$email</a></td><td>{$department}</td><td>{$title}</td></tr></tbody>";
                }

            } else {
                echo "Keine Ergebnisse gefunden.";
            }

            // Verbindung zum LDAP-Server trennen
            ldap_unbind($ldapConn);
        } else {
            echo "Fehler beim Verbinden mit dem LDAP-Server.";
        }
    }
    ?>
            </tbody>
        </table>
    </div>

    <button class="dark-mode-toggle" onclick="toggleDarkMode()">
        Toggle Dark Mode
    </button>

    <script>
        const body = document.getElementsByTagName('body')[0];
        const container = document.querySelector('.container');
        const table = document.querySelector('#phonebook');
        const tableHeaders = table.querySelectorAll('th');
        const darkModeToggle = document.querySelector('.dark-mode-toggle');
        const searchInput = document.querySelector('input[name="search"]');

        // Überprüfen, ob der Dark Mode in den Präferenzen des Nutzers gespeichert ist
        let isDarkMode = localStorage.getItem('darkMode') === 'true';

        // Funktion zum Aktualisieren des Dark Mode
        function updateDarkMode() {
            if (isDarkMode) {
                body.classList.add('dark-mode');
                container.classList.add('dark-mode');
                darkModeToggle.classList.add('dark-mode');
                darkModeToggle.textContent = 'Disable Dark Mode';
                tableHeaders.forEach(header => header.style.backgroundColor = '#333');
            } else {
                body.classList.remove('dark-mode');
                container.classList.remove('dark-mode');
                darkModeToggle.classList.remove('dark-mode');
                darkModeToggle.textContent = 'Enable Dark Mode';
                tableHeaders.forEach(header => header.style.backgroundColor = '#f2f2f2');
            }
        }

        updateDarkMode();

        function toggleDarkMode() {
            isDarkMode = !isDarkMode;

            // Speichern der Dark Mode-Präferenz im Local Storage
            localStorage.setItem('darkMode', isDarkMode);

            updateDarkMode();
        }

        function sortTable(columnIndex) {
            const rows = Array.from(table.getElementsByTagName('tr')).slice(1);
            const isAscending = table.dataset.sortOrder === 'asc';

            rows.sort((a, b) => {
                const valueA = a.cells[columnIndex].textContent.toLowerCase();
                const valueB = b.cells[columnIndex].textContent.toLowerCase();

                if (valueA < valueB) {
                    return isAscending ? 1 : -1;
                } else if (valueA > valueB) {
                    return isAscending ? -1 : 1;
                } else {
                    return 0;
                }
            });

            // Entferne vorhandene Zeilen aus der Tabelle
            while (table.rows.length > 1) {
                table.deleteRow(1);
            }

            // Füge die sortierten Zeilen zur Tabelle hinzu
            rows.forEach(row => table.appendChild(row));

            // Aktualisiere die Sortierreihenfolge
            table.dataset.sortOrder = isAscending ? 'desc' : 'asc';
        }

        // Sortiere die Tabelle standardmäßig nach dem Namen
        sortTable(0);

        // Füge den Klick-Event-Listener zu den Tabellenüberschriften hinzu
        tableHeaders.forEach((header, index) => {
            header.addEventListener('click', () => {
                sortTable(index);
            });
        });

        // Setze den Fokus automatisch auf das Suchfeld
        searchInput.focus();
    </script>
</body>
</html>
