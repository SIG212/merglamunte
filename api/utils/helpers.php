<?php
/**
 * Funcții helper pentru API-ul de analiză
 */

/**
 * Determină contextul (sezon, dificultate, etc.)
 */
if (!function_exists('determinaContext')) {
    function determinaContext($masiv, $data, $altitudine_tinta) {
        $luna = intval(date('m', strtotime($data)));
        
        // Determină sezonul
        $sezon = 'vara';
        if ($luna >= 11 || $luna <= 3) {
            $sezon = 'iarna';
        } elseif ($luna == 4 || $luna == 10) {
            // Tranziție - depinde de altitudine
            $sezon = ($altitudine_tinta > 1800) ? 'iarna' : 'vara';
        }
        
        // Determină dificultatea bazat pe masiv și altitudine
        $dificultate_grad = calculeazaDificultate($masiv, $altitudine_tinta);
        
        return [
            'sezon' => $sezon,
            'luna' => $luna,
            'data' => $data,
            'dificultate_grad' => $dificultate_grad,
            'dificultate_text' => getDificultateText($dificultate_grad),
            'zona' => getZonaMasiv($masiv),
            'masiv_display' => ucfirst(str_replace('-', ' ', $masiv)),
            'altitudine_tinta' => $altitudine_tinta
        ];
    }
}

/**
 * Calculează dificultatea bazat pe masiv și altitudine
 */
if (!function_exists('calculeazaDificultate')) {
    function calculeazaDificultate($masiv, $altitudine) {
        // Masive cu trasee dificile
        $masive_dificile = ['fagaras', 'retezat', 'piatra-craiului', 'rodnei'];
        $masive_medii = ['bucegi', 'parang', 'iezer', 'calimani', 'godeanu', 'tarcu'];
        
        $base_dificultate = 2; // Default mediu
        
        if (in_array($masiv, $masive_dificile)) {
            $base_dificultate = 3;
        } elseif (!in_array($masiv, $masive_medii)) {
            $base_dificultate = 2;
        }
        
        // Ajustare bazată pe altitudine
        if ($altitudine > 2300) {
            $base_dificultate = min(5, $base_dificultate + 2);
        } elseif ($altitudine > 2000) {
            $base_dificultate = min(5, $base_dificultate + 1);
        } elseif ($altitudine < 1500) {
            $base_dificultate = max(1, $base_dificultate - 1);
        }
        
        return $base_dificultate;
    }
}

/**
 * Text pentru grad dificultate
 */
if (!function_exists('getDificultateText')) {
    function getDificultateText($grad) {
        $texte = [
            1 => 'Ușor',
            2 => 'Moderat',
            3 => 'Dificil',
            4 => 'Foarte dificil',
            5 => 'Extrem'
        ];
        return $texte[$grad] ?? 'Necunoscut';
    }
}

/**
 * Obține zona geografică pentru masiv
 */
if (!function_exists('getZonaMasiv')) {
    function getZonaMasiv($masiv) {
        $zone = [
            'bucegi' => 'Carpații Meridionali',
            'fagaras' => 'Carpații Meridionali',
            'retezat' => 'Carpații Meridionali',
            'piatra-craiului' => 'Carpații Meridionali',
            'parang' => 'Carpații Meridionali',
            'tarcu' => 'Carpații Meridionali',
            'godeanu' => 'Carpații Meridionali',
            'cindrel' => 'Carpații Meridionali',
            'cozia' => 'Carpații Meridionali',
            'iezer' => 'Carpații Meridionali',
            'baiului' => 'Carpații Meridionali',
            'ciucas' => 'Carpații Meridionali',
            'buila' => 'Carpații Meridionali',
            'rodnei' => 'Carpații Orientali',
            'ceahlau' => 'Carpații Orientali',
            'calimani' => 'Carpații Orientali',
            'hasmas' => 'Carpații Orientali',
            'bistritei' => 'Carpații Orientali',
            'maramuresului' => 'Carpații Orientali',
            'apuseni' => 'Carpații Occidentali',
            'mehedinti-cernei' => 'Carpații Occidentali'
        ];
        return $zone[strtolower($masiv)] ?? 'România';
    }
}

