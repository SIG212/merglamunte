/**
 * Verdict Card Component
 * Afișează decizia finală GO/CAUTION/NO-GO
 */

class VerdictCard extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
    }

    static get observedAttributes() {
        return ['status', 'mesaj', 'nivel', 'meteo-status', 'dificultate', 'altitudine', 'masiv'];
    }

    connectedCallback() {
        this.render();
    }

    attributeChangedCallback() {
        this.render();
    }

    getConfig() {
        return {
            'VERDE': {
                bgGradient: 'linear-gradient(to bottom right, #f0fdf4, #dcfce7)',
                borderColor: '#86efac',
                iconBg: '#16a34a',
                title: 'Condiții bune'
            },
            'GALBEN': {
                bgGradient: 'linear-gradient(to bottom right, #fffbeb, #fef3c7)',
                borderColor: '#fbbf24',
                iconBg: '#f59e0b',
                title: 'Condiții dificile'
            },
            'ROSU': {
                bgGradient: 'linear-gradient(to bottom right, #fef2f2, #fee2e2)',
                borderColor: '#f87171',
                iconBg: '#dc2626',
                title: 'Condiții periculoase'
            }
        };
    }

    getIcon(status) {
        const icons = {
            'VERDE': `<svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`,
            'GALBEN': `<svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
            'ROSU': `<svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`
        };
        return icons[status] || icons['GALBEN'];
    }

    getNivelDisplay() {
        const nivele = {
            'incepator': 'Începător',
            'mediu': 'Mediu', 
            'experimentat': 'Experimentat'
        };
        return nivele[this.getAttribute('nivel')] || 'N/A';
    }

    getMeteoStatusConfig() {
        const configs = {
            'VERDE': { bg: '#006C2E', text: '#ffffff', label: 'Condiții meteo bune' },
            'GALBEN': { bg: '#E9D502', text: '#000000', label: 'Condiții meteo dificile' },
            'ROSU': { bg: '#fee2e2', text: '#b91c1c', label: 'Condiții meteo periculoase' }
        };
        return configs[this.getAttribute('meteo-status')] || { bg: '#f1f5f9', text: '#475569', label: 'N/A' };
    }

    getTraseuDescription() {
        const dif = parseInt(this.getAttribute('dificultate')) || 1;
        const masiv = this.getAttribute('masiv') || 'munte';
        const altitudine = this.getAttribute('altitudine') || '';
        
        let descriere = '';
        let config = { bg: '#f1f5f9', text: '#475569' };
        
        if (dif <= 2) {
            descriere = `Traseu accesibil în ${masiv} la ${altitudine}m`;
            config = { bg: '#006C2E', text: '#ffffff' };
        } else if (dif === 3) {
            descriere = `Traseu dificil în ${masiv} la ${altitudine}m`;
            config = { bg: '#E9D502', text: '#000000' };
        } else {
            descriere = `Traseu periculos în ${masiv} la ${altitudine}m`;
            config = { bg: '#fee2e2', text: '#b91c1c' };
        }
        
        return { descriere, config };
    }

    render() {
        const status = this.getAttribute('status') || 'GALBEN';
        const mesaj = this.getAttribute('mesaj') || '';
        
        const config = this.getConfig()[status] || this.getConfig()['GALBEN'];
        const meteoConfig = this.getMeteoStatusConfig();
        const traseuInfo = this.getTraseuDescription();

        this.shadowRoot.innerHTML = `
            <style>
                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                
                .card {
                    background: ${config.bgGradient};
                    border: 2px solid ${config.borderColor};
                    border-radius: 1rem;
                    padding: 2rem;
                    text-align: center;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
                    font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                
                @media (min-width: 768px) {
                    .card {
                        padding: 2.5rem;
                    }
                }
                
                .icon-container {
                    display: flex;
                    justify-content: center;
                    margin-bottom: 1rem;
                }
                
                .icon {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 64px;
                    height: 64px;
                    background-color: ${config.iconBg};
                    border-radius: 1rem;
                    color: white;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
                }
                
                .title {
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #0f172a;
                    margin-bottom: 0.75rem;
                }
                
                @media (min-width: 768px) {
                    .title {
                        font-size: 1.875rem;
                    }
                }
                
                .message {
                    font-size: 1rem;
                    line-height: 1.6;
                    color: #475569;
                    max-width: 28rem;
                    margin: 0 auto 1rem;
                }
                
                .badges {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.5rem;
                    justify-content: center;
                    margin-top: 1rem;
                }
                
                .badge {
                    display: inline-flex;
                    align-items: center;
                    padding: 0.25rem 0.75rem;
                    border-radius: 9999px;
                    font-size: 0.75rem;
                    font-weight: 500;
                }
                
                .badge-nivel {
                    background-color: #ffffff;
                    color: #000000;
                    border: 1px solid #e2e8f0;
                }
                
                .badge-meteo {
                    background-color: ${meteoConfig.bg};
                    color: ${meteoConfig.text};
                }
                
                .badge-traseu {
                    background-color: ${traseuInfo.config.bg};
                    color: ${traseuInfo.config.text};
                }
            </style>
            
            <div class="card">
                <div class="icon-container">
                    <div class="icon">
                        ${this.getIcon(status)}
                    </div>
                </div>
                
                <h2 class="title">${config.title}</h2>
                <p class="message">${mesaj}</p>
                
                <div class="badges">
                    <span class="badge badge-nivel">👥 ${this.getNivelDisplay()}</span>
                    <span class="badge badge-meteo">🌤️ ${meteoConfig.label}</span>
                    <span class="badge badge-traseu">🏔️ ${traseuInfo.descriere}</span>
                </div>
            </div>
        `;
    }
}

customElements.define('verdict-card', VerdictCard);