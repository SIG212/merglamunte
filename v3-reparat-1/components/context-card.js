/**
 * Context Card Component
 * Afișează informații despre context traseu (doar pentru CAUTION/NO-GO)
 */

class ContextCard extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this._data = null;
        this._show = false;
    }

    set data(value) {
        this._data = value;
        this.render();
    }

    get data() {
        return this._data;
    }

    set show(value) {
        this._show = value;
        this.render();
    }

    get show() {
        return this._show;
    }

    connectedCallback() {
        this.render();
    }

    render() {
        if (!this._data || !this._show) {
            this.shadowRoot.innerHTML = '';
            return;
        }

        const context = this._data;
        const zonaDisplay = context.zona === 'peste_prag' 
            ? `Peste ${context.altitudine_prag}m (zonă alpină)` 
            : `Sub ${context.altitudine_prag}m`;

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
                    font-size: 1.25rem;
                    font-weight: 700;
                    color: #0f172a;
                    margin: 0 0 1rem 0;
                }
                .content {
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                }
                .info-box {
                    padding: 1rem;
                    border-radius: 0.5rem;
                    border: 1px solid;
                }
                .info-box.neutral {
                    background: #f8fafc;
                    border-color: #e2e8f0;
                }
                .info-box.blue {
                    background: #eff6ff;
                    border-color: #bfdbfe;
                }
                .info-text {
                    font-size: 0.875rem;
                    color: #475569;
                    margin: 0;
                }
                .info-text strong {
                    font-weight: 600;
                }
                .caracteristici-title {
                    font-size: 0.875rem;
                    font-weight: 700;
                    color: #1e40af;
                    margin: 0 0 0.5rem 0;
                }
                .badges {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.5rem;
                }
                .badge {
                    font-size: 0.75rem;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.25rem;
                }
                .badge.blue {
                    background: #dbeafe;
                    color: #1e40af;
                }
                .badge.red {
                    background: #fee2e2;
                    color: #991b1b;
                }
                .badge.amber {
                    background: #fef3c7;
                    color: #92400e;
                }
                .badge.cyan {
                    background: #cffafe;
                    color: #0e7490;
                }
            </style>
            
            <div class="container">
                <h2>Descriere Traseu</h2>
                
                <div class="content">
                    <div class="info-box neutral">
                        <p class="info-text">
                            <strong>${context.masiv_display}</strong> · 
                            <span>${zonaDisplay}</span> · 
                            <span style="text-transform: capitalize;">${context.sezon}</span>
                        </p>
                    </div>
                    
                    ${context.caracteristici ? `
                    <div class="info-box blue">
                        <h4 class="caracteristici-title">📍 Caracteristici zonă</h4>
                        <div class="badges">
                            ${context.caracteristici.zone_expuse ? '<span class="badge blue">Zone expuse</span>' : ''}
                            ${context.caracteristici.avalanse ? '<span class="badge red">Risc avalanșe</span>' : ''}
                            ${!context.caracteristici.surse_apa ? '<span class="badge amber">Fără surse apă</span>' : ''}
                            ${context.caracteristici.zapada_problematica ? '<span class="badge cyan">Zăpadă problematică</span>' : ''}
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
}

customElements.define('context-card', ContextCard);