/**
 * Aplică matricea de risc
 */
if (!function_exists('aplicaMatrice')) {
    function aplicaMatrice($nivel_experienta, $sezon, $dificultate, $meteo_status, $context = []) {
        // Încarcă matricea
        $matrice_path = __DIR__ . '/../config/matrice-risc.php';
        if (!file_exists($matrice_path)) {
            // Fallback simplu
            return aplicaMatriceFallback($meteo_status, $dificultate, $nivel_experienta);
        }
        
        $matrice = require $matrice_path;
        
        // Normalizare nivel
        $nivel_map = [
            'incepator' => 'incepator',
            'mediu' => 'mediu',
            'experimentat' => 'experimentat',
            'beginner' => 'incepator',
            'intermediate' => 'mediu',
            'advanced' => 'experimentat'
        ];
        $nivel = $nivel_map[$nivel_experienta] ?? 'mediu';
        
        // Lookup în matrice
        $dificultate = max(1, min(5, $dificultate));
        
        if (isset($matrice[$nivel][$sezon][$dificultate][$meteo_status])) {
            $status = $matrice[$nivel][$sezon][$dificultate][$meteo_status];
        } else {
            // Fallback
            return aplicaMatriceFallback($meteo_status, $dificultate, $nivel_experienta);
        }
        
        return [
            'status' => $status,
            'meteo_status' => $meteo_status,
            'mesaj' => genereazaMesajDecizie($status, $context, $meteo_status, $dificultate),
            'nivel_aplicat' => $nivel,
            'sezon_aplicat' => $sezon,
            'dificultate_aplicata' => $dificultate
        ];
    }
}

/**
 * Mesaj simplu pentru decizie (fallback)
 */
if (!function_exists('getMesajDecizieSimple')) {
    function getMesajDecizieSimple($status) {
        $mesaje = [
            'VERDE' => 'Condițiile sunt favorabile pentru drumeție. Respectă regulile de siguranță și bucură-te de munte!',
            'GALBEN' => 'Condițiile necesită atenție sporită. Verifică echipamentul, informează pe cineva despre traseu și evaluează constant situația.',
            'ROSU' => 'Condițiile sunt nefavorabile sau periculoase. Recomandăm amânarea drumeției sau alegerea unui traseu mai sigur.'
        ];
        return $mesaje[$status] ?? 'Verifică condițiile înainte de plecare.';
    }
}

/**
 * Fallback simplu pentru matrice
 */
if (!function_exists('aplicaMatriceFallback')) {
    function aplicaMatriceFallback($meteo_status, $dificultate, $nivel) {
        // Logică simplă: meteo ROSU = ROSU, altfel depinde de dificultate și nivel
        if ($meteo_status === 'ROSU') {
            return [
                'status' => 'ROSU',
                'meteo_status' => $meteo_status,
                'mesaj' => 'Condițiile meteo sunt periculoase. Amânați drumeția.'
            ];
        }
        
        // Pentru meteo GALBEN
        if ($meteo_status === 'GALBEN') {
            if ($nivel === 'incepator' || $dificultate >= 4) {
                return [
                    'status' => 'ROSU',
                    'meteo_status' => $meteo_status,
                    'mesaj' => 'Combinația meteo + dificultate traseu este riscantă pentru nivelul dvs.'
                ];
            }
            return [
                'status' => 'GALBEN',
                'meteo_status' => $meteo_status,
                'mesaj' => 'Atenție sporită necesară. Evaluați constant condițiile.'
            ];
        }
        
        // Pentru meteo VERDE
        if ($dificultate >= 5 && $nivel === 'incepator') {
            return [
                'status' => 'ROSU',
                'meteo_status' => $meteo_status,
                'mesaj' => 'Traseu prea dificil pentru nivelul de experiență.'
            ];
        }
        
        if ($dificultate >= 4 && $nivel !== 'experimentat') {
            return [
                'status' => 'GALBEN',
                'meteo_status' => $meteo_status,
                'mesaj' => 'Traseu dificil - atenție sporită necesară.'
            ];
        }
        
        return [
            'status' => 'VERDE',
            'meteo_status' => $meteo_status,
            'mesaj' => 'Condiții favorabile pentru drumeție. Drumeție plăcută!'
        ];
    }
}

