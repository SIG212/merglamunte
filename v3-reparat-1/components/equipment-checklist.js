/**
 * Equipment Checklist Component
 * Afișează lista de echipament cu checkbox-uri
 */

class EquipmentChecklist extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this._items = [];
        this._checked = new Set();
    }

    set items(value) {
        this._items = value || [];
        this.render();
    }

    get items() {
        return this._items;
    }

    connectedCallback() {
        this.render();
    }

    toggleItem(index) {
        if (this._checked.has(index)) {
            this._checked.delete(index);
        } else {
            this._checked.add(index);
        }
        this.render();
    }

    shareList() {
        let text = '🎒 Echipament Recomandat\n\n';
        this._items.forEach((item, idx) => {
            const checked = this._checked.has(idx) ? '☑' : '☐';
            text += `${checked} ${item}\n`;
        });
        text += '\n---\nGenerat de MergLaMunte.ro';

        if (navigator.share) {
            navigator.share({
                title: 'Echipament Montan',
                text: text
            }).catch(() => this.copyToClipboard(text));
        } else {
            this.copyToClipboard(text);
        }
    }

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Lista a fost copiată în clipboard!');
        }).catch(err => {
            console.error('Eroare copiere:', err);
        });
    }

    render() {
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
                .header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 1rem;
                }
                h2 {
                    font-size: 1.25rem;
                    font-weight: 700;
                    color: #0f172a;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                .share-btn {
                    display: flex;
                    align-items: center;
                    gap: 0.25rem;
                    padding: 0.5rem 0.75rem;
                    font-size: 0.75rem;
                    color: #2563eb;
                    background: transparent;
                    border: 1px solid #2563eb;
                    border-radius: 0.375rem;
                    cursor: pointer;
                    transition: all 0.15s;
                }
                .share-btn:hover {
                    background: #eff6ff;
                }
                .items-grid {
                    display: grid;
                    grid-template-columns: repeat(1, 1fr);
                    gap: 0.5rem;
                }
                @media (min-width: 640px) {
                    .items-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }
                .item-label {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 0.625rem;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.5rem;
                    cursor: pointer;
                    transition: all 0.15s;
                }
                .item-label:hover {
                    background: #f1f5f9;
                }
                .item-label.checked {
                    background: #ecfdf5;
                    border-color: #a7f3d0;
                }
                .item-label.checked .item-text {
                    text-decoration: line-through;
                    color: #64748b;
                }
                input[type="checkbox"] {
                    width: 1rem;
                    height: 1rem;
                    accent-color: #22c55e;
                    cursor: pointer;
                }
                .item-text {
                    font-size: 0.875rem;
                    color: #1e293b;
                    transition: all 0.15s;
                }
                .progress {
                    margin-top: 1rem;
                    padding-top: 1rem;
                    border-top: 1px solid #e2e8f0;
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                }
                .progress-bar {
                    flex: 1;
                    height: 0.5rem;
                    background: #e2e8f0;
                    border-radius: 9999px;
                    overflow: hidden;
                }
                .progress-fill {
                    height: 100%;
                    background: #22c55e;
                    border-radius: 9999px;
                    transition: width 0.3s ease;
                }
                .progress-text {
                    font-size: 0.875rem;
                    color: #64748b;
                    font-weight: 500;
                }
            </style>
            
            <div class="container">
                <div class="header">
                    <h2>
                        <span>🎒</span>
                        <span>Echipament Recomandat</span>
                    </h2>
                    <button class="share-btn" id="shareBtn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        Trimite
                    </button>
                </div>
                
                <div class="items-grid">
                    ${this._items.map((item, idx) => `
                        <label class="item-label ${this._checked.has(idx) ? 'checked' : ''}" data-index="${idx}">
                            <input type="checkbox" ${this._checked.has(idx) ? 'checked' : ''}>
                            <span class="item-text">${item}</span>
                        </label>
                    `).join('')}
                </div>
                
                ${this._items.length > 0 ? `
                    <div class="progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${(this._checked.size / this._items.length) * 100}%"></div>
                        </div>
                        <span class="progress-text">${this._checked.size}/${this._items.length}</span>
                    </div>
                ` : ''}
            </div>
        `;

        // Add event listeners
        const shareBtn = this.shadowRoot.getElementById('shareBtn');
        if (shareBtn) {
            shareBtn.addEventListener('click', () => this.shareList());
        }

        const labels = this.shadowRoot.querySelectorAll('.item-label');
        labels.forEach(label => {
            label.addEventListener('click', (e) => {
                e.preventDefault();
                const index = parseInt(label.dataset.index);
                this.toggleItem(index);
            });
        });
    }
}

customElements.define('equipment-checklist', EquipmentChecklist);
