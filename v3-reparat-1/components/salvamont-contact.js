/**
 * Salvamont Contact Component
 * Afișează informațiile de contact Salvamont
 */

class SalvamontContact extends HTMLElement {
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

    render() {
        const salvamont = this._data || {};
        const telefon = salvamont.telefon || salvamont.mobil || '112';
        const nume = salvamont.nume || 'Salvamont';

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
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                .contacts {
                    display: flex;
                    flex-direction: column;
                    gap: 0.75rem;
                }
                .contact-link {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 0.75rem;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.5rem;
                    text-decoration: none;
                    transition: all 0.15s;
                }
                .contact-link:hover {
                    background: #f1f5f9;
                }
                .contact-link.emergency {
                    background: #fef2f2;
                    border-color: #fecaca;
                }
                .contact-link.emergency:hover {
                    background: #fee2e2;
                }
                .contact-icon {
                    font-size: 1.5rem;
                }
                .contact-info {
                    flex: 1;
                }
                .contact-name {
                    font-size: 0.875rem;
                    font-weight: 500;
                    color: #475569;
                    margin: 0;
                }
                .contact-link.emergency .contact-name {
                    color: #dc2626;
                }
                .contact-number {
                    font-size: 1.125rem;
                    font-weight: 700;
                    color: #0f172a;
                    margin: 0;
                }
                .contact-link.emergency .contact-number {
                    color: #991b1b;
                }
                .call-icon {
                    color: #64748b;
                }
                .contact-link.emergency .call-icon {
                    color: #dc2626;
                }
            </style>
            
            <div class="container">
                <h2>
                    <span>🚨</span>
                    <span>Contact Salvamont</span>
                </h2>
                
                <div class="contacts">
                    <a href="tel:${telefon}" class="contact-link">
                        <span class="contact-icon">📞</span>
                        <div class="contact-info">
                            <p class="contact-name">${nume}</p>
                            <p class="contact-number">${telefon}</p>
                        </div>
                        <svg class="call-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </a>
                    
                    ${salvamont.mobil && salvamont.mobil !== telefon ? `
                    <a href="tel:${salvamont.mobil}" class="contact-link">
                        <span class="contact-icon">📱</span>
                        <div class="contact-info">
                            <p class="contact-name">${nume} (Mobil)</p>
                            <p class="contact-number">${salvamont.mobil}</p>
                        </div>
                        <svg class="call-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </a>
                    ` : ''}
                    
                    <a href="tel:112" class="contact-link emergency">
                        <span class="contact-icon">🚨</span>
                        <div class="contact-info">
                            <p class="contact-name">Urgențe Naționale</p>
                            <p class="contact-number">112</p>
                        </div>
                        <svg class="call-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </a>
                </div>
            </div>
        `;
    }
}

customElements.define('salvamont-contact', SalvamontContact);
