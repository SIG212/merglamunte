/**
 * Meteo Details Component
 * Afișează detaliile meteo într-un card swipeable
 */

class MeteoDetails extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this._data = null;
    }

    set data(value) {
        this._data = value;
        this.render();
    }

    get data() {
        return this._data;
    }

    connectedCallback() {
        this.render();
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const months = ['ian', 'feb', 'mar', 'apr', 'mai', 'iun', 'iul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    render() {
        if (!this._data) {
            this.shadowRoot.innerHTML = '<div class="p-4 text-slate-500">Se încarcă...</div>';
            return;
        }

        const meteo = this._data.meteo;
        const meteoData = meteo.date_curente || meteo.date_prognozate;
        const dataSelectata = this._data.data;

        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                }
                .weather-cards-container {
                    display: flex;
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: none;
                    gap: 1rem;
                }
                .weather-cards-container::-webkit-scrollbar {
                    display: none;
                }
                .weather-card {
                    scroll-snap-align: start;
                    flex: 0 0 calc(100% - 2rem);
                    min-width: 280px;
                }
                @media (min-width: 640px) {
                    .weather-card {
                        flex: 0 0 calc(50% - 0.5rem);
                    }
                }
                .card-container {
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
                    margin-bottom: 1rem;
                }
                h3 {
                    font-weight: 700;
                    color: #0f172a;
                    margin-bottom: 0.75rem;
                }
                .row {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 0.5rem 0;
                    border-bottom: 1px solid #e2e8f0;
                }
                .row:last-child {
                    border-bottom: none;
                }
                .label {
                    color: #475569;
                    font-weight: 500;
                    font-size: 0.875rem;
                }
                .value {
                    font-weight: 700;
                    color: #0f172a;
                }
                .value-small {
                    font-size: 0.75rem;
                    color: #64748b;
                    font-weight: 400;
                }
                .card-primary {
                    background: white;
                    border: 2px solid #bfdbfe;
                    border-radius: 0.75rem;
                    padding: 1.25rem;
                    box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
                }
                .card-secondary {
                    background: #f8fafc;
                    border: 2px solid #e2e8f0;
                    border-radius: 0.75rem;
                    padding: 1.25rem;
                    box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
                }
            </style>
            
            <div class="card-container">
                <h2>Detalii Meteo</h2>
                
                <div class="weather-cards-container">
                    <!-- Card 1: Data selectată -->
                    <div class="weather-card card-primary">
                        <h3>📅 ${this.formatDate(dataSelectata)}</h3>
                        <div>
                            <div class="row">
                                <span class="label">🌡️ Temperatură</span>
                                <span class="value">
                                    ${Math.round(meteoData.temperatura || meteoData.temperatura_medie)}°C
                                    ${meteoData.temperatura_min !== undefined ? 
                                        `<span class="value-small">(${Math.round(meteoData.temperatura_min)}° / ${Math.round(meteoData.temperatura_max)}°)</span>` : ''}
                                </span>
                            </div>
                            <div class="row">
                                <span class="label">🥶 Windchill</span>
                                <span class="value">${Math.round(meteo.windchill)}°C</span>
                            </div>
                            <div class="row">
                                <span class="label">💨 Vânt</span>
                                <span class="value">
                                    ${Math.round(meteoData.vant_kmh || meteoData.vant_mediu_kmh)} km/h
                                    ${meteoData.vant_rafale_kmh ? 
                                        `<span class="value-small">(rafale ${Math.round(meteoData.vant_rafale_kmh)})</span>` : ''}
                                </span>
                            </div>
                            <div class="row">
                                <span class="label">☁️ Cer</span>
                                <span class="value" style="text-transform: capitalize;">${meteoData.nebulozitate || 'N/A'}</span>
                            </div>
                            <div class="row">
                                <span class="label">🌧️ Precipitații</span>
                                <span class="value">
                                    ${meteoData.precipitatii_mm ? 
                                        `${meteoData.precipitatii_mm} mm (${meteoData.tip_precipitatii})` : 
                                        (meteoData.fenomen || 'Fără')}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Sursa -->
                    <div class="weather-card card-secondary">
                        <h3>📊 Sursa Date</h3>
                        <div>
                            <div class="row">
                                <span class="label">📡 Furnizor</span>
                                <span class="value">${meteo.sursa}</span>
                            </div>
                            <div class="row">
                                <span class="label">📅 Tip</span>
                                <span class="value">${meteo.tip === 'actual' ? 'Măsurători actuale' : 'Prognoză meteo'}</span>
                            </div>
                            ${meteo.statie ? `
                            <div class="row">
                                <span class="label">📍 Stație</span>
                                <span class="value">${meteo.statie}</span>
                            </div>
                            ` : ''}
                            ${meteo.predictibilitate ? `
                            <div class="row">
                                <span class="label">🎯 Precizie</span>
                                <span class="value">${meteo.predictibilitate}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

customElements.define('meteo-details', MeteoDetails);
