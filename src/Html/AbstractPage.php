<?php

/**
 * Névtere: App\Html
 * 
 * Az osztályok HTML struktúrákat biztosítanak a REST API ügyfél számára.
 */
namespace App\Html;

use App\Interfaces\PageInterface;

/**
 * Absztrakt osztály: AbstractPage
 * 
 * Alapstruktúrát biztosít az oldalakon a REST API ügyfélben.
 * Tartalmazza a közös HTML komponenseket, például fejléc, navigációs sáv és keresőmező funkciókat.
 */
abstract class AbstractPage implements PageInterface
{
    /**
     * @apiName GenerateHead
     * @apiGroup HTMLComponents
     * @apiDescription Generálja a HTML <head> szekcióját meta tag-ekkel és JavaScript fájlokkal.
     * 
     * @apiSuccess {void} void A fejléc HTML kódjának kiírása sikeres.
     * 
     * @return void
     */
    static function head()
    {
        echo '<!DOCTYPE html>
        <html lang="hu-hu">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>REST API Ügyfél</title>
           
            <!-- Script -->
            <script src="js/jquery-3.7.1.js" type="text/javascript"></script>
            <script src="js/app.js" type="text/javascript"></script>
        
        </head>';
    }

    /**
     * @apiName GenerateNav
     * @apiGroup HTMLComponents
     * @apiDescription Generálja a navigációs sávot a Megyék és Városok közötti váltáshoz.
     * 
     * @apiSuccess {void} void A navigációs HTML kód megjelenítése sikeres.
     * 
     * @return void
     */
    static function nav()
    {
        echo '
        <nav>
            <form name="nav" method="post" action="index.php">
                <button type="submit" name="btn-counties">Megyék</button>
                <button type="submit" name="btn-cities">Városok</button>
            </form>
        </nav>';
    }

    /**
     * Absztrakt metódus a táblázat fejléce struktúrájának definiálásához.
     * 
     * @return void
     */
    abstract static function tableHead();

    /**
     * Absztrakt metódus a táblázat törzsének definiálásához az entitások alapján.
     * 
     * @param array $entities Az entitások listája, amelyet meg kell jeleníteni.
     * @return void
     */
    abstract static function tableBody(array $entities);

    /**
     * Absztrakt metódus a teljes táblázat struktúrájának definiálásához.
     * 
     * @param array $entities Az entitások listája, amelyet meg kell jeleníteni.
     * @return void
     */
    abstract static function table(array $entities);

    /**
     * Absztrakt metódus a szerkesztői felület definiálásához.
     * 
     * @return void
     */
    abstract static function editor();

    /**
     * @apiName GenerateSearchBar
     * @apiGroup HTMLComponents
     * @apiDescription Generálja a keresőmezőt a kulcsszavas keresési funkcióhoz.
     * 
     * @apiSuccess {void} void A keresőmező megjelenítése sikeres.
     * 
     * @return void
     */
    static function searchbar()
    {
        echo '
        <form method="post" action="">
            <input type="text" name="keyword">
            <button type="submit" name="btn-search-city" title="Keresés">Keresés</button>
        </form>
        <br>';
    }

    /**
     * @apiName DisplayCountySearchResults
     * @apiGroup SearchResults
     * @apiDescription Megjeleníti a keresési eredményeket táblázatos formában a megyékhez.
     * 
     * @apiParam {Array} results A keresési eredmények.
     * @apiParam {String} keyword A keresési kulcsszó.
     * 
     * @apiSuccess {void} void A keresési eredmények megjelenítése sikeres.
     * @apiError {String} NoResults Nincs találat a megadott kulcsszóra.
     * 
     * @param array $results A keresési eredmények.
     * @param string $keyword A keresési kulcsszó.
     * @return void
     */
    static function displaySearchResults($results, $keyword)
    {
        if (!empty($results)) {
            echo "<table><thead><tr><th>Index</th><th>Név</th><th>Műveletek</th></tr></thead><tbody>";
            
            foreach ($results as $result) {
                echo "
                    <tr>
                        <td>{$result['id']}</td> <!-- Megye azonosítója -->
                        <td>{$result['name']}</td>
                        <td class='flex'>
                            <form method='post' action='' class='inline-form'>
                                <button type='submit' name='btn-edit-county' value='{$result['id']}' title='Módosítás'>Módosítás</button>
                            </form>
                            <form method='post' action=''>
                                <button type='submit' name='btn-del-county' value='{$result['id']}' title='Törlés'>Törlés</button>
                            </form>
                        </td>
                    </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>Nincs találat a következő keresési kifejezésre: <strong>$keyword</strong></p>";
        }
    }

    /**
     * @apiName DisplayCitySearchResults
     * @apiGroup SearchResults
     * @apiDescription Megjeleníti a városok keresési eredményeit egy táblázatban.
     * 
     * @apiParam {Array} filteredCities A szűrt városok listája.
     * @apiParam {Array} counties A megyék listája a város-megye kapcsolatok feloldásához.
     * 
     * @apiSuccess {void} void A keresési eredmények megjelenítése sikeres.
     * @apiError {String} NoResults Nincs találat a megadott keresési feltétellel.
     * 
     * @param array $filteredCities A szűrt városok listája.
     * @param array $counties A megyék listája a város-megye kapcsolatok feloldásához.
     * @return void
     */
    static function displayCitySearchResults(array $filteredCities, array $counties)
    {
        echo '<h2>Keresési eredmények:</h2>';
        if (!empty($filteredCities)) {
            echo '<table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Város</th>
                            <th>Irányítószám</th>
                            <th>Megye</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($filteredCities as $city) {
                $countyName = '';
                foreach ($counties as $county) {
                    if ($county['id'] == $city['id_county']) {
                        $countyName = $county['name'];
                        break;
                    }
                }
                $i = 0; 
                foreach ($filteredCities as $city) {
                    $rowClass = (++$i % 2 === 0) ? "even" : "odd"; 
                    echo "
                        <tr class='{$rowClass}'>
                            <td>{$city['id']}</td>
                            <td>{$city['city']}</td>
                            <td>{$city['zip_code']}</td>
                            <td>{$countyName}</td>
                            <td class='flex'>
                                <form method='post' action='' class='inline-form'>
                                    <input type='hidden' name='id' value='{$city['id']}'>
                                    <button type='submit' name='btn-edit-city' value='{$city['id']}' title='Szerkesztés'></button>
                                </form>
                                <form method='post' action=''>
                                    <button type='submit' name='btn-del-city' value='{$city['id']}' title='Törlés'></button>
                                </form>
                            </td>
                        </tr>";
                }
            }
            echo '</tbody></table>';
        } else {
            echo '<p>Nincs találat a megadott keresési feltétellel.</p>';
        }
    }
}