/**
 * Generează mesaj pentru decizie
 * Definită fără function_exists pentru a fi sigur că e folosită versiunea noastră
 */
function genereazaMesajDecizie($status, $context = [], $meteo_status = null, $dificultate = null) {
    $mesaje = [
        'VERDE' => 'Condițiile sunt favorabile pentru drumeție. Respectă regulile de siguranță și bucură-te de munte!',
        'GALBEN' => 'Condițiile necesită atenție sporită. Verifică echipamentul, informează pe cineva despre traseu și evaluează constant situația.',
        'ROSU' => 'Condițiile sunt nefavorabile sau periculoase. Recomandăm amânarea drumeției sau alegerea unui traseu mai sigur.'
    ];
    
    return $mesaje[$status] ?? 'Verifică condițiile înainte de plecare.';
}

/**
 * Generează lista de echipament recomandat
 */
if (!function_exists('genereazaEchipament')) {
    function genereazaEchipament($status, $sezon, $temperatura, $zapada_cm, $cod_vreme_rea = null) {
        $echipament = [
            '🥾 Bocanci montani impermeabili',
            '🎒 Rucsac cu husă de ploaie',
            '🗺️ Hartă + busolă / GPS',
            '🔦 Lanternă frontală',
            '📱 Telefon încărcat + baterie externă',
            '💧 Apă (min. 1.5L)',
            '🍫 Gustări energizante'
        ];
        
        // Echipament de iarnă
        if ($sezon === 'iarna' || $zapada_cm > 10 || $temperatura < 0) {
            $echipament = array_merge($echipament, [
                '❄️ Crampoane',
                '🪓 Piolet/Ceapcan',
                '🧤 Mănuși impermeabile + rezervă',
                '🧣 Fular/Buff protecție față',
                '🧥 Strat termic + jachetă iarnă',
                '🕶️ Ochelari de soare (protecție zăpadă)'
            ]);
        }
        
        // Echipament de vară
        if ($sezon === 'vara' && $temperatura > 20) {
            $echipament = array_merge($echipament, [
                '🧴 Cremă protecție solară',
                '🧢 Șapcă/Pălărie',
                '🦟 Spray anti-insecte'
            ]);
        }
        
        // Echipament pentru condiții dificile
        if ($status !== 'VERDE') {
            $echipament = array_merge($echipament, [
                '🆘 Pătură termică de urgență',
                '☕ Termos cu lichid cald',
                '🩹 Trusă prim ajutor',
                '🔥 Chibrituri impermeabile'
            ]);
        }
        
        // Cod vreme rea activ
        if ($cod_vreme_rea && $cod_vreme_rea['activ']) {
            $echipament = array_merge($echipament, [
                '📻 Radio/telefon pentru avertizări',
                '🦺 Vestă reflectorizantă'
            ]);
        }
        
        return array_unique($echipament);
    }
}

/**
 * Obține contact Salvamont
 */
