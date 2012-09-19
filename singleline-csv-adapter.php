<?php
require_once('app/Mage.php');
umask(0);
Mage::app();

$row = 0;
if (($handle = fopen('singleline.csv', "r")) !== FALSE) {
	while (($zeilen = fgetcsv($handle, 0, ",")) !== FALSE) { // datei zeilenweise durchgehen
		$num = count($zeilen); // anzahl spalten
		$attribute_gefunden = '';
		for ($c=0; $c < $num; $c++) { // spalten durchgehen
			if ($row == 0){ // Array für Keys anlegen
				$keys[$c] = $zeilen[$c];
			} else { // Produkte in Array speichern
				$data[$row-1][$keys[$c]] = $zeilen[$c]; // Schreibe Werte in Array
				$trenner = '||';
				if (strpos($zeilen[$c], $trenner) !== false) { // Wenn mehrere Attribute
					$attribute_gefunden[] = array('in_spalte' => $c, 'attribute' => $zeilen[$c]);
				}
			}
		}
		if ($attribute_gefunden != ''){ // wenn in einer Zeile Attribute gefunden wurden
			$aktuelles_attribut = 0;
			foreach ($attribute_gefunden as $attribute) { // attribute durchgehen
				$attribute_array = explode($trenner, $attribute['attribute']);
				foreach ($attribute_array as $attribut) { // attribute in Zeilen schreiben
					$data[$row-1][$keys[0]] = $zeilen[0];
					$data[$row-1][$keys[$attribute['in_spalte']]] = $attribut;
					// noch kommende Attribute leeren
					if (count($attribute_gefunden) > 1){
						$aktuelles_attribut2 = 0;
						foreach ($attribute_gefunden as $attribute2) { // attribute durchgehen
							if ($aktuelles_attribut < $aktuelles_attribut2){
								$data[$row-1][$keys[$attribute2['in_spalte']]] = '';
							}
							$aktuelles_attribut2++;
						}
					}
					$row++; 
				}
				$aktuelles_attribut++;
			}
		}
		$row++;
	}
	fclose($handle);
}
Mage::getSingleton('fastsimpleimport/import')->processProductImport($data);