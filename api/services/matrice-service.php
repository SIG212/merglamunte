<?php
/**
 * Serviciu aplicare matrice risc
 * Combină: nivel experiență + sezon + dificultate + meteo → Decizie finală
 */

require_once __DIR__ . '/../utils/helpers.php';

/**
 * Aplică matricea de risc și returnează decizia finală
 */
function aplicareMatriceRisc($nivel_experienta, $context_traseu, $meteo_status) {
    $matrice = loadConfig('matrice-risc');
    
    $sezon = $context_traseu['sezon'];
    $dificultate = $context_traseu['dificultate'];
    
    // Lookup în matrice
    if (!isset($matrice[$nivel_experienta][$sezon][$dificultate][$meteo_status])) {
        throw new Exception("Combinație invalidă în matricea de risc");
    }
    
    $status_final = $matrice[$nivel_experienta][$sezon][$dificultate][$meteo_status];
    
    // Generează mesaj personalizat
    $mesaj = genereazaMesajDecizieDetaliat(
        $status_final,
        $nivel_experienta,
        $context_traseu,
        $meteo_status
    );
    
    return [
        'status' => $status_final,
        'nivel_experienta' => $nivel_experienta,
        'sezon' => $sezon,
        'dificultate' => $dificultate,
        'meteo_status' => $meteo_status,
        'mesaj' => $mesaj,
        'motiv_principal' => determinaMotivPrincipal($status_final, $nivel_experienta, $dificultate, $meteo_status)
    ];
}

/**
 * Generează mesaj personalizat detaliat pentru decizie
 * (diferit de genereazaMesajDecizie din helpers.php care e simplu)
 */
if (!function_exists('genereazaMesajDecizieDetaliat')) {
    function genereazaMesajDecizieDetaliat($status_final, $nivel, $context, $meteo_status) {
        $masiv = $context['masiv_display'] ?? 'munte';
        $altitudine = $context['altitudine_tinta'] ?? 1800;
        $dificultate = $context['dificultate'] ?? $context['dificultate_grad'] ?? 3;
        $sezon = $context['sezon'] ?? 'vara';
        $zona = $context['zona'] ?? 'sub_prag';
        
        // Mesaje pentru ROȘU
        if ($status_final === 'ROSU') {
            if ($meteo_status === 'ROSU') {
                return "🔴 Condițiile meteo sunt foarte periculoase. Amână drumeția pentru o zi cu vreme stabilă.";
            }
            
            if ($nivel === 'incepator') {
                if ($dificultate >= 4) {
                    if ($zona === 'peste_prag') {
                        return "🔴 Un traseu în $masiv, peste limita de sus a pădurii, este prea dificil pentru începători. Alege un traseu mai ușor sau rămâi sub altitudinea golului alpin.";
                    } else {
                        return "🔴 Acest traseu depășește nivelul pentru începători. Recomandăm trasee mai ușoare și sigure.";
                    }
                }
                
                if ($sezon === 'iarna' && $dificultate >= 3) {
                    return "🔴 Traseele de iarnă la această altitudine necesită experiență avansată în drumețiile de iarnă și echipament tehnic complet (crampoane, ceapcan, piolet). Recomandăm trasee mai simple sau o drumeție vara.";
                }
            }
            
            if ($nivel === 'mediu') {
                if ($sezon === 'iarna' && $dificultate >= 4) {
                    if ($zona === 'peste_prag') {
                        return "🔴 Un traseu în $masiv, în golul alpin, iarna, necesită experiență de alpinism avansat. Condițiile depășesc nivelul mediu.";
                    } else {
                        return "🔴 Traseul de iarnă la această altitudine necesită experiență avansată. Ia în considerare un traseu mai ușor sau amână pentru condiții mai bune.";
                    }
                }
            }
            
            return "🔴 Combinația de factori (altitudine, sezon $sezon, condiții meteo) este prea periculoasă pentru nivelul tău. Alege o alternativă mai ușoară și sigură.";
        }
        
        // Mesaje pentru GALBEN
        if ($status_final === 'GALBEN') {
            if ($nivel === 'incepator' && $dificultate === 3) {
                return "🟡 Traseul va fi dificil și vă va pune la încercare. Asigură-te că aveți condiție fizică bună și echipament complet (vezi lista mai jos). Pleacă devreme, verifică vremea, estimează corect durata, păstrează drumeția cât mai sigură.";
            }
            
            if ($nivel === 'mediu') {
                if ($dificultate >= 4) {
                    return "🟡 Traseul necesită experiență și echipament adecvat. Verifică prognoza des și pregătește plan de retragere în caz de înrăutățire a condițiilor.";
                }
                
                if ($sezon === 'iarna') {
                    return "🟡 Condiții de iarnă - echipament de iarnă necesar (frig, zăpadă, viscol). Monitorizează constant evoluția meteo.";
                }
            }
            
            if ($nivel === 'experimentat') {
                return "🟡 Condiții dificile chiar pentru experți. Informare Salvamont recomandată. Echipament complet și plan detaliat obligatorii.";
            }
            
            return "🟡 Condițiile necesită atenție sporită. Echipament complet, verificări constante, și plan de retragere pregătit.";
        }
        
        // Mesaje pentru VERDE
        if ($nivel === 'incepator') {
            return "✅ Condiții bune pentru începători. Respectă regulile de bază: apă suficientă, telefon încărcat, informează pe cineva despre traseu.";
        }
        
        if ($nivel === 'mediu') {
            return "✅ Condiții favorabile pentru drumeție. Rămâi atent la evoluția meteo și respectă programul planificat.";
        }
        
        return "✅ Condiții bune. Rămâi atent la evoluția meteo, respectă programul planificat și păstrează drumeția sigură.";
    }
}

/**
 * Determină motivul principal pentru decizie
 */
if (!function_exists('determinaMotivPrincipal')) {
    function determinaMotivPrincipal($status_final, $nivel, $dificultate, $meteo_status) {
        if ($status_final !== 'ROSU') {
            return null;
        }
        
        if ($meteo_status === 'ROSU') {
            return 'meteo_periculos';
        }
        
        if ($nivel === 'incepator' && $dificultate >= 4) {
            return 'traseu_prea_dificil';
        }
        
        if ($nivel === 'incepator' && $dificultate >= 3) {
            return 'traseu_iarna_nepotrivit';
        }
        
        if ($nivel === 'mediu' && $dificultate >= 4) {
            return 'combinatie_dificila';
        }
        
        return 'conditii_generale_periculoase';
    }
}

if (!function_exists('aplicaMatrice')) {
    /**
     * Alias pentru aplicareMatriceRisc (pentru compatibilitate cu analiza-v2/v3)
     */
    function aplicaMatrice($nivel_experienta, $sezon, $dificultate, $meteo_status, $context = []) {
        // Construiește context_traseu dacă nu e deja format complet
        if (!isset($context['sezon'])) {
            $context['sezon'] = $sezon;
        }
        if (!isset($context['dificultate'])) {
            $context['dificultate'] = $dificultate;
        }
        if (!isset($context['dificultate_grad'])) {
            $context['dificultate_grad'] = $dificultate;
        }
        
        $result = aplicareMatriceRisc($nivel_experienta, $context, $meteo_status);
        
        // Add 'cod' alias pentru compatibilitate (analiza-v2/v3 folosesc 'cod', nu 'status')
        $result['cod'] = $result['status'];
        
        return $result;
    }
}