if (!function_exists('getContactSalvamont')) {
    function getContactSalvamont($masiv) {
        $contacte = [
            'bucegi' => ['nume' => 'Salvamont Bușteni', 'telefon' => '0244-320444', 'judet' => 'Prahova'],
            'fagaras' => ['nume' => 'Salvamont Sibiu', 'telefon' => '0745-815920', 'judet' => 'Sibiu'],
            'retezat' => ['nume' => 'Salvamont Hunedoara', 'telefon' => '0722-242636', 'judet' => 'Hunedoara'],
            'piatra-craiului' => ['nume' => 'Salvamont Brașov', 'telefon' => '0268-471197', 'judet' => 'Brașov'],
            'ceahlau' => ['nume' => 'Salvamont Neamț', 'telefon' => '0233-218956', 'judet' => 'Neamț'],
            'rodnei' => ['nume' => 'Salvamont Maramureș', 'telefon' => '0262-221656', 'judet' => 'Maramureș'],
            'parang' => ['nume' => 'Salvamont Gorj', 'telefon' => '0253-212096', 'judet' => 'Gorj'],
            'calimani' => ['nume' => 'Salvamont Bistrița-Năsăud', 'telefon' => '0263-232925', 'judet' => 'Bistrița-Năsăud'],
            'apuseni' => ['nume' => 'Salvamont Bihor', 'telefon' => '0259-412769', 'judet' => 'Bihor'],
            'cozia' => ['nume' => 'Salvamont Vâlcea', 'telefon' => '0250-736956', 'judet' => 'Vâlcea'],
            'cindrel' => ['nume' => 'Salvamont Sibiu', 'telefon' => '0745-815920', 'judet' => 'Sibiu'],
            'iezer' => ['nume' => 'Salvamont Argeș', 'telefon' => '0248-221595', 'judet' => 'Argeș'],
            'tarcu' => ['nume' => 'Salvamont Caraș-Severin', 'telefon' => '0255-211876', 'judet' => 'Caraș-Severin'],
            'godeanu' => ['nume' => 'Salvamont Caraș-Severin', 'telefon' => '0255-211876', 'judet' => 'Caraș-Severin'],
            'ciucas' => ['nume' => 'Salvamont Brașov', 'telefon' => '0268-471197', 'judet' => 'Brașov'],
            'baiului' => ['nume' => 'Salvamont Brașov', 'telefon' => '0268-471197', 'judet' => 'Brașov'],
            'hasmas' => ['nume' => 'Salvamont Harghita', 'telefon' => '0266-371619', 'judet' => 'Harghita'],
            'bistritei' => ['nume' => 'Salvamont Suceava', 'telefon' => '0230-522024', 'judet' => 'Suceava'],
            'buila' => ['nume' => 'Salvamont Vâlcea', 'telefon' => '0250-736956', 'judet' => 'Vâlcea'],
            'maramuresului' => ['nume' => 'Salvamont Maramureș', 'telefon' => '0262-221656', 'judet' => 'Maramureș'],
            'mehedinti-cernei' => ['nume' => 'Salvamont Mehedinți', 'telefon' => '0252-316677', 'judet' => 'Mehedinți']
        ];
        
        $contact = $contacte[strtolower($masiv)] ?? null;
        
        if (!$contact) {
            return [
                'nume' => 'Salvamont România',
                'telefon' => '0SALVAMONT (0725-826668)',
                'telefon_urgenta' => '112',
                'nota' => 'Apelați 112 pentru urgențe'
            ];
        }
        
        $contact['telefon_urgenta'] = '112';
        return $contact;
    }
}

/**
 * Încarcă configurația stațiilor
 */
if (!function_exists('loadStatiiConfig')) {
    function loadStatiiConfig() {
        static $config = null;
        if ($config === null) {
            $path = __DIR__ . '/../config/statii-meteo.php';
            if (file_exists($path)) {
                $config = require $path;
            } else {
                $config = [];
            }
        }
        return $config;
    }
}

/**
 * Obține configurația pentru un masiv
 */
if (!function_exists('getStationConfig')) {
    function getStationConfig($masiv) {
        $config = loadStatiiConfig();
        return $config[strtolower($masiv)] ?? null;
    }
}

/**
 * Determină stația ANM
 */
