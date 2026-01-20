/**
 * Predictability Banner Component
 * Afișează banner cu informații despre predictibilitatea prognozei
 */

class PredictabilityBanner extends HTMLElement {
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

    calculeazaZileAvans(dataSelectata) {
        const azi = new Date();
        azi.setHours(0, 0, 0, 0);
        const dataCeruta = new Date(dataSelectata);
        dataCeruta.setHours(0, 0, 0, 0);
        return Math.round((dataCeruta - azi) / (1000 * 60 * 60 * 24));
    }

    render() {
        if (!this._data || !this._data.meteo.predictibilitate || this._data.meteo.tip !== 'prognoza') {
            this.shadowRoot.innerHTML = '';
            return;
        }

        const pred = parseInt(this._data.meteo.predictibilitate);
        const zileAvans = this.calculeazaZileAvans(this._data.data);

        let mesaj = '';
        let arata = false;

        if (zileAvans === 1) {
            mesaj = `Prognoză meteo cu ${this._data.meteo.predictibilitate} predictibilitate`;
            arata = pred < 90;
        } else if (zileAvans === 2) {
            mesaj = `Prognoză la 2 zile - ${this._data.meteo.predictibilitate} predictibilitate`;
            arata = true;
        } else if (zileAvans >= 3) {
            mesaj = `Prognoză la ${zileAvans} zile - ${this._data.meteo.predictibilitate} predictibilitate. Revino cu o zi înainte pentru date mai precise.`;
            arata = true;
        }

        if (!arata) {
            this.shadowRoot.innerHTML = '';
            return;
        }

        const isGood = pred >= 85;
        const bgColor = isGood ? '#eff6ff' : '#fffbeb';
        const borderColor = isGood ? '#60a5fa' : '#fbbf24';
        const textColor = isGood ? '#1e40af' : '#92400e';
        const icon = isGood ? 'ℹ️' : '⚠️';

        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                }
                .banner {
                    padding: 1rem;
                    margin: 1rem 0;
                    border-radius: 0 0.5rem 0.5rem 0;
                    border-left: 4px solid ${borderColor};
                    background: ${bgColor};
                }
                .content {
                    display: flex;
                    align-items: flex-start;
                    gap: 0.75rem;
                }
                .icon {
                    font-size: 1.5rem;
                }
                .message {
                    font-size: 0.875rem;
                    font-weight: 500;
                    color: ${textColor};
                    margin: 0;
                    line-height: 1.5;
                }
            </style>
            
            <div class="banner">
                <div class="content">
                    <span class="icon">${icon}</span>
                    <p class="message">${mesaj}</p>
                </div>
            </div>
        `;
    }
}

customElements.define('predictability-banner', PredictabilityBanner);
