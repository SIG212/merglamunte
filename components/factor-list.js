/**
 * Factor List Component - V2
 * Afișează lista de factori meteo evaluați
 * Sortat: ROȘU → GALBEN → VERDE
 */

class FactorList extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this._factors = null;
        this._showOk = false;
    }

    set factors(value) {
        this._factors = value;
        this.render();
    }

    get factors() {
        return this._factors;
    }

    static get factorNames() {
        return {
            // Factori meteo (Meteoblue - prognoză)
            'temperatura': 'Temperatură',
            'temperatura_real_feel': 'Temperatură Resimțită',
            'stres_termic': 'Stres Termic',
            'vant': 'Vânt',
            'vizibilitate_prognoza': 'Vizibilitate (prognoză)',
            'precipitatii_max_ora': 'Precipitații Max/Oră',
            'precipitatii_24h': 'Precipitații 24h',
            'tip_precipitatii': 'Tip Precipitații',
            'instabilitate': 'Instabilitate Atmosferică',
            
            // Factori actuali (ANM - prezent)
            'vizibilitate_actual': 'Vizibilitate (actuală)',
            'grosime_zapada': 'Grosime Strat Zăpadă',
            'stare_sol': 'Stare Sol',
            
            // Buletin nivologic
            'avalansa': 'Risc Avalanșă',
            'detalii_zapada': 'Detalii Strat Zăpadă',
            
            // Avertizări
            'cod_vreme_rea': 'Cod Vreme Rea',
            
            // Legacy (backward compatibility)
            'vizibilitate': 'Vizibilitate',
            'precipitatii': 'Precipitații',
            'ninsoare': 'Ninsoare',
            'durata_expunere': 'Durata Expunerii',
            'schimbari_rapide': 'Schimbări Rapide'
        };
    }

    static get factorIcons() {
        return {
            'temperatura': '🌡️',
            'temperatura_real_feel': '🌡️',
            'stres_termic': '❄️',
            'vant': '💨',
            'vizibilitate_prognoza': '👁️',
            'vizibilitate_actual': '👁️',
            'vizibilitate': '👁️',
            'precipitatii_max_ora': '🌧️',
            'precipitatii_24h': '🌧️',
            'precipitatii': '🌧️',
            'tip_precipitatii': '🌨️',
            'ninsoare': '🌨️',
            'instabilitate': '⛈️',
            'grosime_zapada': '❄️',
            'stare_sol': '🏔️',
            'avalansa': '⚠️',
            'detalii_zapada': '❄️',
            'cod_vreme_rea': '🚨',
            'durata_expunere': '⏱️',
            'schimbari_rapide': '🔄'
        };
    }

    static get statusConfig() {
        return {
            'GO': { bg: '#22c55e', text: 'white', label: 'OK', border: '#16a34a' },
            'CAUTION': { bg: '#eab308', text: '#0f172a', label: 'ATENȚIE', border: '#ca8a04' },
            'NO-GO': { bg: '#ef4444', text: 'white', label: 'PERICOL', border: '#dc2626' }
        };
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const months = ['ian', 'feb', 'mar', 'apr', 'mai', 'iun', 'iul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const months = ['ian', 'feb', 'mar', 'apr', 'mai', 'iun', 'iul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${date.getDate()} ${months[date.getMonth()]}, ${hours}:${minutes}`;
    }

    toggleOkFactors() {
        this._showOk = !this._showOk;
        this.render();
    }

    connectedCallback() {
        this.render();
    }

    renderFactor(key, factor) {
        const config = FactorList.statusConfig[factor.status] || FactorList.statusConfig['GO'];
        const name = FactorList.factorNames[key] || key.replace(/_/g, ' ');
        const icon = FactorList.factorIcons[key] || '📊';

        // Construiește metadatele (timestamp, validitate, sursă)
        let metaHtml = '';
        
        // Timestamp măsurătoare (pentru date actuale ANM)
        if (factor.masurat_la) {
            metaHtml += `<span class="factor-meta-item">📍 Măsurat: ${this.formatDateTime(factor.masurat_la)}</span>`;
        }
        
        // Validitate (pentru buletin nivologic)
        if (factor.valabilitate) {
            if (factor.valabilitate.de_la && factor.valabilitate.pana_la) {
                metaHtml += `<span class="factor-meta-item">📅 Valabil: ${this.formatDate(factor.valabilitate.de_la)} - ${this.formatDate(factor.valabilitate.pana_la)}</span>`;
            } else if (factor.valabilitate.pana_la) {
                metaHtml += `<span class="factor-meta-item">📅 Valabil până la: ${this.formatDateTime(factor.valabilitate.pana_la)}</span>`;
            }
        }
        
        // Legacy validitate
        if (factor.validitate && !factor.valabilitate) {
            metaHtml += `<span class="factor-meta-item">📅 Valabil: ${this.formatDate(factor.validitate.de_la)} - ${this.formatDate(factor.validitate.pana_la)}</span>`;
        }
        
        // Sursă date
        if (factor.sursa) {
            metaHtml += `<span class="factor-meta-item">ℹ️ Sursă: ${factor.sursa}</span>`;
        }

        // Detalii extinse (pentru detalii_zapada)
        let detaliiHtml = '';
        if (factor.detalii_extinse) {
            detaliiHtml = `<div class="factor-detalii">${factor.detalii_extinse}</div>`;
        }

        // Pericole (pentru avalanșă/zăpadă)
        let pericoleHtml = '';
        if (factor.pericole && factor.pericole.length > 0) {
            const pericoleLabels = {
                'placi_vant': 'Plăci de vânt',
                'cornise': 'Cornișe',
                'strat_slab': 'Strat slab',
                'supraincarcari': 'Supraîncărcări',
                'avalanse_spontane': 'Avalanșe spontane'
            };
            const badges = factor.pericole.map(p => 
                `<span class="pericol-badge">${pericoleLabels[p] || p}</span>`
            ).join('');
            pericoleHtml = `<div class="factor-pericole">${badges}</div>`;
        }

        return `
            <div class="factor-item factor-${factor.status.toLowerCase().replace('-', '')}">
                <div class="factor-header">
                    <div class="factor-title">
                        <span class="factor-icon">${icon}</span>
                        <h3 class="factor-name">${name}</h3>
                    </div>
                    <span class="factor-badge" style="background-color: ${config.bg}; color: ${config.text}; border-color: ${config.border};">
                        ${config.label}
                    </span>
                </div>
                <p class="factor-value">${factor.valoare}</p>
                <p class="factor-mesaj">${factor.mesaj}</p>
                ${detaliiHtml}
                ${pericoleHtml}
                ${metaHtml ? `<div class="factor-meta">${metaHtml}</div>` : ''}
            </div>
        `;
    }

    render() {
        if (!this._factors) {
            this.shadowRoot.innerHTML = '<div style="padding: 1rem; color: #64748b;">Se încarcă...</div>';
            return;
        }

        const entries = Object.entries(this._factors);
        const redFactors = entries.filter(([_, f]) => f.status === 'NO-GO');
        const yellowFactors = entries.filter(([_, f]) => f.status === 'CAUTION');
        const okFactors = entries.filter(([_, f]) => f.status === 'GO');

        const hasRisks = redFactors.length > 0 || yellowFactors.length > 0;

        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                }
                .container {
                    background: white;
                    border-radius: 0.75rem;
                    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                    border: 1px solid #e2e8f0;
                    padding: 1.5rem;
                }
                h2 {
                    font-size: 1.125rem;
                    font-weight: 700;
                    color: #0f172a;
                    margin: 0 0 1rem 0;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                .factors-list {
                    display: flex;
                    flex-direction: column;
                    gap: 0.75rem;
                }
                .factor-item {
                    padding: 1rem;
                    border-radius: 0.5rem;
                    border-left: 4px solid #e2e8f0;
                    background: #f8fafc;
                    transition: all 0.15s;
                }
                .factor-item:hover {
                    background: #f1f5f9;
                }
                .factor-nogo {
                    border-left-color: #ef4444;
                    background: #fef2f2;
                }
                .factor-nogo:hover {
                    background: #fee2e2;
                }
                .factor-caution {
                    border-left-color: #eab308;
                    background: #fefce8;
                }
                .factor-caution:hover {
                    background: #fef9c3;
                }
                .factor-go {
                    border-left-color: #22c55e;
                    background: #f0fdf4;
                }
                .factor-go:hover {
                    background: #dcfce7;
                }
                .factor-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 0.75rem;
                    margin-bottom: 0.5rem;
                }
                .factor-title {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                .factor-icon {
                    font-size: 1.125rem;
                }
                .factor-name {
                    font-size: 0.875rem;
                    font-weight: 600;
                    color: #334155;
                    margin: 0;
                }
                .factor-badge {
                    font-size: 0.6875rem;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.25rem;
                    font-weight: 700;
                    flex-shrink: 0;
                    text-transform: uppercase;
                    letter-spacing: 0.025em;
                    border: 1px solid;
                }
                .factor-value {
                    font-size: 1.125rem;
                    font-weight: 700;
                    color: #0f172a;
                    margin: 0 0 0.25rem 0;
                }
                .factor-mesaj {
                    font-size: 0.875rem;
                    color: #475569;
                    line-height: 1.5;
                    margin: 0;
                }
                .factor-detalii {
                    font-size: 0.8125rem;
                    color: #64748b;
                    line-height: 1.6;
                    margin-top: 0.5rem;
                    padding: 0.5rem;
                    background: rgba(255,255,255,0.5);
                    border-radius: 0.25rem;
                }
                .factor-pericole {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.375rem;
                    margin-top: 0.5rem;
                }
                .pericol-badge {
                    font-size: 0.6875rem;
                    padding: 0.125rem 0.375rem;
                    background: #fef3c7;
                    color: #92400e;
                    border-radius: 0.25rem;
                    font-weight: 500;
                }
                .factor-meta {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.75rem;
                    margin-top: 0.5rem;
                    padding-top: 0.5rem;
                    border-top: 1px solid rgba(0,0,0,0.05);
                }
                .factor-meta-item {
                    font-size: 0.75rem;
                    color: #64748b;
                }
                .no-risks {
                    padding: 1.5rem;
                    text-align: center;
                    color: #16a34a;
                    background: #f0fdf4;
                    border-radius: 0.5rem;
                    font-weight: 500;
                }
                .ok-section {
                    margin-top: 0.75rem;
                }
                .toggle-btn {
                    width: 100%;
                    margin-top: 1rem;
                    padding: 0.75rem 1rem;
                    font-size: 0.875rem;
                    color: #475569;
                    background: #f1f5f9;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.5rem;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                    transition: all 0.15s;
                    font-weight: 500;
                }
                .toggle-btn:hover {
                    background: #e2e8f0;
                    color: #0f172a;
                }
                .toggle-icon {
                    transition: transform 0.2s;
                    ${this._showOk ? 'transform: rotate(180deg);' : ''}
                }
                .section-divider {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    margin: 1rem 0 0.75rem 0;
                    color: #94a3b8;
                    font-size: 0.75rem;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                }
                .section-divider::before,
                .section-divider::after {
                    content: '';
                    flex: 1;
                    height: 1px;
                    background: #e2e8f0;
                }
            </style>
            
            <div class="container">
                <h2>📊 Evaluare Factori</h2>
                
                <div class="factors-list">
                    ${redFactors.length > 0 ? `
                        ${redFactors.map(([k, f]) => this.renderFactor(k, f)).join('')}
                    ` : ''}
                    
                    ${yellowFactors.length > 0 ? `
                        ${redFactors.length > 0 ? '<div class="section-divider">Atenție</div>' : ''}
                        ${yellowFactors.map(([k, f]) => this.renderFactor(k, f)).join('')}
                    ` : ''}
                </div>
                
                ${!hasRisks ? `
                    <div class="no-risks">✅ Toți factorii meteo sunt favorabili</div>
                ` : ''}
                
                ${this._showOk && okFactors.length > 0 ? `
                    <div class="ok-section">
                        <div class="section-divider">Factori OK</div>
                        <div class="factors-list">
                            ${okFactors.map(([k, f]) => this.renderFactor(k, f)).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${okFactors.length > 0 ? `
                    <button class="toggle-btn" id="toggleBtn">
                        <span>${this._showOk ? 'Ascunde factorii OK' : `Vezi toți factorii (${okFactors.length} OK)`}</span>
                        <svg class="toggle-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                ` : ''}
            </div>
        `;

        // Add event listener for toggle button
        const toggleBtn = this.shadowRoot.getElementById('toggleBtn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleOkFactors());
        }
    }
}

customElements.define('factor-list', FactorList);