if (!function_exists('getANMStation')) {
    function getANMStation($masiv, $altitudine_tinta) {
        $config = getStationConfig($masiv);
        if (!$config) return null;
        
        $prag = $config['prag_altitudine'] ?? 1800;
        
        if ($altitudine_tinta >= $prag) {
            return $config['anm_peste_prag'];
        }
        return $config['anm_sub_prag'];
    }
}

/**
 * Determină sursa Meteoblue
 */
if (!function_exists('getMeteoblueSource')) {
    function getMeteoblueSource($masiv, $altitudine_tinta) {
        $config = getStationConfig($masiv);
        if (!$config) return null;
        
        $prag = $config['prag_altitudine'] ?? 1800;
        
        if ($altitudine_tinta >= $prag) {
            return $config['meteoblue_peste_prag'];
        }
        return $config['meteoblue_sub_prag'];
    }
}

/**
 * Obține info despre sursa de date
 */
if (!function_exists('getSourceInfo')) {
    function getSourceInfo($masiv, $altitudine_tinta) {
        $config = getStationConfig($masiv);
        if (!$config) return null;
        
        $prag = $config['prag_altitudine'] ?? 1800;
        $peste_prag = $altitudine_tinta >= $prag;
        
        return [
            'anm_statie' => $peste_prag ? $config['anm_peste_prag'] : $config['anm_sub_prag'],
            'zona' => $peste_prag ? 'peste ' . $prag . 'm' : 'sub ' . $prag . 'm',
            'prag' => $prag,
            'peste_prag' => $peste_prag
        ];
    }
}
**
 * Analizează factorii evaluați și generează mesaj contextual dinamic
 * pentru context-card.js
 */
