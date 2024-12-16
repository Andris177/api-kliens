<?php
namespace App\Html;
 
use App\Html\AbstractPage;  
 
class PageCounties extends AbstractPage
{
    static function table(array $entities)
    {
        self::searchBar();

        // New button to create a county
        echo '
        <button id="btn-new-county" onclick="toggleNewCountyForm()">Új megye létrehozása</button>
        
        <div id="new-county-form" style="display:none;">
            <form method="post" action="">
                <input type="text" name="new_name" placeholder="Megye neve" required>
                <button type="submit" name="btn-save-new-county" class="save-btn">Mentés</button>
            </form>
        </div>';

        echo '<table id="counties-table">';
        self::tableHead();
        self::tableBody($entities);
        echo '</table>';

        echo '
        <script>
            function toggleNewCountyForm() {
                var form = document.getElementById("new-county-form");
                if (form.style.display === "none") {
                    form.style.display = "block";
                } else {
                    form.style.display = "none";
                }
            }
        </script>';
    }
 
    static function tableHead() {
        echo '
        <thead>
            <tr>
                <th>Index</th>
                <th>Név</th>
                <th>Műveletek</th>
            </tr>
        </thead>';
    }

    static function editor()
    {
        echo '
            <th>&nbsp;</th>
            <th>
                <form name="county-editor" method="post" action="">
                    <input type="hidden" id="id" name="id">
                    <input type="search" id="name" name="name" placeholder="Megye" required>
                    <button type="submit" id="btn-update-county" name="btn-update-county" title="Frissítés">Frissítés</button> 
                    <button type="button" id="btn-cancel-county" title="Mégse">Mégse</button>
                </form>
            </th>
            <th class="flex">
            &nbsp;
            </th>
        ';
    }
    
    
 
    static function tableBody(array $entities)
    {
        echo '<tbody>';
        $i = 0;
        foreach ($entities as $entity) {
            echo "
            <tr class='" . (++$i % 2 ? "odd" : "even") . "'>
                <td>{$entity['id']}</td>
                <td id='name-{$entity['id']}'>{$entity['name']}</td> 
                <td class='flex'>
                    <form method='post' action='' class='inline-form'>
                        <input type='hidden' name='id' value='{$entity['id']}'>
                        <button type='submit' name='btn-edit-county' value='{$entity['id']}' title='Módosítás'>Módosítás</button>
                    </form>
                    <form method='post' action=''>
                        <button type='submit' id='btn-del-county-{$entity['id']}' name='btn-del-county' value='{$entity['id']}' title='Törlés'>Törlés</button>
                    </form>
                </td>
            </tr>";
        }
        echo '</tbody>';
    }
    
    public static function displayEditForm($countyResponse)
    {
        if (isset($countyResponse['data'])) {
            $county = $countyResponse['data'];
            $id = $county['id'];
            $name = $county['name'];
    
            echo "
            <label for='old_name'>Régi név: {$name}</label>
            <form method='post' action=''>
                <input type='hidden' name='id' value='{$id}'>
                <label for='edit_name'>Új név:</label>
                <input type='text' name='edit_name' value='{$name}' required>
                <button type='submit' name='btn-save-edit-county'>Mentés</button>
                <button type='submit' name='btn-home'>Kilépés</button>
            </form>
            ";
        } else {
            echo "<p>Hiba történt a megye adatok lekérésekor.</p>";
        }
    }
    
}