// public/assets/js/custom-picker.js
class DriverLuxPicker {
    constructor() {
        this.overlay = null;
        this.targetDateInput = null;
        this.targetTimeInput = null;
        this.veiculoId = null;
        this.bookedIntervals = [];

        this.currentMonth = new Date().getMonth();
        this.currentYear = new Date().getFullYear();

        this.selectedDate = null; // Date object
        this.selectedTime = "12:00"; // HH:MM string

        this.months = [
            "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
            "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
        ];

        this.initHTML();
    }

    initHTML() {
        if (document.getElementById('dl-picker-overlay')) {
            this.overlay = document.getElementById('dl-picker-overlay');
            return;
        }

        const overlay = document.createElement('div');
        overlay.id = 'dl-picker-overlay';
        overlay.className = 'dl-picker-overlay';

        overlay.innerHTML = `
            <div class="dl-picker-card">
                <div class="dl-picker-header">
                    <div class="dl-picker-title" id="dl-picker-title">Selecionar Data & Hora</div>
                    <button class="dl-picker-close" id="dl-picker-close">&times;</button>
                </div>
                
                <div class="dl-cal-section">
                    <div class="dl-cal-month-nav">
                        <button class="dl-cal-nav-btn" id="dl-cal-prev">&lt;</button>
                        <div class="dl-cal-month-name" id="dl-cal-month-name">Junho 2026</div>
                        <button class="dl-cal-nav-btn" id="dl-cal-next">&gt;</button>
                    </div>
                    
                    <div class="dl-cal-weekdays">
                        <div>Dom</div><div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
                    </div>
                    
                    <div class="dl-cal-days" id="dl-cal-days"></div>
                    
                    <div class="dl-cal-legend">
                        <div class="dl-legend-item"><span class="dl-legend-color free"></span>Livre</div>
                        <div class="dl-legend-item"><span class="dl-legend-color busy"></span>Ocupado</div>
                        <div class="dl-legend-item"><span class="dl-legend-color selected"></span>Selecionado</div>
                    </div>
                </div>
                
                <div class="dl-time-section">
                    <div class="dl-time-title">Escolha o Horário</div>
                    <div class="dl-time-grid" id="dl-time-grid"></div>
                </div>
                
                <button class="dl-picker-confirm-btn" id="dl-picker-confirm">Confirmar Escolha</button>
            </div>
        `;

        document.body.appendChild(overlay);
        this.overlay = overlay;

        // Event Listeners
        document.getElementById('dl-picker-close').addEventListener('click', () => this.close());
        document.getElementById('dl-cal-prev').addEventListener('click', () => this.changeMonth(-1));
        document.getElementById('dl-cal-next').addEventListener('click', () => this.changeMonth(1));
        document.getElementById('dl-picker-confirm').addEventListener('click', () => this.confirmSelection());

        // Close on overlay click
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) this.close();
        });
    }

    async open(dateInput, timeInput, veiculoId = null) {
        this.targetDateInput = dateInput;
        this.targetTimeInput = timeInput;
        this.veiculoId = veiculoId || this.detectVeiculoId();

        this.bookedIntervals = [];
        if (this.veiculoId) {
            await this.fetchBookedDates();
        }

        // Parse current value if any
        if (dateInput.value) {
            const parsed = new Date(dateInput.value + 'T00:00:00');
            if (!isNaN(parsed)) {
                this.selectedDate = parsed;
                this.currentMonth = parsed.getMonth();
                this.currentYear = parsed.getFullYear();
            }
        } else {
            this.selectedDate = null;
            this.currentMonth = new Date().getMonth();
            this.currentYear = new Date().getFullYear();
        }

        if (timeInput.value) {
            this.selectedTime = timeInput.value;
        } else {
            this.selectedTime = "12:00";
        }

        this.renderCalendar();
        this.renderTimeGrid();

        this.overlay.classList.add('active');
    }

    close() {
        this.overlay.classList.remove('active');
    }

    detectVeiculoId() {
        // Tenta pegar de dados_reserva no sessionStorage
        const dados = JSON.parse(sessionStorage.getItem('dados_reserva') || '{}');
        return dados.veiculo_id || null;
    }

    async fetchBookedDates() {
        try {
            const res = await fetch(`/DriverLux/public/api/reservas?veiculo_id=${this.veiculoId}`);
            if (res.ok) {
                const json = await res.json();
                if (json.status === 'success' && Array.isArray(json.data)) {
                    this.bookedIntervals = json.data.map(r => ({
                        start: new Date(r.data_retirada),
                        end: new Date(r.data_devolucao)
                    }));
                }
            }
        } catch (e) {
            console.error("Erro ao buscar datas reservadas:", e);
        }
    }

    changeMonth(dir) {
        this.currentMonth += dir;
        if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        } else if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        }
        this.renderCalendar();
    }

    renderCalendar() {
        const monthName = document.getElementById('dl-cal-month-name');
        monthName.textContent = `${this.months[this.currentMonth]} ${this.currentYear}`;

        const daysContainer = document.getElementById('dl-cal-days');
        daysContainer.innerHTML = '';

        const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();
        const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();

        // Dias em branco do mês anterior
        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'dl-cal-day empty';
            daysContainer.appendChild(emptyDiv);
        }

        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);

        // Renderiza cada dia
        for (let day = 1; day <= lastDay; day++) {
            const dayDiv = document.createElement('div');
            const thisDate = new Date(this.currentYear, this.currentMonth, day);
            thisDate.setHours(0, 0, 0, 0);

            dayDiv.textContent = day;

            // Verifica se está no passado
            const estaNoPassado = thisDate < hoje;

            // Verifica conflito de reservas
            const estaOcupado = this.checkDateOcupada(thisDate);

            if (estaNoPassado || estaOcupado) {
                dayDiv.className = 'dl-cal-day unavailable';
            } else {
                dayDiv.className = 'dl-cal-day available';

                // Evento clique
                dayDiv.addEventListener('click', () => {
                    document.querySelectorAll('.dl-cal-day').forEach(d => d.classList.remove('selected'));
                    dayDiv.classList.add('selected');
                    this.selectedDate = thisDate;
                });

                // Se já estiver selecionado
                if (this.selectedDate && thisDate.getTime() === this.selectedDate.getTime()) {
                    dayDiv.classList.add('selected');
                }
            }

            daysContainer.appendChild(dayDiv);
        }
    }

    checkDateOcupada(date) {
        // Se a data cai dentro de qualquer intervalo de reservas ativas
        const dateMs = date.getTime();
        for (let interval of this.bookedIntervals) {
            const start = new Date(interval.start);
            start.setHours(0, 0, 0, 0);

            const end = new Date(interval.end);
            end.setHours(23, 59, 59, 999);

            if (dateMs >= start.getTime() && dateMs <= end.getTime()) {
                return true;
            }
        }
        return false;
    }

    renderTimeGrid() {
        const grid = document.getElementById('dl-time-grid');
        grid.innerHTML = '';

        const startHour = 8;
        const endHour = 20;

        for (let h = startHour; h <= endHour; h++) {
            for (let m of ["00", "30"]) {
                if (h === endHour && m === "30") continue;

                const timeStr = `${h.toString().padStart(2, '0')}:${m}`;
                const card = document.createElement('div');
                card.className = 'dl-time-card';
                card.textContent = timeStr;

                if (this.selectedTime === timeStr) {
                    card.classList.add('selected');
                }

                card.addEventListener('click', () => {
                    document.querySelectorAll('.dl-time-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    this.selectedTime = timeStr;
                });

                grid.appendChild(card);
            }
        }
    }

    confirmSelection() {
        if (!this.selectedDate) {
            alert("Por favor, selecione uma data.");
            return;
        }

        // Formata data YYYY-MM-DD
        const year = this.selectedDate.getFullYear();
        const month = (this.selectedDate.getMonth() + 1).toString().padStart(2, '0');
        const day = this.selectedDate.getDate().toString().padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;

        this.targetDateInput.value = formattedDate;
        this.targetTimeInput.value = this.selectedTime;

        // Dispara o evento change para recalcular os totais no fluxo
        this.targetDateInput.dispatchEvent(new Event('change'));
        this.targetTimeInput.dispatchEvent(new Event('change'));

        this.close();
    }
}
// Inicializa a classe e disponibiliza globalmente
window.driverLuxPicker = new DriverLuxPicker();
// Hook automático para facilitar a aplicação nos inputs
window.addEventListener('DOMContentLoaded', () => {
    // Adiciona o link do CSS se não estiver presente
    if (!document.querySelector('link[href*="custom-picker.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/DriverLux/public/assets/css/custom-picker.css';
        document.head.appendChild(link);
    }

    // Função para anexar o picker nos inputs
    window.attachDriverLuxPicker = (dateInputId, timeInputId, veiculoId = null) => {
        const dateInput = document.getElementById(dateInputId);
        const timeInput = document.getElementById(timeInputId);

        if (dateInput && timeInput) {
            // Torna as inputs readonly para forçar o uso do picker
            dateInput.setAttribute('readonly', 'true');
            timeInput.setAttribute('readonly', 'true');
            dateInput.style.cursor = 'pointer';
            timeInput.style.cursor = 'pointer';

            const openPicker = () => {
                window.driverLuxPicker.open(dateInput, timeInput, veiculoId);
            };

            dateInput.addEventListener('click', openPicker);
            timeInput.addEventListener('click', openPicker);
        }
    };
});