if (!function_exists('analizaContextDinamic')) {
    function analizaContextDinamic($factori, $meteo_status, $nivel_experienta = 'mediu', $altitudine_tinta = 1800) {
        // Colectează factorii periculoși
        $factori_critici = [];
        $factori_atentie = [];
        $factori_severi = []; // Pentru CAUTION dar foarte periculos (ex: avalanșă 3+)
        
        foreach ($factori as $nume_factor => $factor) {
            $nume_display = formatNumeFactor($nume_factor);
            
            if ($factor['status'] === 'ROSU') {
                $factori_critici[] = [
                    'nume' => $nume_display,
                    'detalii' => $factor['detalii']
                ];
            } elseif ($factor['status'] === 'GALBEN') {
                // SPECIAL: Avalanșă 3+ e foarte periculos chiar dacă e GALBEN
                if ($nume_factor === 'risc_avalansa') {
                    // Extrage nivelul din detalii (ex: "Risc 3/5")
                    if (preg_match('/Risc\s+(\d)/', $factor['detalii'], $matches)) {
                        $nivel_risc = intval($matches[1]);
                        if ($nivel_risc >= 3) {
                            $factori_severi[] = [
                                'nume' => $nume_display,
                                'detalii' => $factor['detalii']
                            ];
                        } else {
                            $factori_atentie[] = [
                                'nume' => $nume_display,
                                'detalii' => $factor['detalii']
                            ];
                        }
                    } else {
                        $factori_atentie[] = [
                            'nume' => $nume_display,
                            'detalii' => $factor['detalii']
                        ];
                    }
                } else {
                    $factori_atentie[] = [
                        'nume' => $nume_display,
                        'detalii' => $factor['detalii']
                    ];
                }
            }
        }
        
        // Determină mesajul principal și recomandările
        $conditii_text = '';
        $recomandari = [];
        
        // CAZ 1: Factori CRITICI (ROȘU)
        if (count($factori_critici) > 0) {
            $conditii_text = 'Condiții CRITICE - Pericole grave detectate';
            
            foreach ($factori_critici as $fc) {
                $recomandari[] = "⛔ {$fc['nume']}: {$fc['detalii']}";
            }
            
            $recomandari[] = "🚫 Amână drumeția sau alege un traseu alternativ la altitudine mai mică";
            $recomandari[] = "☎️ Verifică condițiile cu Salvamont înainte de plecare";
        }
        // CAZ 2: Factori SEVERI (avalanșă 3+) SAU 2+ factori GALBEN
        elseif (count($factori_severi) > 0 || count($factori_atentie) >= 2) {
            $conditii_text = 'Condiții DIFICILE - Necesită experiență și precauție sporită';
            
            // Listează factorii severi mai întâi
            foreach ($factori_severi as $fs) {
                $recomandari[] = "⚠️ {$fs['nume']}: {$fs['detalii']}";
            }
            
            // Apoi factorii de atenție
            foreach ($factori_atentie as $fa) {
                $recomandari[] = "⚠️ {$fa['nume']}: {$fa['detalii']}";
            }
            
            // Recomandări specifice pe nivel experiență
            if ($nivel_experienta === 'incepator') {
                $recomandari[] = "👥 Nivel începător: mergi DOAR cu ghid montan sau grup experimentat";
                $recomandari[] = "🔄 Alternativ: alege trasee marcate la altitudine sub 1500m";
            } else {
                $recomandari[] = "👥 Mergi în grup de minim 3 persoane";
                $recomandari[] = "📱 Informează pe cineva despre traseu și oră estimată de sosire";
            }
            
            $recomandari[] = "🔄 Fii pregătit să renunți dacă condițiile se înrăutățesc pe traseu";
        }
        // CAZ 3: UN singur factor GALBEN
        elseif (count($factori_atentie) === 1) {
            $conditii_text = 'Condiții ACCEPTABILE cu un factor de atenție';
            
            $fa = $factori_atentie[0];
            $recomandari[] = "⚠️ {$fa['nume']}: {$fa['detalii']}";
            $recomandari[] = "✅ Restul condițiilor sunt favorabile";
            $recomandari[] = "👁️ Monitorizează acest factor pe parcursul traseului";
        }
        // CAZ 4: TOTUL OK (VERDE)
        else {
            $conditii_text = 'Condiții BUNE pentru drumeție';
            $recomandari[] = "✅ Toate condițiile meteo sunt favorabile";
            $recomandari[] = "🎯 Respectă în continuare regulile de siguranță în munte";
            $recomandari[] = "📱 Ține telefonul încărcat pentru eventuale urgențe";
            
            if ($altitudine_tinta > 2000) {
                $recomandari[] = "⛰️ Altitudine {$altitudine_tinta}m: condițiile se pot schimba rapid";
            }
        }
        
        return [
            'conditii_text' => $conditii_text,
            'recomandari' => $recomandari,
            'factori_critici_count' => count($factori_critici),
            'factori_atentie_count' => count($factori_atentie),
            'factori_severi_count' => count($factori_severi)
        ];
    }
}

/**
 * Formatează numele factorului pentru afișare
 */
if (!function_exists('formatNumeFactor')) {
    function formatNumeFactor($nume_factor) {
        $mapping = [
            'stres_termic' => 'Stres Termic (Windchill)',
            'vant' => 'Vânt',
            'vizibilitate' => 'Vizibilitate',
            'precipitatii_ninsoare' => 'Ninsoare',
            'precipitatii_ploaie' => 'Ploaie',
            'precipitatii_lapovita' => 'Lapoviță',
            'precipitatii_inghet' => 'Chiciură/Polei',
            'instabilitate_atmosferica' => 'Risc Furtuni',
            'stare_sol' => 'Starea Solului',
            'durata_expunere' => 'Durată Expunere',
            'schimbari_rapide' => 'Schimbări Meteo Rapide',
            'risc_avalansa' => 'Risc Avalanșă'
        ];
        
        return $mapping[$nume_factor] ?? ucfirst(str_replace('_', ' ', $nume_factor));
    }
}
