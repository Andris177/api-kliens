<?php

namespace App\Html;

use App\RestApiClient\Client;
use App\Interfaces\PageInterface;
use App\Html\AbstractPage;
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
class Request {
    /**
     * @api {post} /request POST kérések kezelése
     * @apiName HandleRequest
     * @apiGroup Request
     * @apiDescription Ez a végpont különböző POST kéréseket kezel, mint például megyék mentése, törlése, szerkesztése, keresése és városok kezelése.
     * 
     * @apiSuccess {String} message A művelet sikerének üzenete.
     * @apiError {String} error Hibaüzenet, ha a művelet nem sikerült.
     */
    static function handle()
    {
        switch ($_SERVER["REQUEST_METHOD"]){
            case "POST":
                self::postRequest();
                break;

        }
    }
    /**
     * @api {post} /request/btn-save-new-county Új megye mentése
     * @apiName SaveNewCounty
     * @apiGroup Request
     * @apiDescription Ez a végpont lehetővé teszi egy új megye mentését a megye nevének megadásával.
     *
     * @apiParam {String} new_name Az új megye neve, amelyet el szeretnénk menteni.
     *
     * @apiSuccess {String} message "A város sikeresen hozzáadva!" - A város sikeresen hozzáadva.
     * @apiError {String} error "Hiba történt a mentés során!" - Hiba a város mentése közben.
     */
    private static function postRequest()
    {
        $request = $_REQUEST;

        switch ($request) {
            case isset($request['btn-home']):
                break;

            case isset($request['btn-counties']):
                PageCounties::table(self::getCounties());
                break;

            case isset($request['btn-save-county']):
                $client = new Client();
                if (!empty($request['id'])) {
                    $data['id'] = $request['id'];
                }
                break;
            /**
             * @api {delete} /request/btn-del-county Megye törlése
             * @apiName DeleteCounty
             * @apiGroup Request
             * @apiDescription Ez a végpont törli a megyét a megadott megye ID alapján.
             *
             * @apiParam {Number} id A törlendő megye ID-ja.
             *
             * @apiSuccess {String} message "Sikeres törlés!" - A megye sikeresen törölve.
             * @apiError {String} error Hibaüzenet, ha a törlés nem sikerült.
             */

            case isset($request['btn-del-county']):
                $id = $request['btn-del-county'];
                $client = new Client();
                $response = $client->delete('counties/' . $id, $id);
                if ($response && isset($response['success']) && $response['success']) {
                    echo "Sikeres törlés!";
                } 
                PageCounties::table(self::getCounties());
                break;
            case isset($request['btn-save-new-county']):
                $name = $request['new_name'];
                $client = new Client();
                $response = $client->post('counties', ['name' => $name]);
                if ($response && isset($response['success']) && $response['success']) {
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit; 
                } else {
                    echo "Hiba történt a mentés során!";
                }
                PageCounties::table(self::getCounties());
                break;
            /**
             * @api {post} /request/btn-search Megye keresése név alapján
             * @apiName SearchCounties
             * @apiGroup Request
             * @apiDescription Ez a végpont lehetővé teszi megyék keresését név alapján egy kulcsszó segítségével.
             *
             * @apiParam {String} keyword A keresési kulcsszó a megyék neveihez.
             *
             * @apiSuccess {Object[]} results A keresési kulcsszónak megfelelő megyék listája.
             * @apiError {String} error "Nincs találat!" - Nincs találat.
             */

            case isset($request['btn-search']):
                $keyword = $_POST['keyword'];
                $results = self::searchCountiesByName($keyword);
                echo "<h2>Keresési eredmények:</h2>";
                AbstractPage::searchbar(); 
                AbstractPage::displaySearchResults($results, $keyword);
                break;
               
            case isset($request['btn-edit-county']):
                $id = $request['btn-edit-county'];
                $client = new Client();
                $county = $client->get('counties/' . $id); 

                PageCounties::displayEditForm($county); 
                break;
            /**
             * @api {put} /request/btn-save-edit-county Megye adatainak módosítása
             * @apiName SaveEditCounty
             * @apiGroup Request
             * @apiDescription Ez a végpont lehetővé teszi egy meglévő megye nevének módosítását.
             *
             * @apiParam {Number} id A módosítandó megye ID-ja.
             * @apiParam {String} edit_name Az új megye név.
             *
             * @apiSuccess {String} message "A név sikeresen módosítva lett!" - A név sikeresen módosítva.
             * @apiError {String} error Hibaüzenet, ha a módosítás nem sikerült.
             */
            case isset($request['btn-save-edit-county']):
                $id = $request['id'];
                $newName = $request['edit_name'];
                $client = new Client();
                        
                $response = $client->put('counties/' . $id, ['name' => $newName]);
                if ($response && isset($response['success']) && $response['success']) {
                    echo "A név sikeresen módosítva lett!";
                } else {
                    echo "Hiba történt a módosítás során!";
                }
                PageCounties::table(self::getCounties());
                break;
            /**
             * @api {post} /request/btn-show-cities Városok megjelenítése egy megye alapján
             * @apiName ShowCities
             * @apiGroup Request
             * @apiDescription Ez a végpont minden várost lekér és megjelenít, amely egy adott megyéhez tartozik.
             *
             * @apiParam {Number} selected-county A kiválasztott megye ID-ja.
             *
             * @apiSuccess {Object[]} cities A kiválasztott megyéhez tartozó városok listája.
             * @apiError {String} error "Hiba történt a városok megjelenítésekor!" - Hiba a városok megjelenítésekor.
             */
            case isset($request['btn-show-cities']):
                self::handleShowCities($request);
                break;
                
            case isset($request['btn-filter-letter']):
                self::handleFilterLetter($request);
                break;
            /**
             * @api {post} /request/btn-save-new-city Új város mentése
             * @apiName SaveNewCity
             * @apiGroup Request
             * @apiDescription Ez a végpont új város mentését teszi lehetővé egy kiválasztott megye alatt.
             *
             * @apiParam {String} new_city-name Az új város neve.
             * @apiParam {String} new_zip-code Az új város irányítószáma.
             * @apiParam {Number} selected-county A megye ID-ja, amelyhez a város tartozik.
             *
             * @apiSuccess {String} message "Új város hozzáadva!" - Az új város sikeresen hozzáadva.
             * @apiError {String} error "Kérem, töltse ki az összes mezőt!" - Kérjük, töltse ki az összes mezőt.
             */
            case isset($request['btn-save-new-city']):
                self::handleNewCityRequest($request);
                break;
            /**
             * @api {delete} /request/btn-del-city Város törlése
             * @apiName DeleteCity
             * @apiGroup Request
             * @apiDescription Ez a végpont törli a várost a megadott város ID alapján.
             *
             * @apiParam {Number} id A törlendő város ID-ja.
             *
             * @apiSuccess {String} message "A város sikeresen törölve!" - A város sikeresen törölve.
             * @apiError {String} error Hibaüzenet, ha a törlés nem sikerült.
             */
            case isset($request['btn-del-city']):
                self::handleCityDeletion($request);
                break;
            /**
             * @api {post} /request/btn-search-city Városok keresése név alapján
             * @apiName SearchCity
             * @apiGroup Request
             * @apiDescription Ez a végpont lehetővé teszi városok keresését név alapján egy kulcsszó segítségével.
             *
             * @apiParam {String} keyword A keresési kulcsszó a városok neveihez.
             *
             * @apiSuccess {Object[]} results A keresési kulcsszónak megfelelő városok listája.
             * @apiError {String} error "Nincs találat!" - Nincs találat.
             */
            case isset($request['btn-search-city']):
                self::handleCitySearch($request['keyword']);
                break;
             case isset($request['btn-edit-city']):
                self::handleEditCity($request);
                break;
            /**
             * @api {put} /request/btn-save-edit-city Város adatainak módosítása
             * @apiName EditCity
             * @apiGroup Request
             * @apiDescription Ez a végpont lehetővé teszi egy város adatainak, például a név, irányítószám, megye módosítását.
             *
             * @apiParam {Number} city_id A módosítandó város ID-ja.
             * @apiParam {String} city_name Az új város név.
             * @apiParam {String} zip_code Az új irányítószám.
             * @apiParam {Number} county_id Az új megye ID-ja.
             *
             * @apiSuccess {String} message "A város adatai sikeresen módosítva lettek!" - A város adatai sikeresen módosítva.
             * @apiError {String} error Hibaüzenet, ha a módosítás nem sikerült.
             */
            case isset($request['btn-save-edit-city']):
                self::handleSaveEditCity($request);
                break;
            default:
                 $counties = self::getCounties();
                  PageCities::render($counties);
                break;
        }
    }
private static function getCounties(): array
{
    $client = new Client();
    $response = $client->get('counties');
    
    // Ellenőrizd, hogy a válasz érvényes-e és tartalmazza-e a 'data' kulcsot
    if ($response && isset($response['data']) && is_array($response['data'])) {
        return $response['data'];
    }
    
    // Ha a válasz érvénytelen, dobjunk kivételt vagy térjünk vissza üres tömbbel
    return [];
}
    /**
     * @api {post} /request/btn-save-new-city Új város mentése
     * @apiName SaveNewCity
     * @apiGroup Request
     * @apiDescription Ez a végpont új város mentését teszi lehetővé egy kiválasztott megye alatt.
     *
     * @apiParam {String} new_city-name Az új város neve.
     * @apiParam {String} new_zip-code Az új város irányítószáma.
     * @apiParam {Number} selected-county A megye ID-ja, amelyhez a város tartozik.
     *
     * @apiSuccess {String} message "Új város hozzáadva!" - Az új város sikeresen hozzáadva.
     * @apiError {String} error "Kérem, töltse ki az összes mezőt!" - Kérjük, töltse ki az összes mezőt.
     */
    static function searchCountiesByName($keyword)
    {
        $client = new Client();
        $counties = $client->get('counties');
        
        $results = [];
        foreach ($counties['data'] as $county) {
            if (stripos($county['name'], $keyword) !== false) {
                $results[] = $county;
            }
        }
        
        return $results;
    }
/**
 * @api {get} /cities Városok lekérdezése
 * @apiName GetCities
 * @apiGroup City
 *
 * @apiDescription Lekéri az összes város adatait a REST API-tól.
 *
 * @apiSuccess {Object[]} cities Az összes város adatai.
 * @apiSuccess {Number} cities.id Város azonosítója.
 * @apiSuccess {String} cities.city Város neve.
 * @apiSuccess {String} cities.zip_code Város irányítószáma.
 * @apiSuccess {Number} cities.id_county Megye azonosítója.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

 private static function getCities(): array
 {
     $client = new Client();
     $response = $client->get('cities');
     
     // Ellenőrzés, hogy a válasz tartalmazza-e a szükséges adatokat
     if ($response && isset($response['data']) && is_array($response['data'])) {
         return $response['data'];
     }
     
     // Ha nincs megfelelő válasz, térjünk vissza üres tömbbel
     return [];
 }
/**
 * @api {get} /cities Megye alapján szűrt városok
 * @apiName FilterCitiesByCounty
 * @apiGroup City
 *
 * @apiDescription Megadott megyéhez tartozó városok listázása.
 *
 * @apiParam {Number} county_id A megye azonosítója.
 *
 * @apiSuccess {Object[]} cities Az adott megyéhez tartozó városok adatai.
 * @apiSuccess {Number} cities.id Város azonosítója.
 * @apiSuccess {String} cities.city Város neve.
 * @apiSuccess {String} cities.zip_code Irányítószám.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

    public static function filterCitiesByCounty(array $cities, ?int $countyId): array
    {
        if ($countyId === null) {
            return [];
        }

        return array_filter($cities, function ($city) use ($countyId) {
            return intval($city['id_county']) === $countyId;
        });
    }
/**
 * @api {get} /cities/filter Városok szűrése megyén és kezdőbetű alapján
 * @apiName FilterCitiesByCountyAndLetter
 * @apiGroup City
 *
 * @apiDescription Egy adott megyében található városok szűrése kezdőbetű alapján.
 *
 * @apiParam {Number} county_id A megye azonosítója.
 * @apiParam {String} letter Az első betű, amely alapján szűrjük a városokat.
 *
 * @apiSuccess {Object[]} cities A szűrésnek megfelelő városok.
 * @apiSuccess {Number} cities.id Város azonosítója.
 * @apiSuccess {String} cities.city Város neve.
 * @apiSuccess {String} cities.zip_code Irányítószám.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

    public static function filterCitiesByCountyAndLetter(array $cities, ?int $countyId, ?string $letter): array
    {
        if ($countyId === null) {
            return [];
        }

        $filteredCities = array_filter($cities, function ($city) use ($countyId) {
            return intval($city['id_county']) === $countyId;
        });

        if ($letter) {
            $filteredCities = array_filter($filteredCities, function ($city) use ($letter) {
                return stripos($city['city'], $letter) === 0;
            });
        }

        return $filteredCities;
    }
/**
 * @api {post} /request POST kérés kezelése
 * @apiName HandlePostRequest
 * @apiGroup Request
 *
 * @apiDescription A POST kérések feldolgozása, mint új város hozzáadása, város törlése stb.
 *
 * @apiSuccess {String} status A kérés sikeressége vagy hibája.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

    public static function handlePostRequest()
    {
        if (isset($_POST['btn-save-new-city'])) {
            $selectedCountyId = $_POST['selected-county'] ?? null;
            $newCityName = $_POST['new_city-name'] ?? '';
            $newZipCode = $_POST['new_zip-code'] ?? '';
            if ($selectedCountyId && !empty($newCityName) && !empty($newZipCode)) {
                $client = new Client();
                $response = $client->post('cities', [
                    'name' => $newCityName,
                    'zip_code' => $newZipCode,
                    'county_id' => $selectedCountyId
                ]);

                if ($response && isset($response['success']) && $response['success']) {
                    echo "A város sikeresen hozzáadva!";
                }
            } else {
                echo "Kérlek válassz megyét és adj meg városnevet és irányítószámot!";
            }
        }
    }
/**
 * @api {post} /cities/add Új város hozzáadásának kezelése
 * @apiName HandleNewCityRequest
 * @apiGroup City
 *
 * @apiDescription Kezeli az új város hozzáadására irányuló kéréseket, ellenőrzi a bemeneti adatokat, és meghívja az API-t az új város mentéséhez. Ezt követően megjeleníti az aktuális városok listáját.
 *
 * @apiParam {String} new_city-name Az új város neve.
 * @apiParam {String} new_zip-code Az új város irányítószáma.
 * @apiParam {Number} selected-county A megye azonosítója, amelyhez az új város tartozik.
 *
 * @apiSuccess {String} message Sikeres mentés üzenete.
 * @apiSuccess {Object[]} cities Az összes város adatai a mentés után.
 * @apiSuccess {Number} cities.id Város azonosítója.
 * @apiSuccess {String} cities.city Város neve.
 * @apiSuccess {String} cities.zip_code Irányítószám.
 * @apiSuccess {Number} cities.id_county A város megyéjének azonosítója.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */
    private static function handleNewCityRequest($request)
    {
        $newCityName = $request['new_city-name'] ?? null;
        $newZipCode = $request['new_zip-code'] ?? null;
        $selectedCountyId = $request['selected-county'] ?? null;

        if ($newCityName && $newZipCode && $selectedCountyId) {
            $response = self::addCity($newCityName, $newZipCode, $selectedCountyId);

            if ($response && isset($response['success']) && $response['success']) {
                echo "Új város hozzáadva!";
            } 
        } else {
            echo "Kérem, töltse ki az összes mezőt!";
        }
        $counties = self::getCounties();
        $cities = self::getCities();
        PageCities::render($counties, $cities, $selectedCountyId);
    }
/**
 * @api {post} /cities Új város hozzáadása
 * @apiName AddCity
 * @apiGroup City
 *
 * @apiDescription Egy új város hozzáadása a REST API-n keresztül.
 *
 * @apiParam {String} city A város neve.
 * @apiParam {String} zip_code Az irányítószám.
 * @apiParam {Number} id_county A megye azonosítója, amelyhez a város tartozik.
 *
 * @apiSuccess {String} message A sikeres mentés üzenete.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */
    private static function addCity($newCityName, $newZipCode, $selectedCountyId)
    {
        $client = new Client();
        return $client->post('cities', [
            'id_county' => $selectedCountyId,
            'city' => $newCityName,
            'zip_code' => $newZipCode
        ]);
    }
/**
 * @api {delete} /cities/:id Város törlése
 * @apiName HandleCityDeletion
 * @apiGroup City
 *
 * @apiDescription Egy város törlése a REST API-ból.
 *
 * @apiParam {Number} id A törölni kívánt város azonosítója.
 *
 * @apiSuccess {String} message A sikeres törlés üzenete.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */
    private static function handleCityDeletion($request)
    {
        $id = $request['btn-del-city'];
        $client = new Client();
        $response = $client->delete('cities/' . $id, $id);
    
        if ($response && isset($response['success']) && $response['success']) {
            echo "A város sikeresen törölve!";
        }
    
        $counties = self::getCounties();
        $cities = self::getCities();
        $selectedCountyId = $_POST['selected-county'] ?? null;
        PageCities::render($counties, $cities, $selectedCountyId);
    }
/**
 * @api {post} /cities/search Város keresése
 * @apiName HandleCitySearch
 * @apiGroup City
 *
 * @apiDescription Keresés város alapján, kilistázva a város adatait és a hozzá tartozó megye nevét.
 *
 * @apiParam {String} keyword A keresett város neve vagy irányítószáma.
 *
 * @apiSuccess {Object[]} cities Az összes találat a keresési feltételek alapján.
 * @apiSuccess {Number} cities.id Város azonosítója.
 * @apiSuccess {String} cities.city Város neve.
 * @apiSuccess {String} cities.zip_code Irányítószám.
 * @apiSuccess {String} cities.county_name Megye neve.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

    private static function handleCitySearch($keyword)
    {
        $client = new Client();
        $cities = $client->get('cities'); 
        $counties = $client->get('counties'); 
    
        $filteredCities = array_filter($cities['data'], function ($city) use ($keyword) {
            return stripos($city['city'], $keyword) !== false;
        });
    
        AbstractPage::displayCitySearchResults($filteredCities, $counties['data']);
    }
/**
 * @api {put} /cities/:id Városadatok módosításának mentése
 * @apiName HandleSaveEditCity
 * @apiGroup City
 *
 * @apiDescription Kezeli a meglévő város adatainak (név, irányítószám, megye) frissítését az API-n keresztül.
 *
 * @apiParam {Number} city_id A módosítandó város azonosítója.
 * @apiParam {String} city_name A frissített városnév.
 * @apiParam {String} zip_code Az új irányítószám.
 * @apiParam {Number} county_id A városhoz tartozó megye azonosítója.
 *
 * @apiSuccess {String} message A sikeres módosítás üzenete.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

    private static function handleSaveEditCity($request)
    {
        $cityId = $request['city_id'];
        $updatedName = $request['city_name'];
        $updatedZipCode = $request['zip_code'];
        $updatedCountyId = $request['county_id'];

        $client = new Client();
        $response = $client->put("cities/$cityId", [
            'city' => $updatedName,
            'zip_code' => $updatedZipCode,
            'id_county' => $updatedCountyId
        ]);

        if ($response && isset($response['success']) && $response['success']) {
            echo "A város adatai sikeresen módosítva lettek!";
        }
        $counties = self::getCounties();
        $cities = self::getCities();
        PageCities::render($counties, $cities, $updatedCountyId);
    }
/**
 * @api {post} /cities/edit Város adatainak módosítása
 * @apiName HandleEditCity
 * @apiGroup City
 *
 * @apiDescription Egy adott város nevének, irányítószámának, vagy megyéjének módosítása.
 *
 * @apiParam {Number} city_id A módosítandó város azonosítója.
 * @apiParam {String} city_name A módosított város neve.
 * @apiParam {String} zip_code Az új irányítószám.
 * @apiParam {Number} county_id Az új megye azonosítója.
 *
 * @apiSuccess {String} message Sikeres módosítás üzenet.
 * @apiSuccess {Object} city A módosított város adatai.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */
    private static function handleEditCity($request)
    {
        $cityId = $request['btn-edit-city'];
        $client = new Client();

        $city = $client->get("cities/$cityId");
        $counties = self::getCounties();

        if ($city && isset($city['data'])) {
            PageCities::displayEditForm($city['data'], $counties); 
        } 
    }
/**
 * @api {post} /cities Városok megjelenítése
 * @apiName HandleShowCities
 * @apiGroup City
 *
 * @apiDescription Megjeleníti az összes várost, vagy egy adott megyéhez tartozó városokat.
 *
 * @apiParam {Number} [selected_county] A megye azonosítója, ha szűrni szeretnénk a városokat.
 *
 * @apiSuccess {Object[]} cities Az összes város adatai.
 * @apiSuccess {Number} cities.id Város azonosítója.
 * @apiSuccess {String} cities.city Város neve.
 * @apiSuccess {String} cities.zip_code Irányítószám.
 * @apiSuccess {Number} cities.id_county Megye azonosítója.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

    private static function handleShowCities($request)
    {
        $selectedCountyId = !empty($request['selected-county']) ? intval($request['selected-county']) : null;
    
        $counties = self::getCounties();
        $cities = self::getCities();
    
        PageCities::render($counties, $cities, $selectedCountyId);
    }
/**
 * @api {post} /cities/filter Városok szűrése kezdőbetű alapján
 * @apiName HandleFilterLetter
 * @apiGroup City
 *
 * @apiDescription Egy adott megyében található városok szűrése az első betűjük alapján.
 *
 * @apiParam {Number} selected_county Megye azonosítója.
 * @apiParam {String} filter_letter Az első betű, amely alapján a városokat szűrjük.
 *
 * @apiSuccess {Object[]} cities Az összes szűrt város adatai.
 * @apiSuccess {Number} cities.id Város azonosítója.
 * @apiSuccess {String} cities.city Város neve.
 * @apiSuccess {String} cities.zip_code Irányítószám.
 *
 * @apiError {String} error Hiba esetén visszaadott üzenet.
 */

    private static function handleFilterLetter($request)
    {
        $selectedCountyId = !empty($request['selected-county']) ? intval($request['selected-county']) : null;
        $filterLetter = !empty($request['filter-letter']) ? strtoupper($request['filter-letter']) : null;

        $counties = self::getCounties();
        $cities = self::getCities();

        PageCities::render($counties, $cities, $selectedCountyId, $filterLetter);
    }
}